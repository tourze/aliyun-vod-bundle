<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\AliyunVodBundle\Controller\Admin\TranscodeTaskCrudController;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(TranscodeTaskCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TranscodeTaskCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * 获取控制器服务实例
     *
     * @return AbstractCrudController<TranscodeTask>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(TranscodeTaskCrudController::class);
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
        yield '任务ID' => ['任务ID'];
        yield '模板ID' => ['模板ID'];
        yield '状态' => ['状态'];
        yield '进度(%)' => ['进度(%)'];
        yield '创建时间' => ['创建时间'];
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
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/aliyun-vod/transcode-task');
    }

    public function testIndexPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/transcode-task');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '转码任务监控');
    }

    public function testDetailPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $transcodeTask = $this->createTestTranscodeTask();

        $client->request('GET', '/admin/aliyun-vod/transcode-task?crudAction=detail&entityId=' . $transcodeTask->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '转码任务监控');
    }

    public function testNewActionDisabled(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/transcode-task?crudAction=new');

        // 当动作被禁用时，EasyAdminBundle 直接显示索引页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '转码任务监控');
    }

    public function testEditActionDisabled(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $transcodeTask = $this->createTestTranscodeTask();

        $client->request('GET', '/admin/aliyun-vod/transcode-task?crudAction=edit&entityId=' . $transcodeTask->getId());

        // 当动作被禁用时，EasyAdminBundle 直接显示索引页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '转码任务监控');
    }

    public function testRefreshStatusAction(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $transcodeTask = $this->createTestTranscodeTask();

        $client->request('GET', '/admin/aliyun-vod/transcode-task?crudAction=refreshStatus&entityId=' . $transcodeTask->getId());

        // refreshStatus 动作添加 flash 消息后刷新当前页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '转码任务监控');
    }

    public function testFilterByVideo(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/transcode-task');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testFilterByStatus(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/transcode-task');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testFilterByTemplateGroup(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $this->createTestTranscodeTask();

        $client->request('GET', '/admin/aliyun-vod/transcode-task?filters[templateGroupId][value]=test_template_group');

        $this->assertResponseIsSuccessful();
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

    private function createTestTranscodeTask(?Video $video = null): TranscodeTask
    {
        if (null === $video) {
            $video = $this->createTestVideo();
        }

        $transcodeTask = new TranscodeTask();
        $transcodeTask->setVideo($video);
        $transcodeTask->setTaskId('test_task_id_' . uniqid());
        $transcodeTask->setTemplateId('test_template_id');
        $transcodeTask->setStatus('TranscodeSuccess');

        $entityManager = self::getEntityManager();
        $entityManager->persist($transcodeTask);
        $entityManager->flush();

        return $transcodeTask;
    }
}
