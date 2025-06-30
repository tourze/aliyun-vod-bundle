<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoUploadService;
use Tourze\AliyunVodBundle\Service\VodClientFactory;

/**
 * @covers \Tourze\AliyunVodBundle\Service\VideoUploadService
 */
class VideoUploadServiceTest extends TestCase
{
    private VideoUploadService $videoUploadService;
    private MockObject|VodClientFactory $clientFactoryMock;
    private MockObject|AliyunVodConfigService $configServiceMock;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(VodClientFactory::class);
        $this->configServiceMock = $this->createMock(AliyunVodConfigService::class);
        
        $this->videoUploadService = new VideoUploadService(
            $this->clientFactoryMock,
            $this->configServiceMock
        );
    }

    public function testCreateUploadAuthThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->videoUploadService->createUploadAuth('test-title', 'test-file.mp4');
    }

    public function testRefreshUploadAuthThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->videoUploadService->refreshUploadAuth('test-video-id');
    }

    public function testGetUploadProgress(): void
    {
        $result = $this->videoUploadService->getUploadProgress('test-video-id');
        $this->assertArrayHasKey('videoId', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('test-video-id', $result['videoId']);
    }
}