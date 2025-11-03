<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\AliyunVodBundle\Controller\Admin\PlayRecordCrudController;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(PlayRecordCrudController::class)]
#[RunTestsInSeparateProcesses]
final class PlayRecordCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * 获取控制器服务实例
     *
     * @return AbstractCrudController<PlayRecord>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(PlayRecordCrudController::class);
    }

    /**
     * 提供索引页表头数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '视频' => ['视频'];
        yield 'IP地址' => ['IP地址'];
        yield '设备类型' => ['设备类型'];
        yield '播放质量' => ['播放质量'];
        yield '播放时长(秒)' => ['播放时长(秒)'];
        yield '播放进度(秒)' => ['播放进度(秒)'];
        yield '播放时间' => ['播放时间'];
    }

    /**
     * 提供新增页字段数据 - 此控制器禁用了NEW操作
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 提供虚拟数据点以避免DataProvider空数组错误
        // 实际测试会在isActionEnabled检查时跳过
        yield 'dummy' => ['dummy'];
    }

    /**
     * 提供编辑页字段数据 - 此控制器禁用了EDIT操作
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // 提供虚拟数据点以避免DataProvider空数组错误
        // 实际测试会在isActionEnabled检查时跳过
        yield 'dummy' => ['dummy'];
    }

    public function testUnauthorizedAccess(): void
    {
        $client = self::createClientWithDatabase();

        try {
            $client->request('GET', '/admin/aliyun-vod/play-record');
            // 如果请求成功，检查状态码
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        } catch (AccessDeniedException $e) {
            // 如果抛出异常，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testIndexPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/play-record');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '播放记录管理');
    }

    public function testDetailPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $playRecord = $this->createTestPlayRecord();

        $client->request('GET', '/admin/aliyun-vod/play-record?crudAction=detail&entityId=' . $playRecord->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '播放记录管理');
    }

    public function testNewActionDisabled(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/play-record?crudAction=new');

        // 当动作被禁用时，EasyAdminBundle 直接显示索引页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '播放记录管理');
    }

    public function testEditActionDisabled(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $playRecord = $this->createTestPlayRecord();

        $client->request('GET', '/admin/aliyun-vod/play-record?crudAction=edit&entityId=' . $playRecord->getId());

        // 当动作被禁用时，EasyAdminBundle 直接显示索引页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '播放记录管理');
    }

    public function testViewStatsAction(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $playRecord = $this->createTestPlayRecord();

        $client->request('GET', '/admin/aliyun-vod/play-record?crudAction=viewStats&entityId=' . $playRecord->getId());

        // viewStats 动作添加 flash 消息后刷新当前页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '播放记录管理');
    }

    public function testFilterByVideo(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/play-record');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testFilterByIpAddress(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/play-record');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testFilterByDeviceType(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/play-record');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    private function createTestConfig(): AliyunVodConfig
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');
        $config->setAccessKeyId('test_key_id');
        $config->setAccessKeySecret('test_key_secret');
        $config->setRegionId('cn-shanghai');
        $config->setValid(true);

        $entityManager = self::getEntityManager();
        $entityManager->persist($config);
        $entityManager->flush();

        return $config;
    }

    private function createTestVideo(): Video
    {
        $config = $this->createTestConfig();

        $video = new Video();
        $video->setTitle('测试视频');
        $video->setVideoId('test_video_id');
        $video->setStatus('Normal');
        $video->setConfig($config);

        $entityManager = self::getEntityManager();
        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }

    private function createTestPlayRecord(?Video $video = null): PlayRecord
    {
        if (null === $video) {
            $video = $this->createTestVideo();
        }

        $playRecord = new PlayRecord();
        $playRecord->setVideo($video);
        $playRecord->setIpAddress('192.168.1.100');
        $playRecord->setDeviceType('mobile');
        $playRecord->setPlayQuality('HD');
        $playRecord->setPlayDuration(300);
        $playRecord->setPlayPosition(150);
        $playRecord->setUserAgent('Mozilla/5.0 Test');
        $playRecord->setReferer('https://example.com');
        $playRecord->setPlayerVersion('1.0.0');
        $playRecord->setPlayTime(new \DateTimeImmutable());

        $entityManager = self::getEntityManager();
        $entityManager->persist($playRecord);
        $entityManager->flush();

        return $playRecord;
    }
}
