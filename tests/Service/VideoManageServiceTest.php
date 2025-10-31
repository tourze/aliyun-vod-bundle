<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\VideoManageService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(VideoManageService::class)]
#[RunTestsInSeparateProcesses]
final class VideoManageServiceTest extends AbstractIntegrationTestCase
{
    private VideoManageService $videoManageService;

    protected function onSetUp(): void
    {
        $this->videoManageService = self::getService(VideoManageService::class);
    }

    public function testGetVideoInfoThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoManageService->getVideoInfo('test-video-id');
    }

    public function testBatchDeleteVideos(): void
    {
        $this->expectException(TeaUnableRetryError::class);

        $this->videoManageService->batchDeleteVideos(['video1', 'video2']);
    }

    public function testDeleteVideoThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoManageService->deleteVideo('test-video-id');
    }

    public function testUpdateVideoInfoThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoManageService->updateVideoInfo(
            'test-video-id',
            'New Title',
            'New Description',
            'tag1,tag2'
        );
    }

    public function testGetPlayInfoThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoManageService->getPlayInfo('test-video-id');
    }
}
