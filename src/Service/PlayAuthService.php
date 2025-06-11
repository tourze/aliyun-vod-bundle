<?php

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\GetVideoPlayAuthRequest;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 播放凭证服务
 */
class PlayAuthService
{
    public function __construct(
        private readonly VodClientFactory $clientFactory,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    /**
     * 获取视频播放凭证
     */
    public function getPlayAuth(
        string $videoId,
        ?int $authInfoTimeout = 3000,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?: $this->configService->getDefaultConfig();
        if (!$config) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetVideoPlayAuthRequest([
            'videoId' => $videoId,
            'authInfoTimeout' => $authInfoTimeout, // 凭证有效期，单位秒，默认3000秒
        ]);

        $response = $client->getVideoPlayAuth($request);

        return [
            'videoId' => $response->body->videoMeta->videoId,
            'playAuth' => $response->body->playAuth,
            'videoMeta' => [
                'title' => $response->body->videoMeta->title,
                'duration' => $response->body->videoMeta->duration,
                'coverURL' => $response->body->videoMeta->coverURL,
                'status' => $response->body->videoMeta->status,
            ],
            'requestId' => $response->body->requestId,
        ];
    }

    /**
     * 批量获取播放凭证
     */
    public function batchGetPlayAuth(
        array $videoIds,
        ?int $authInfoTimeout = 3000,
        ?AliyunVodConfig $config = null
    ): array {
        $results = [];
        foreach ($videoIds as $videoId) {
            try {
                $results[$videoId] = $this->getPlayAuth($videoId, $authInfoTimeout, $config);
            } catch (\Throwable $e) {
                $results[$videoId] = [
                    'error' => $e->getMessage(),
                    'videoId' => $videoId,
                ];
            }
        }
        return $results;
    }

    /**
     * 验证播放凭证是否有效
     * 注意：这是一个简单的时间验证，实际验证需要调用阿里云API
     */
    public function validatePlayAuth(string $playAuth): bool
    {
        // 这里应该实现真正的凭证验证逻辑
        // 暂时返回true作为占位符
        return !empty($playAuth);
    }
}
