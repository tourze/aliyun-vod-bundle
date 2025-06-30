<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\PlayAuthService;
use Tourze\AliyunVodBundle\Service\VodClientFactory;

/**
 * @covers \Tourze\AliyunVodBundle\Service\PlayAuthService
 */
class PlayAuthServiceTest extends TestCase
{
    private PlayAuthService $playAuthService;
    private MockObject|VodClientFactory $clientFactoryMock;
    private MockObject|AliyunVodConfigService $configServiceMock;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(VodClientFactory::class);
        $this->configServiceMock = $this->createMock(AliyunVodConfigService::class);
        
        $this->playAuthService = new PlayAuthService(
            $this->clientFactoryMock,
            $this->configServiceMock
        );
    }

    public function testGetPlayAuthThrowsExceptionWhenNoConfig(): void
    {
        $this->configServiceMock
            ->expects($this->once())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $this->expectException(AliyunVodException::class);
        $this->expectExceptionMessage('未找到可用的阿里云VOD配置');

        $this->playAuthService->getPlayAuth('test-video-id');
    }

    public function testValidatePlayAuth(): void
    {
        $this->assertTrue($this->playAuthService->validatePlayAuth('valid-auth'));
        $this->assertFalse($this->playAuthService->validatePlayAuth(''));
    }

    public function testBatchGetPlayAuth(): void
    {
        $this->configServiceMock
            ->expects($this->atLeastOnce())
            ->method('getDefaultConfig')
            ->willReturn(null);

        $result = $this->playAuthService->batchGetPlayAuth(['video1', 'video2']);
        $this->assertArrayHasKey('video1', $result);
        $this->assertArrayHasKey('video2', $result);
        $this->assertArrayHasKey('error', $result['video1']);
        $this->assertArrayHasKey('error', $result['video2']);
    }
}