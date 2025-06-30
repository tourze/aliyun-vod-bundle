<?php

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\CreateUploadVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\RefreshUploadVideoRequest;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;

/**
 * 视频上传服务
 */
class VideoUploadService
{
    public function __construct(
        private readonly VodClientFactory $clientFactory,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    /**
     * 创建上传视频凭证
     */
    public function createUploadAuth(
        string $title,
        string $fileName,
        ?string $description = null,
        ?string $tags = null,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new CreateUploadVideoRequest([
            'title' => $title,
            'fileName' => $fileName,
            'description' => $description,
            'tags' => $tags,
            'templateGroupId' => $config->getTemplateGroupId(),
            'storageLocation' => $config->getStorageLocation(),
        ]);

        $response = $client->createUploadVideo($request);

        return [
            'videoId' => $response->body->videoId,
            'uploadAddress' => $response->body->uploadAddress,
            'uploadAuth' => $response->body->uploadAuth,
            'requestId' => $response->body->requestId,
        ];
    }

    /**
     * 刷新上传凭证
     */
    public function refreshUploadAuth(
        string $videoId,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new RefreshUploadVideoRequest([
            'videoId' => $videoId,
        ]);

        $response = $client->refreshUploadVideo($request);

        return [
            'videoId' => $response->body->videoId,
            'uploadAddress' => $response->body->uploadAddress,
            'uploadAuth' => $response->body->uploadAuth,
            'requestId' => $response->body->requestId,
        ];
    }

    /**
     * 获取上传进度
     * 注意：阿里云VOD不直接提供上传进度查询API
     * 需要在客户端实现进度回调
     */
    public function getUploadProgress(string $videoId): array
    {
        // 这里可以通过查询视频状态来间接判断上传进度
        // 实际的上传进度需要在客户端通过回调获取
        return [
            'videoId' => $videoId,
            'message' => '上传进度需要在客户端通过回调获取',
        ];
    }
}
