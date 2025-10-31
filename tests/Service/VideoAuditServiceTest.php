<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\VideoAuditService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(VideoAuditService::class)]
#[RunTestsInSeparateProcesses]
final class VideoAuditServiceTest extends AbstractIntegrationTestCase
{
    private VideoAuditService $videoAuditService;

    protected function onSetUp(): void
    {
        $this->videoAuditService = self::getService(VideoAuditService::class);
    }

    public function testSubmitAIMediaAuditJobThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

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

    public function testBatchSubmitAuditJobs(): void
    {
        $result = $this->videoAuditService->batchSubmitAuditJobs(['media1', 'media2']);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('media1', $result);
        $this->assertArrayHasKey('media2', $result);
        $this->assertArrayHasKey('error', $result['media1']);
        $this->assertArrayHasKey('error', $result['media2']);
    }

    public function testCheckAuditStatusThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoAuditService->checkAuditStatus('test-job-id');
    }

    public function testGetAIMediaAuditJobThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoAuditService->getAIMediaAuditJob('test-job-id');
    }

    public function testGetMediaAuditResultThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoAuditService->getMediaAuditResult('test-media-id');
    }
}
