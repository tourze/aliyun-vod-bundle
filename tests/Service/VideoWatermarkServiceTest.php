<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\VideoWatermarkService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(VideoWatermarkService::class)]
#[RunTestsInSeparateProcesses]
final class VideoWatermarkServiceTest extends AbstractIntegrationTestCase
{
    private VideoWatermarkService $videoWatermarkService;

    protected function onSetUp(): void
    {
        $this->videoWatermarkService = self::getService(VideoWatermarkService::class);
    }

    public function testAddWatermarkThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        // Network or SSL errors are also acceptable in test environment
        $this->expectExceptionMessageMatches('/(Specified access key is not found|TLS connect error|SSL routines)/');

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

    public function testDeleteWatermarkThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        // Network or SSL errors are also acceptable in test environment
        $this->expectExceptionMessageMatches('/(Specified access key is not found|TLS connect error|SSL routines)/');

        $this->videoWatermarkService->deleteWatermark('test-watermark-id');
    }

    public function testListWatermarksThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        // Network or SSL errors are also acceptable in test environment
        $this->expectExceptionMessageMatches('/(Specified access key is not found|TLS connect error|SSL routines)/');

        $this->videoWatermarkService->listWatermarks();
    }

    public function testUpdateWatermarkThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        // Network or SSL errors are also acceptable in test environment
        $this->expectExceptionMessageMatches('/(Specified access key is not found|TLS connect error|SSL routines)/');

        $this->videoWatermarkService->updateWatermark('test-watermark-id', 'new-name', 'new-config');
    }

    public function testGetWatermarkThrowsExceptionWhenNoConfig(): void
    {
        $this->expectException(TeaUnableRetryError::class);
        // Network or SSL errors are also acceptable in test environment
        $this->expectExceptionMessageMatches('/(Specified access key is not found|TLS connect error|SSL routines)/');

        $this->videoWatermarkService->getWatermark('test-watermark-id');
    }
}
