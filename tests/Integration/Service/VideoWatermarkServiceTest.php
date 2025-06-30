<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoWatermarkService;
use Tourze\AliyunVodBundle\Service\VodClientFactory;

/**
 * @covers \Tourze\AliyunVodBundle\Service\VideoWatermarkService
 */
class VideoWatermarkServiceTest extends TestCase
{
    private VideoWatermarkService $videoWatermarkService;
    private MockObject|VodClientFactory $clientFactoryMock;
    private MockObject|AliyunVodConfigService $configServiceMock;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(VodClientFactory::class);
        $this->configServiceMock = $this->createMock(AliyunVodConfigService::class);
        
        $this->videoWatermarkService = new VideoWatermarkService(
            $this->clientFactoryMock,
            $this->configServiceMock
        );
    }

    public function testAddWatermarkThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->videoWatermarkService->addWatermark('test-name', 'test-config');
    }

    public function testCreateImageWatermarkConfig(): void
    {
        $config = $this->videoWatermarkService->createImageWatermarkConfig(
            'http://example.com/watermark.png'
        );
        $decodedConfig = json_decode($config, true);
        $this->assertIsArray($decodedConfig);
        $this->assertArrayHasKey('Dx', $decodedConfig);
        $this->assertArrayHasKey('Dy', $decodedConfig);
        $this->assertArrayHasKey('Width', $decodedConfig);
        $this->assertArrayHasKey('Height', $decodedConfig);
    }

    public function testCreateTextWatermarkConfig(): void
    {
        $config = $this->videoWatermarkService->createTextWatermarkConfig('Test Text');
        $decodedConfig = json_decode($config, true);
        $this->assertIsArray($decodedConfig);
        $this->assertArrayHasKey('Content', $decodedConfig);
        $this->assertArrayHasKey('FontName', $decodedConfig);
        $this->assertArrayHasKey('FontSize', $decodedConfig);
        $this->assertEquals('Test Text', $decodedConfig['Content']);
    }
}