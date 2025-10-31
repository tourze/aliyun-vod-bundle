<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\VideoSnapshotService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(VideoSnapshotService::class)]
#[RunTestsInSeparateProcesses]
final class VideoSnapshotServiceTest extends AbstractIntegrationTestCase
{
    private VideoSnapshotService $videoSnapshotService;

    protected function onSetUp(): void
    {
        $this->videoSnapshotService = self::getService(VideoSnapshotService::class);
    }

    public function testSubmitSnapshotJobThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoSnapshotService->submitSnapshotJob('test-video-id');
    }

    public function testBatchGenerateSnapshots(): void
    {
        $result = $this->videoSnapshotService->batchGenerateSnapshots(['video1', 'video2']);
        $this->assertArrayHasKey('video1', $result);
        $this->assertArrayHasKey('video2', $result);
        $this->assertArrayHasKey('error', $result['video1']);
        $this->assertArrayHasKey('error', $result['video2']);
    }

    public function testGenerateSnapshotAtTimeThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoSnapshotService->generateSnapshotAtTime('test-video-id', 5);
    }

    public function testGetVideoSnapshotsThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoSnapshotService->getVideoSnapshots('test-video-id');
    }
}
