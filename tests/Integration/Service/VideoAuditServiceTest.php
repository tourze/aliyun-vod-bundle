<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoAuditService;
use Tourze\AliyunVodBundle\Service\VodClientFactory;

/**
 * @covers \Tourze\AliyunVodBundle\Service\VideoAuditService
 */
class VideoAuditServiceTest extends TestCase
{
    private VideoAuditService $videoAuditService;
    private MockObject|VodClientFactory $clientFactoryMock;
    private MockObject|AliyunVodConfigService $configServiceMock;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(VodClientFactory::class);
        $this->configServiceMock = $this->createMock(AliyunVodConfigService::class);
        
        $this->videoAuditService = new VideoAuditService(
            $this->clientFactoryMock,
            $this->configServiceMock
        );
    }

    public function testSubmitAIMediaAuditJobThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->videoAuditService->submitAIMediaAuditJob('test-media-id');
    }

    public function testIsAuditPassed(): void
    {
        $passResult = ['mediaAuditResult' => ['suggestion' => 'pass']];
        $failResult = ['mediaAuditResult' => ['suggestion' => 'block']];
        $emptyResult = [];

        $this->assertTrue($this->videoAuditService->isAuditPassed($passResult));
        $this->assertFalse($this->videoAuditService->isAuditPassed($failResult));
        $this->assertFalse($this->videoAuditService->isAuditPassed($emptyResult));
    }

    public function testNeedsManualReview(): void
    {
        $reviewResult = ['mediaAuditResult' => ['suggestion' => 'review']];
        $passResult = ['mediaAuditResult' => ['suggestion' => 'pass']];

        $this->assertTrue($this->videoAuditService->needsManualReview($reviewResult));
        $this->assertFalse($this->videoAuditService->needsManualReview($passResult));
    }

    public function testIsAuditRejected(): void
    {
        $blockResult = ['mediaAuditResult' => ['suggestion' => 'block']];
        $passResult = ['mediaAuditResult' => ['suggestion' => 'pass']];

        $this->assertTrue($this->videoAuditService->isAuditRejected($blockResult));
        $this->assertFalse($this->videoAuditService->isAuditRejected($passResult));
    }
}