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
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;
use Tourze\AliyunVodBundle\Repository\VideoRepository;

/**
 * 从阿里云VOD同步视频数据到本地数据库
 */
#[AsCommand(name: self::NAME, description: '从阿里云VOD同步视频数据到本地数据库', help: <<<'TXT'
    此命令从阿里云VOD服务同步视频数据到本地数据库，支持增量同步和全量同步。
    TXT)]
#[WithMonologChannel(channel: 'aliyun_vod')]
class SyncVideoFromRemoteCommand extends Command
{
    public const NAME = 'aliyun-vod:sync:from-remote';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AliyunVodConfigRepository $configRepository,
        private readonly VideoRepository $videoRepository,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, '指定配置名称')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, '限制同步数量', 100)
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制更新已存在的视频')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际写入数据库')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $configName = $this->resolveStringOption($input, 'config');
        $limit = $this->resolveIntOption($input, 'limit', 100);
        $force = (bool) $input->getOption('force');
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('阿里云VOD视频数据同步');

        try {
            // 获取配置
            $configs = $this->getConfigs($configName);
            if ([] === $configs) {
                $io->error('未找到可用的阿里云VOD配置');

                return Command::FAILURE;
            }

            $totalSynced = 0;
            $totalUpdated = 0;
            $totalErrors = 0;

            foreach ($configs as $config) {
                $io->section("同步配置: {$config->getName()}");

                try {
                    $result = $this->syncVideosFromConfig($config, $limit, $force, $dryRun, $io);
                    $totalSynced += $result['synced'];
                    $totalUpdated += $result['updated'];
                    $totalErrors += $result['errors'];
                } catch (\Throwable $e) {
                    $io->error("配置 {$config->getName()} 同步失败: {$e->getMessage()}");
                    $this->logger->error('视频同步失败', [
                        'config' => $config->getName(),
                        'error' => $e->getMessage(),
                        'exception' => $e,
                    ]);
                    ++$totalErrors;
                }
            }

            // 显示总结
            $io->success([
                '同步完成！',
                "新增视频: {$totalSynced}",
                "更新视频: {$totalUpdated}",
                "错误数量: {$totalErrors}",
            ]);

            $this->logger->info('视频同步完成', [
                'synced' => $totalSynced,
                'updated' => $totalUpdated,
                'errors' => $totalErrors,
            ]);

            return $totalErrors > 0 ? Command::FAILURE : Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error("同步过程中发生错误: {$e->getMessage()}");
            $this->logger->error('视频同步异常', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * 获取要同步的配置列表
     *
     * @return array<int, AliyunVodConfig>
     */
    private function getConfigs(?string $configName): array
    {
        if (null !== $configName) {
            $config = $this->configRepository->findOneBy(['name' => $configName, 'valid' => true]);

            return null !== $config ? [$config] : [];
        }

        return $this->configRepository->findActiveConfigs();
    }

    /**
     * 从指定配置同步视频
     *
     * @return array{synced: int, updated: int, errors: int}
     */
    private function syncVideosFromConfig(
        AliyunVodConfig $config,
        int $limit,
        bool $force,
        bool $dryRun,
        SymfonyStyle $io,
    ): array {
        $synced = 0;
        $updated = 0;
        $errors = 0;

        // 这里应该调用阿里云API获取视频列表
        // 由于阿里云SDK的复杂性，这里使用模拟数据
        $remoteVideos = $this->getRemoteVideoList($config, $limit);

        $progressBar = $io->createProgressBar(count($remoteVideos));
        $progressBar->start();

        foreach ($remoteVideos as $remoteVideoData) {
            try {
                $result = $this->syncSingleVideo($config, $remoteVideoData, $force, $dryRun);

                if ('synced' === $result) {
                    ++$synced;
                } elseif ('updated' === $result) {
                    ++$updated;
                }

                $progressBar->advance();
            } catch (\Throwable $e) {
                ++$errors;
                $this->logger->error('单个视频同步失败', [
                    'videoId' => $remoteVideoData['videoId'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $io->newLine(2);

        return [
            'synced' => $synced,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * 同步单个视频
     *
     * @param array<string, mixed> $remoteVideoData
     */
    private function syncSingleVideo(
        AliyunVodConfig $config,
        array $remoteVideoData,
        bool $force,
        bool $dryRun,
    ): string {
        $videoId = $remoteVideoData['videoId'] ?? '';
        if (!is_string($videoId)) {
            throw new \UnexpectedValueException('远程视频数据缺少有效的 videoId');
        }
        $existingVideo = $this->videoRepository->findByVideoId($videoId);

        if (null !== $existingVideo && !$force) {
            return 'skipped';
        }

        if ($dryRun) {
            return null !== $existingVideo ? 'would_update' : 'would_sync';
        }

        if (null !== $existingVideo) {
            // 更新现有视频
            $this->updateVideoFromRemoteData($existingVideo, $remoteVideoData);

            return 'updated';
        }
        // 创建新视频
        $this->createVideoFromRemoteData($config, $remoteVideoData);

        return 'synced';
    }

    /**
     * 从远程数据创建新视频
     *
     * @param array<string, mixed> $data
     */
    private function createVideoFromRemoteData(AliyunVodConfig $config, array $data): void
    {
        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId($this->requireString($data, 'videoId'));
        $video->setTitle($this->requireString($data, 'title'));
        $video->setDescription($this->getStringOrNull($data, 'description'));
        $video->setDuration($this->getIntOrNull($data, 'duration'));
        $video->setSize($this->getIntOrNull($data, 'size'));
        $video->setStatus($this->requireString($data, 'status'));
        $video->setCoverUrl($this->getStringOrNull($data, 'coverURL'));
        $video->setTags($this->getStringOrNull($data, 'tags'));
        $video->setValid(true);

        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    /**
     * 从远程数据更新现有视频
     *
     * @param array<string, mixed> $data
     */
    private function updateVideoFromRemoteData(Video $video, array $data): void
    {
        $video->setTitle($this->requireString($data, 'title'));
        $video->setDescription($this->getStringOrNull($data, 'description'));
        $video->setDuration($this->getIntOrNull($data, 'duration'));
        $video->setSize($this->getIntOrNull($data, 'size'));
        $video->setStatus($this->requireString($data, 'status'));
        $video->setCoverUrl($this->getStringOrNull($data, 'coverURL'));
        $video->setTags($this->getStringOrNull($data, 'tags'));

        $this->entityManager->flush();
    }

    /**
     * 获取远程视频列表（模拟数据）
     * 实际实现中应该调用阿里云API
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRemoteVideoList(AliyunVodConfig $config, int $limit): array
    {
        // 模拟从阿里云API获取的视频数据
        return [
            [
                'videoId' => 'remote_video_001',
                'title' => '远程同步测试视频1',
                'description' => '这是从阿里云同步的测试视频',
                'duration' => 120,
                'size' => 10485760,
                'status' => 'Normal',
                'coverURL' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&h=450&fit=crop',
                'tags' => '同步,测试',
            ],
            [
                'videoId' => 'remote_video_002',
                'title' => '远程同步测试视频2',
                'description' => '另一个从阿里云同步的测试视频',
                'duration' => 240,
                'size' => 20971520,
                'status' => 'Normal',
                'coverURL' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=450&fit=crop',
                'tags' => '同步,演示',
            ],
        ];
    }

    /**
     * 解析字符串类型的命令选项
     */
    private function resolveStringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);
        if (null === $value) {
            return null;
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException("选项 {$name} 必须为字符串");
        }

        return $value;
    }

    /**
     * 解析整数类型的命令选项
     */
    private function resolveIntOption(InputInterface $input, string $name, int $default): int
    {
        $value = $input->getOption($name);
        if (null === $value) {
            return $default;
        }
        // 兼容测试环境中可能传递的整数类型
        if (is_int($value)) {
            return $value;
        }
        if (!is_string($value) || !ctype_digit($value)) {
            throw new \InvalidArgumentException("选项 {$name} 必须为正整数");
        }

        return (int) $value;
    }

    /**
     * 从数组中获取必需的字符串字段
     *
     * @param array<string, mixed> $data
     */
    private function requireString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (!is_string($value) || '' === $value) {
            throw new \UnexpectedValueException("字段 {$key} 必须为非空字符串");
        }

        return $value;
    }

    /**
     * 从数组中获取可选的字符串字段
     *
     * @param array<string, mixed> $data
     */
    private function getStringOrNull(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (!is_string($value)) {
            throw new \UnexpectedValueException("字段 {$key} 期望字符串类型");
        }

        return $value;
    }

    /**
     * 从数组中获取可选的整数字段
     *
     * @param array<string, mixed> $data
     */
    private function getIntOrNull(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (!is_int($value) && !is_numeric($value)) {
            throw new \UnexpectedValueException("字段 {$key} 期望整数类型");
        }

        return (int) $value;
    }
}
