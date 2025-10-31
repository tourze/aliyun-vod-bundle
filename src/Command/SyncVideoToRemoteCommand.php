<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Command;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\AliyunVodBundle\Service\VideoManageService;

/**
 * 将本地视频数据同步到阿里云VOD
 */
#[AsCommand(name: self::NAME, description: '将本地视频数据同步到阿里云VOD', help: <<<'TXT'
    此命令将本地数据库中的视频信息同步到阿里云VOD服务，主要用于更新视频元数据。
    TXT)]
#[WithMonologChannel(channel: 'aliyun_vod')]
class SyncVideoToRemoteCommand extends Command
{
    public const NAME = 'aliyun-vod:sync:to-remote';

    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly VideoManageService $videoManageService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('video-id', null, InputOption::VALUE_OPTIONAL, '指定要同步的视频ID')
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, '只同步指定状态的视频')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, '限制同步数量', 50)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际调用阿里云API')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $videoId = $input->getOption('video-id');
        $status = $input->getOption('status');
        $limit = (int) $input->getOption('limit');
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('本地视频数据同步到阿里云VOD');

        try {
            // 获取要同步的视频列表
            $videos = $this->getVideosToSync($videoId, $status, $limit);

            if ([] === $videos) {
                $io->warning('没有找到需要同步的视频');

                return Command::SUCCESS;
            }

            $io->info('找到 ' . count($videos) . ' 个视频需要同步');

            $totalSynced = 0;
            $totalErrors = 0;

            $progressBar = $io->createProgressBar(count($videos));
            $progressBar->start();

            foreach ($videos as $video) {
                try {
                    $result = $this->syncVideoToRemote($video, $dryRun);

                    if ($result) {
                        ++$totalSynced;
                    }

                    $progressBar->advance();
                } catch (\Throwable $e) {
                    ++$totalErrors;
                    $this->logger->error('视频同步到远程失败', [
                        'videoId' => $video->getVideoId(),
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
                "成功同步: {$totalSynced}",
                "错误数量: {$totalErrors}",
            ]);

            $this->logger->info('视频同步到远程完成', [
                'synced' => $totalSynced,
                'errors' => $totalErrors,
            ]);

            return $totalErrors > 0 ? Command::FAILURE : Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error("同步过程中发生错误: {$e->getMessage()}");
            $this->logger->error('视频同步到远程异常', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * 获取要同步的视频列表
     *
     * @return array<int, Video>
     */
    private function getVideosToSync(?string $videoId, ?string $status, int $limit): array
    {
        if (null !== $videoId) {
            $video = $this->videoRepository->findByVideoId($videoId);

            return null !== $video ? [$video] : [];
        }

        $criteria = ['valid' => true];
        if (null !== $status) {
            $criteria['status'] = $status;
        }

        return $this->videoRepository->findBy($criteria, ['updatedTime' => 'DESC'], $limit);
    }

    /**
     * 同步单个视频到远程
     */
    private function syncVideoToRemote(Video $video, bool $dryRun): bool
    {
        if ($dryRun) {
            $this->logger->info('试运行：将同步视频', [
                'videoId' => $video->getVideoId(),
                'title' => $video->getTitle(),
            ]);

            return true;
        }

        try {
            // 更新视频信息到阿里云
            $result = $this->videoManageService->updateVideoInfo(
                $video->getVideoId(),
                $video->getTitle(),
                $video->getDescription(),
                $video->getTags(),
                $video->getConfig()
            );

            if ($result) {
                $this->logger->info('视频同步到远程成功', [
                    'videoId' => $video->getVideoId(),
                    'title' => $video->getTitle(),
                ]);

                return true;
            }

            return false;
        } catch (\Throwable $e) {
            $this->logger->error('视频同步到远程失败', [
                'videoId' => $video->getVideoId(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
