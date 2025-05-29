<?php

namespace Tourze\AliyunVodBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Repository\TranscodeTaskRepository;
use Tourze\AliyunVodBundle\Service\TranscodeService;

/**
 * 同步转码任务状态
 */
#[AsCommand(
    name: self::NAME,
    description: '同步转码任务状态和进度'
)]
class SyncTranscodeTaskCommand extends Command
{
    public const NAME = 'aliyun-vod:sync:transcode-task';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranscodeTaskRepository $transcodeTaskRepository,
        private readonly TranscodeService $transcodeService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('task-id', null, InputOption::VALUE_OPTIONAL, '指定要同步的任务ID')
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, '只同步指定状态的任务', 'Processing')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, '限制同步数量', 50)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际更新数据库')
            ->setHelp('此命令同步转码任务的状态和进度信息，主要用于更新进行中的转码任务。');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $taskId = $input->getOption('task-id');
        $status = $input->getOption('status');
        $limit = (int) $input->getOption('limit');
        $dryRun = $input->getOption('dry-run');

        $io->title('转码任务状态同步');

        try {
            // 获取要同步的转码任务列表
            $tasks = $this->getTasksToSync($taskId, $status, $limit);
            
            if (empty($tasks)) {
                $io->warning('没有找到需要同步的转码任务');
                return Command::SUCCESS;
            }

            $io->info("找到 " . count($tasks) . " 个转码任务需要同步");

            $totalSynced = 0;
            $totalCompleted = 0;
            $totalErrors = 0;

            $progressBar = $io->createProgressBar(count($tasks));
            $progressBar->start();

            foreach ($tasks as $task) {
                try {
                    $result = $this->syncTranscodeTask($task, $dryRun, $io);
                    
                    if ($result === 'synced') {
                        $totalSynced++;
                    } elseif ($result === 'completed') {
                        $totalCompleted++;
                    }

                    $progressBar->advance();
                } catch (\Exception $e) {
                    $totalErrors++;
                    $this->logger->error('转码任务同步失败', [
                        'taskId' => $task->getTaskId(),
                        'error' => $e->getMessage(),
                        'exception' => $e,
                    ]);
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            $io->newLine(2);

            // 显示总结
            $io->success([
                "同步完成！",
                "更新任务: {$totalSynced}",
                "完成任务: {$totalCompleted}",
                "错误数量: {$totalErrors}",
            ]);

            $this->logger->info('转码任务同步完成', [
                'synced' => $totalSynced,
                'completed' => $totalCompleted,
                'errors' => $totalErrors,
            ]);

            return $totalErrors > 0 ? Command::FAILURE : Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error("同步过程中发生错误: {$e->getMessage()}");
            $this->logger->error('转码任务同步异常', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * 获取要同步的转码任务列表
     */
    private function getTasksToSync(?string $taskId, ?string $status, int $limit): array
    {
        if ($taskId) {
            $task = $this->transcodeTaskRepository->findByTaskId($taskId);
            return $task ? [$task] : [];
        }

        if ($status) {
            return $this->transcodeTaskRepository->findBy(
                ['status' => $status],
                ['createdTime' => 'ASC'],
                $limit
            );
        }

        // 默认同步进行中的任务
        return $this->transcodeTaskRepository->findProcessingTasks();
    }

    /**
     * 同步单个转码任务
     */
    private function syncTranscodeTask(TranscodeTask $task, bool $dryRun, SymfonyStyle $io): string
    {
        if ($dryRun) {
            $io->writeln("试运行：将同步转码任务 {$task->getTaskId()}");
            return 'would_sync';
        }

        try {
            // 获取远程转码任务状态
            $remoteTaskInfo = $this->transcodeService->getTranscodeTask(
                $task->getTaskId(),
                $task->getVideo()->getConfig()
            );

            $oldStatus = $task->getStatus();
            $oldProgress = $task->getProgress();

            // 更新任务状态和进度
            $task->setStatus($remoteTaskInfo['taskStatus']);

            // 计算总体进度
            $progressInfo = $this->transcodeService->getTranscodeProgress(
                $task->getTaskId(),
                $task->getVideo()->getConfig()
            );
            $task->setProgress((int) $progressInfo['overallProgress']);

            // 如果任务完成，设置完成时间
            if (in_array($remoteTaskInfo['taskStatus'], ['TranscodeSuccess', 'TranscodeFail', 'TranscodeCancel'])) {
                if (!$task->isCompleted()) {
                    $task->markAsCompleted();
                }

                // 如果是失败状态，记录错误信息
                if ($remoteTaskInfo['taskStatus'] === 'TranscodeFail') {
                    $jobDetails = $progressInfo['jobDetails'] ?? [];
                    foreach ($jobDetails as $job) {
                        if ($job['transcodeJobStatus'] === 'TranscodeFail') {
                            $task->setErrorCode($job['errorCode'] ?? 'Unknown')
                                ->setErrorMessage($job['errorMessage'] ?? '转码失败');
                            break;
                        }
                    }
                }
            }

            $this->entityManager->flush();

            // 记录状态变化
            if ($oldStatus !== $task->getStatus() || $oldProgress !== $task->getProgress()) {
                $this->logger->info('转码任务状态更新', [
                    'taskId' => $task->getTaskId(),
                    'oldStatus' => $oldStatus,
                    'newStatus' => $task->getStatus(),
                    'oldProgress' => $oldProgress,
                    'newProgress' => $task->getProgress(),
                ]);
            }

            return $task->isCompleted() ? 'completed' : 'synced';

        } catch (\Exception $e) {
            $this->logger->error('转码任务同步失败', [
                'taskId' => $task->getTaskId(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw $e;
        }
    }
} 