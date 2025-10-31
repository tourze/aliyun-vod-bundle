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
        $this->expectException(TeaUnableRetryError::class);
        $this->expectExceptionMessage('Specified access key is not found');

        $this->playAuthService->getPlayAuth('test-video-id');
    }

    public function testBatchGetPlayAuthWithInvalidConfig(): void
    {
        // 测试在没有配置的情况下批量获取播放凭证
        $result = $this->playAuthService->batchGetPlayAuth(['video1', 'video2']);

        $this->assertArrayHasKey('video1', $result);
        $this->assertArrayHasKey('video2', $result);
        $this->assertArrayHasKey('error', $result['video1']);
        $this->assertArrayHasKey('error', $result['video2']);
        // 在没有配置的情况下，应该有错误信息
        $this->assertNotEmpty($result['video1']['error']);
    }
}
