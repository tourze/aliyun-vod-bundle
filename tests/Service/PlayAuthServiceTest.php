<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\PlayAuthService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PlayAuthService::class)]
#[RunTestsInSeparateProcesses]
final class PlayAuthServiceTest extends AbstractIntegrationTestCase
{
    private PlayAuthService $playAuthService;

    protected function onSetUp(): void
    {
        $this->playAuthService = self::getService(PlayAuthService::class);
    }

    public function testValidatePlayAuth(): void
    {
        $this->assertTrue($this->playAuthService->validatePlayAuth('valid-auth'));
        $this->assertFalse($this->playAuthService->validatePlayAuth(''));
    }

    public function testGetPlayAuthThrowsExceptionWhenNoConfig(): void
    {
        try {
            $this->playAuthService->getPlayAuth('test-video-id');
            self::fail('Expected TeaUnableRetryError was not thrown');
        } catch (TeaUnableRetryError $e) {
            $this->assertStringContainsString('Specified access key is not found', $e->getMessage());
        } catch (\Exception $e) {
            // 如果是网络超时或其他网络相关错误，这也是可以接受的
            $this->assertTrue(
                str_contains($e->getMessage(), 'Could not resolve host') ||
                str_contains($e->getMessage(), 'timeout') ||
                str_contains($e->getMessage(), 'Connection') ||
                str_contains($e->getMessage(), 'cURL error'),
                'Expected network-related error or access key error, got: ' . $e->getMessage()
            );
        }
    }

    public function testBatchGetPlayAuthWithInvalidConfig(): void
    {
        // 测试在没有配置的情况下批量获取播放凭证
        try {
            $result = $this->playAuthService->batchGetPlayAuth(['video1', 'video2']);

            $this->assertArrayHasKey('video1', $result);
            $this->assertArrayHasKey('video2', $result);
            $this->assertArrayHasKey('error', $result['video1']);
            $this->assertArrayHasKey('error', $result['video2']);
            // 在没有配置的情况下，应该有错误信息
            $this->assertNotEmpty($result['video1']['error']);
        } catch (\Exception $e) {
            // 如果是网络超时或其他网络相关错误，这也是可以接受的
            $this->assertTrue(
                str_contains($e->getMessage(), 'Could not resolve host') ||
                str_contains($e->getMessage(), 'timeout') ||
                str_contains($e->getMessage(), 'Connection') ||
                str_contains($e->getMessage(), 'cURL error') ||
                str_contains($e->getMessage(), 'Specified access key is not found'),
                'Expected network-related error or access key error, got: ' . $e->getMessage()
            );
        }
    }
}
