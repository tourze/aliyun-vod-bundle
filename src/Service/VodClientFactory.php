<?php

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\GetPlayInfoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Vod;
use Darabonba\OpenApi\Models\Config;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 阿里云VOD客户端工厂
 * 根据配置实体创建对应的VOD客户端
 */
class VodClientFactory
{
    /**
     * 根据配置创建VOD客户端
     */
    public function createClient(AliyunVodConfig $config): Vod
    {
        $openApiConfig = new Config([
            'accessKeyId' => $config->getAccessKeyId(),
            'accessKeySecret' => $config->getAccessKeySecret(),
            'regionId' => $config->getRegionId(),
            'endpoint' => sprintf('vod.%s.aliyuncs.com', $config->getRegionId()),
        ]);

        return new Vod($openApiConfig);
    }

    /**
     * 验证配置是否有效
     */
    public function validateConfig(AliyunVodConfig $config): bool
    {
        try {
            $client = $this->createClient($config);
            // 尝试调用一个简单的API来验证配置
            $request = new GetPlayInfoRequest(['videoId' => 'test_video_id']);
            $client->getPlayInfo($request);
            return true;
        } catch (\Throwable $e) {
            // 如果是因为视频ID不存在的错误，说明配置是有效的
            if (str_contains($e->getMessage(), 'InvalidVideo.NotFound')) {
                return true;
            }
            return false;
        }
    }
}
