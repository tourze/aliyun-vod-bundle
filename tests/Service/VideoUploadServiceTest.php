<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\VideoUploadService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(VideoUploadService::class)]
#[RunTestsInSeparateProcesses]
final class VideoUploadServiceTest extends AbstractIntegrationTestCase
{
    private VideoUploadService $videoUploadService;

    protected function onSetUp(): void
    {
        $this->videoUploadService = self::getService(VideoUploadService::class);
    }

    public function testCreateUploadAuthThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->videoUploadService->createUploadAuth('test-title', 'test-file.mp4');
    }

    public function testRefreshUploadAuthThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

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
