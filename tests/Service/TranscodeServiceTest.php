<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\TranscodeService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(TranscodeService::class)]
#[RunTestsInSeparateProcesses]
final class TranscodeServiceTest extends AbstractIntegrationTestCase
{
    private TranscodeService $transcodeService;

    protected function onSetUp(): void
    {
        $this->transcodeService = self::getService(TranscodeService::class);
    }

    public function testSubmitTranscodeJobsThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->transcodeService->submitTranscodeJobs('test-video-id');
    }

    public function testGetTranscodeTaskThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->transcodeService->getTranscodeTask('test-task-id');
    }

    public function testCheckTranscodeStatusThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->transcodeService->checkTranscodeStatus('test-task-id');
    }

    public function testGetTranscodeProgressThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->transcodeService->getTranscodeProgress('test-task-id');
    }
}
