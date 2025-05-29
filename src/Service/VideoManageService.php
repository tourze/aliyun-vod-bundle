<?php

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\DeleteVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\GetPlayInfoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\GetVideoInfoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\UpdateVideoInfoRequest;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 视频管理服务
 */
class VideoManageService
{
    public function __construct(
        private readonly VodClientFactory $clientFactory,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    /**
     * 获取视频信息
     */
    public function getVideoInfo(
        string $videoId,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?: $this->configService->getDefaultConfig();
        if (!$config) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetVideoInfoRequest([
            'videoId' => $videoId,
        ]);

        $response = $client->getVideoInfo($request);
        $video = $response->body->video;

        return [
            'videoId' => $video->videoId,
            'title' => $video->title,
            'description' => $video->description,
            'duration' => $video->duration,
            'size' => $video->size,
            'status' => $video->status,
            'creationTime' => $video->creationTime,
            'modificationTime' => $video->modificationTime,
            'coverURL' => $video->coverURL,
            'snapshots' => $video->snapshots,
            'tags' => $video->tags,
        ];
    }

    /**
     * 获取视频播放信息
     */
    public function getPlayInfo(
        string $videoId,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?: $this->configService->getDefaultConfig();
        if (!$config) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetPlayInfoRequest([
            'videoId' => $videoId,
        ]);

        $response = $client->getPlayInfo($request);

        $playInfos = [];
        foreach ($response->body->playInfoList as $playInfo) {
            $playInfos[] = [
                'playURL' => $playInfo->playURL,
                'definition' => $playInfo->definition,
                'format' => $playInfo->format,
                'duration' => $playInfo->duration,
                'size' => $playInfo->size,
                'bitrate' => $playInfo->bitrate,
                'fps' => $playInfo->fps,
                'width' => $playInfo->width,
                'height' => $playInfo->height,
            ];
        }

        return [
            'videoBase' => [
                'videoId' => $response->body->videoBase->videoId,
                'title' => $response->body->videoBase->title,
                'duration' => $response->body->videoBase->duration,
                'coverURL' => $response->body->videoBase->coverURL,
                'status' => $response->body->videoBase->status,
            ],
            'playInfoList' => $playInfos,
        ];
    }

    /**
     * 更新视频信息
     */
    public function updateVideoInfo(
        string $videoId,
        ?string $title = null,
        ?string $description = null,
        ?string $tags = null,
        ?AliyunVodConfig $config = null
    ): bool {
        $config = $config ?: $this->configService->getDefaultConfig();
        if (!$config) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new UpdateVideoInfoRequest([
            'videoId' => $videoId,
            'title' => $title,
            'description' => $description,
            'tags' => $tags,
        ]);

        $response = $client->updateVideoInfo($request);

        return !empty($response->body->requestId);
    }

    /**
     * 删除视频
     */
    public function deleteVideo(
        string $videoIds,
        ?AliyunVodConfig $config = null
    ): bool {
        $config = $config ?: $this->configService->getDefaultConfig();
        if (!$config) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new DeleteVideoRequest([
            'videoIds' => $videoIds, // 支持批量删除，用逗号分隔
        ]);

        $response = $client->deleteVideo($request);

        return !empty($response->body->requestId);
    }

    /**
     * 批量删除视频
     */
    public function batchDeleteVideos(
        array $videoIds,
        ?AliyunVodConfig $config = null
    ): bool {
        return $this->deleteVideo(implode(',', $videoIds), $config);
    }
}
