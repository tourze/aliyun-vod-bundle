<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
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
#[AsCommand(name: self::NAME, description: '同步转码任务状态和进度', help: <<<'TXT'
    此命令同步转码任务的状态和进度信息，主要用于更新进行中的转码任务。
    TXT)]
#[WithMonologChannel(channel: 'aliyun_vod')]
class SyncTranscodeTaskCommand extends Command
{
    public const NAME = 'aliyun-vod:sync:transcode-task';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranscodeTaskRepository $transcodeTaskRepository,
        private readonly TranscodeService $transcodeService,
        private readonly LoggerInterface $logger,
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $taskId = $input->getOption('task-id');
        $status = $input->getOption('status');
        $limit = (int) $input->getOption('limit');
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('转码任务状态同步');

        try {
            // 获取要同步的转码任务列表
            $tasks = $this->getTasksToSync($taskId, $status, $limit);

            if ([] === $tasks) {
                $io->warning('没有找到需要同步的转码任务');

                return Command::SUCCESS;
            }

            $io->info('找到 ' . count($tasks) . ' 个转码任务需要同步');

            $totalSynced = 0;
            $totalCompleted = 0;
            $totalErrors = 0;

            $progressBar = $io->createProgressBar(count($tasks));
            $progressBar->start();

            foreach ($tasks as $task) {
                try {
                    $result = $this->syncTranscodeTask($task, $dryRun, $io);

                    if ('synced' === $result) {
                        ++$totalSynced;
                    } elseif ('completed' === $result) {
                        ++$totalCompleted;
                    }

                    $progressBar->advance();
                } catch (\Throwable $e) {
                    ++$totalErrors;
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
                '同步完成！',
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
        } catch (\Throwable $e) {
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
     *
     * @return array<int, TranscodeTask>
     */
    private function getTasksToSync(?string $taskId, ?string $status, int $limit): array
    {
        if (null !== $taskId) {
            $task = $this->transcodeTaskRepository->findByTaskId($taskId);

            return null !== $task ? [$task] : [];
        }

        if (null !== $status) {
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
            $remoteTaskInfo = $this->getRemoteTaskInfo($task);
            $oldStatus = $task->getStatus();
            $oldProgress = $task->getProgress();

            $this->updateTaskFromRemote($task, $remoteTaskInfo);
            $this->entityManager->flush();

            $this->logStatusChanges($task, $oldStatus, $oldProgress);

            return $task->isCompleted() ? 'completed' : 'synced';
        } catch (\Throwable $e) {
            $this->logError($task, $e);
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getRemoteTaskInfo(TranscodeTask $task): array
    {
        return $this->transcodeService->getTranscodeTask(
            $task->getTaskId(),
            $task->getVideo()->getConfig()
        );
    }

    /**
     * @param array<string, mixed> $remoteTaskInfo
     */
    private function updateTaskFromRemote(TranscodeTask $task, array $remoteTaskInfo): void
    {
        $task->setStatus($remoteTaskInfo['taskStatus']);

        $progressInfo = $this->transcodeService->getTranscodeProgress(
            $task->getTaskId(),
            $task->getVideo()->getConfig()
        );
        $task->setProgress((int) $progressInfo['overallProgress']);

        $this->handleTaskCompletion($task, $remoteTaskInfo, $progressInfo);
    }

    /**
     * @param array<string, mixed> $remoteTaskInfo
     * @param array<string, mixed> $progressInfo
     */
    private function handleTaskCompletion(TranscodeTask $task, array $remoteTaskInfo, array $progressInfo): void
    {
        $completedStatuses = ['TranscodeSuccess', 'TranscodeFail', 'TranscodeCancel'];

        if (!in_array($remoteTaskInfo['taskStatus'], $completedStatuses, true)) {
            return;
        }

        if (!$task->isCompleted()) {
            $task->markAsCompleted();
        }

        if ('TranscodeFail' === $remoteTaskInfo['taskStatus']) {
            $this->handleTranscodeFailure($task, $progressInfo);
        }
    }

    /**
     * @param array<string, mixed> $progressInfo
     */
    private function handleTranscodeFailure(TranscodeTask $task, array $progressInfo): void
    {
        $jobDetails = $progressInfo['jobDetails'] ?? [];
        foreach ($jobDetails as $job) {
            if ('TranscodeFail' === $job['transcodeJobStatus']) {
                $task->setErrorCode($job['errorCode'] ?? 'Unknown');
                $task->setErrorMessage($job['errorMessage'] ?? '转码失败');
                break;
            }
        }
    }

    private function logStatusChanges(TranscodeTask $task, string $oldStatus, int $oldProgress): void
    {
        if ($oldStatus !== $task->getStatus() || $oldProgress !== $task->getProgress()) {
            $this->logger->info('转码任务状态更新', [
                'taskId' => $task->getTaskId(),
                'oldStatus' => $oldStatus,
                'newStatus' => $task->getStatus(),
                'oldProgress' => $oldProgress,
                'newProgress' => $task->getProgress(),
            ]);
        }
    }

    private function logError(TranscodeTask $task, \Throwable $e): void
    {
        $this->logger->error('转码任务同步失败', [
            'taskId' => $task->getTaskId(),
            'error' => $e->getMessage(),
            'exception' => $e,
        ]);
    }
}
