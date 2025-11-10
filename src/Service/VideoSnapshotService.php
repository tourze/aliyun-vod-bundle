<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\ListSnapshotsRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\SubmitSnapshotJobRequest;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;

/**
 * 视频截图服务
 */
#[Autoconfigure(public: true)]
readonly class VideoSnapshotService
{
    public function __construct(
        private VodClientFactory $clientFactory,
        private AliyunVodConfigService $configService,
    ) {
    }

    /**
     * 提交截图任务
     *
     * @return array{requestId: string, snapshotJob: array{jobId: string|null}}
     */
    public function submitSnapshotJob(
        string $videoId,
        ?string $snapshotTemplateId = null,
        ?int $count = 1,
        ?int $interval = null,
        ?AliyunVodConfig $config = null,
    ): array {
        $config ??= $this->configService->getDefaultConfig();
        if (null === $config) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $requestData = [
            'videoId' => $videoId,
            'count' => $count,
        ];

        if (null !== $snapshotTemplateId) {
            $requestData['snapshotTemplateId'] = $snapshotTemplateId;
        }

        if (null !== $interval) {
            $requestData['interval'] = $interval;
        }

        $request = new SubmitSnapshotJobRequest($requestData);
        $response = $client->submitSnapshotJob($request);

        return [
            'requestId' => $response->body->requestId,
            'snapshotJob' => [
                'jobId' => $response->body->snapshotJob->jobId,
            ],
        ];
    }

    /**
     * 获取视频截图列表
     *
     * @return array<string, mixed>
     */
    public function getVideoSnapshots(
        string $videoId,
        ?string $snapshotType = 'CoverSnapshot',
        ?int $pageNo = 1,
        ?int $pageSize = 20,
        ?AliyunVodConfig $config = null,
    ): array {
        $config ??= $this->configService->getDefaultConfig();
        if (null === $config) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new ListSnapshotsRequest([
            'videoId' => $videoId,
            'snapshotType' => $snapshotType,
            'pageNo' => $pageNo,
            'pageSize' => $pageSize,
        ]);

        $response = $client->listSnapshots($request);

        $snapshots = [];
              foreach ($response->body->mediaSnapshot->snapshots->snapshot as $snapshot) {
            $snapshots[] = [
                'url' => $snapshot->url,
                'index' => $snapshot->index,
            ];
        }

        return [
            'requestId' => $response->body->requestId,
            'mediaSnapshot' => [
                'total' => $response->body->mediaSnapshot->total,
                'regular' => $response->body->mediaSnapshot->regular,
                'snapshots' => $snapshots,
            ],
        ];
    }

    /**
     * 生成指定时间点的截图
     *
     * @return array{requestId: string, snapshotJob: array{jobId: string|null}}
     */
    public function generateSnapshotAtTime(
        string $videoId,
        int $timePoint,
        ?int $width = 800,
        ?int $height = 600,
        ?AliyunVodConfig $config = null,
    ): array {
        // 提交截图任务，指定时间点
        return $this->submitSnapshotJob(
            $videoId,
            null,
            1,
            $timePoint,
            $config
        );
    }

    /**
     * 批量生成截图
     *
     * @param array<int, string> $videoIds
     *
     * @return array<string, array<string, mixed>>
     */
    public function batchGenerateSnapshots(
        array $videoIds,
        ?int $count = 1,
        ?AliyunVodConfig $config = null,
    ): array {
        $results = [];
        foreach ($videoIds as $videoId) {
            try {
                $results[$videoId] = $this->submitSnapshotJob($videoId, null, $count, null, $config);
            } catch (\Throwable $e) {
                $results[$videoId] = [
                    'error' => $e->getMessage(),
                    'videoId' => $videoId,
                ];
            }
        }

        return $results;
    }
}
