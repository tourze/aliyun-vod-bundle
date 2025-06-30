<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\TranscodeService;
use Tourze\AliyunVodBundle\Service\VodClientFactory;

/**
 * @covers \Tourze\AliyunVodBundle\Service\TranscodeService
 */
class TranscodeServiceTest extends TestCase
{
    private TranscodeService $transcodeService;
    private MockObject|VodClientFactory $clientFactoryMock;
    private MockObject|AliyunVodConfigService $configServiceMock;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(VodClientFactory::class);
        $this->configServiceMock = $this->createMock(AliyunVodConfigService::class);
        
        $this->transcodeService = new TranscodeService(
            $this->clientFactoryMock,
            $this->configServiceMock
        );
    }

    public function testSubmitTranscodeJobsThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->transcodeService->submitTranscodeJobs('test-video-id');
    }

    public function testGetTranscodeTaskThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->transcodeService->getTranscodeTask('test-task-id');
    }
}