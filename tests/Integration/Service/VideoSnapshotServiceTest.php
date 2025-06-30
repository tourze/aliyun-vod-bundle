<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoSnapshotService;
use Tourze\AliyunVodBundle\Service\VodClientFactory;

/**
 * @covers \Tourze\AliyunVodBundle\Service\VideoSnapshotService
 */
class VideoSnapshotServiceTest extends TestCase
{
    private VideoSnapshotService $videoSnapshotService;
    private MockObject|VodClientFactory $clientFactoryMock;
    private MockObject|AliyunVodConfigService $configServiceMock;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(VodClientFactory::class);
        $this->configServiceMock = $this->createMock(AliyunVodConfigService::class);
        
        $this->videoSnapshotService = new VideoSnapshotService(
            $this->clientFactoryMock,
            $this->configServiceMock
        );
    }

    public function testSubmitSnapshotJobThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->videoSnapshotService->submitSnapshotJob('test-video-id');
    }

    public function testBatchGenerateSnapshots(): void
    {
        $this->configServiceMock
            ->expects($this->atLeastOnce())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $result = $this->videoSnapshotService->batchGenerateSnapshots(['video1', 'video2']);
        $this->assertArrayHasKey('video1', $result);
        $this->assertArrayHasKey('video2', $result);
        $this->assertArrayHasKey('error', $result['video1']);
        $this->assertArrayHasKey('error', $result['video2']);
    }
}