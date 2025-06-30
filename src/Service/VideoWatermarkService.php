<?php

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\AddWatermarkRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\DeleteWatermarkRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\GetWatermarkRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\ListWatermarkRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\UpdateWatermarkRequest;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;

/**
 * 视频水印服务
 */
class VideoWatermarkService
{
    public function __construct(
        private readonly VodClientFactory $clientFactory,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    /**
     * 添加水印
     */
    public function addWatermark(
        string $name,
        string $watermarkConfig,
        string $type = 'Image',
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new AddWatermarkRequest([
            'name' => $name,
            'type' => $type, // Image 或 Text
            'watermarkConfig' => $watermarkConfig,
        ]);

        $response = $client->addWatermark($request);

        return [
            'requestId' => $response->body->requestId,
            'watermarkInfo' => [
                'watermarkId' => $response->body->watermarkInfo->watermarkId,
                'name' => $response->body->watermarkInfo->name,
                'type' => $response->body->watermarkInfo->type,
                'isDefault' => $response->body->watermarkInfo->isDefault,
                'creationTime' => $response->body->watermarkInfo->creationTime,
            ],
        ];
    }

    /**
     * 更新水印
     */
    public function updateWatermark(
        string $watermarkId,
        ?string $name = null,
        ?string $watermarkConfig = null,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $requestData = ['watermarkId' => $watermarkId];
        if ($name !== null) {
            $requestData['name'] = $name;
        }
        if ($watermarkConfig !== null) {
            $requestData['watermarkConfig'] = $watermarkConfig;
        }

        $request = new UpdateWatermarkRequest($requestData);
        $response = $client->updateWatermark($request);

        return [
            'requestId' => $response->body->requestId,
            'watermarkInfo' => [
                'watermarkId' => $response->body->watermarkInfo->watermarkId,
                'name' => $response->body->watermarkInfo->name,
                'type' => $response->body->watermarkInfo->type,
                'isDefault' => $response->body->watermarkInfo->isDefault,
                'creationTime' => $response->body->watermarkInfo->creationTime,
            ],
        ];
    }

    /**
     * 删除水印
     */
    public function deleteWatermark(
        string $watermarkId,
        ?AliyunVodConfig $config = null
    ): bool {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new DeleteWatermarkRequest([
            'watermarkId' => $watermarkId,
        ]);

        $response = $client->deleteWatermark($request);

        return !empty($response->body->requestId);
    }

    /**
     * 获取水印列表
     */
    public function listWatermarks(?AliyunVodConfig $config = null): array
    {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new ListWatermarkRequest();
        $response = $client->listWatermark($request);

        $watermarks = [];
        /** @phpstan-ignore-next-line */
        if (isset($response->body->watermarkInfos)) {
            foreach ($response->body->watermarkInfos as $watermark) {
                $watermarks[] = [
                    'watermarkId' => $watermark->watermarkId,
                    'name' => $watermark->name,
                    'type' => $watermark->type,
                    'isDefault' => $watermark->isDefault,
                    'creationTime' => $watermark->creationTime,
                ];
            }
        }

        return [
            'requestId' => $response->body->requestId,
            'watermarks' => $watermarks,
        ];
    }

    /**
     * 获取水印详情
     */
    public function getWatermark(
        string $watermarkId,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetWatermarkRequest([
            'watermarkId' => $watermarkId,
        ]);

        $response = $client->getWatermark($request);

        return [
            'requestId' => $response->body->requestId,
            'watermarkInfo' => [
                'watermarkId' => $response->body->watermarkInfo->watermarkId,
                'name' => $response->body->watermarkInfo->name,
                'type' => $response->body->watermarkInfo->type,
                'watermarkConfig' => $response->body->watermarkInfo->watermarkConfig,
                /** @phpstan-ignore-next-line */
                'fileUrl' => $response->body->watermarkInfo->fileUrl ?? null,
                'isDefault' => $response->body->watermarkInfo->isDefault,
                'creationTime' => $response->body->watermarkInfo->creationTime,
            ],
        ];
    }

    /**
     * 创建图片水印配置
     */
    public function createImageWatermarkConfig(
        string $fileUrl,
        string $position = 'TopRight',
        int $dx = 10,
        int $dy = 10,
        int $width = 100,
        int $height = 100
    ): string {
        $config = [
            'Dx' => $dx,
            'Dy' => $dy,
            'Width' => $width,
            'Height' => $height,
            'ReferPos' => $position,
            'Timeline' => [
                'Start' => '0',
                'Duration' => 'ToEND',
            ],
        ];

        return json_encode($config);
    }

    /**
     * 创建文字水印配置
     */
    public function createTextWatermarkConfig(
        string $content,
        string $fontName = 'SimSun',
        int $fontSize = 16,
        string $fontColor = 'Black',
        string $position = 'TopRight',
        int $dx = 10,
        int $dy = 10
    ): string {
        $config = [
            'Content' => $content,
            'FontName' => $fontName,
            'FontSize' => $fontSize,
            'FontColor' => $fontColor,
            'Dx' => $dx,
            'Dy' => $dy,
            'ReferPos' => $position,
            'Timeline' => [
                'Start' => '0',
                'Duration' => 'ToEND',
            ],
        ];

        return json_encode($config);
    }
} 