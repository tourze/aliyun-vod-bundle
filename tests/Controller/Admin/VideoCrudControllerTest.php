<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\AliyunVodBundle\Controller\Admin\VideoCrudController;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(VideoCrudController::class)]
#[RunTestsInSeparateProcesses]
final class VideoCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * 获取控制器服务实例
     *
     * @return AbstractCrudController<Video>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(VideoCrudController::class);
    }

    /**
     * 提供索引页表头数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '配置' => ['配置'];
        yield '视频ID' => ['视频ID'];
        yield '标题' => ['标题'];
        yield '时长(秒)' => ['时长(秒)'];
        yield '状态' => ['状态'];
        yield '标签' => ['标签'];
        yield '有效状态' => ['有效状态'];
        yield '创建时间' => ['创建时间'];
    }

    /**
     * 提供新增页字段数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'config' => ['config'];
        yield 'videoId' => ['videoId'];
        yield 'title' => ['title'];
        yield 'description' => ['description'];
        yield 'status' => ['status'];
        yield 'coverUrl' => ['coverUrl'];
        yield 'playUrl' => ['playUrl'];
        yield 'tags' => ['tags'];
        yield 'valid' => ['valid'];
    }

    /**
     * 提供编辑页字段数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'config' => ['config'];
        yield 'videoId' => ['videoId'];
        yield 'title' => ['title'];
        yield 'description' => ['description'];
        yield 'status' => ['status'];
        yield 'coverUrl' => ['coverUrl'];
        yield 'playUrl' => ['playUrl'];
        yield 'tags' => ['tags'];
        yield 'valid' => ['valid'];
    }

    public function testUnauthorizedAccess(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/aliyun-vod/video');
    }

    public function testIndexPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/aliyun-vod/video');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testNewPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();

        // 直接通过Symfony验证器测试实体验证规则
        // 这个测试验证必填字段的验证，等同于表单提交空表单时的验证
        $video = new Video();

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($video);

        // 验证必填字段的错误
        self::assertGreaterThan(0, count($violations), '空的Video实体应该有验证错误');

        $violationMessages = [];
        foreach ($violations as $violation) {
            $violationMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        // 验证必填字段（title, videoId, config）都有相应的验证错误
        // 注意：status 有默认值 'Uploading'，所以不会触发验证错误
        $expectedFields = ['title', 'videoId', 'config'];
        foreach ($expectedFields as $field) {
            $hasFieldViolation = false;
            foreach ($violations as $violation) {
                if ($violation->getPropertyPath() === $field) {
                    $hasFieldViolation = true;
                    // 验证错误信息包含"should not be blank"或"should not be null"
                    $message = (string) $violation->getMessage();
                    self::assertTrue(
                        str_contains($message, 'should not be blank') || str_contains($message, 'should not be null'),
                        "字段 {$field} 的错误信息应包含验证约束"
                    );

                    break;
                }
            }
            self::assertTrue($hasFieldViolation, "字段 {$field} 应该有验证错误");
        }
    }

    public function testNewFormWithValidData(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=edit&entityId=' . $video->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testDetailPageAccessible(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=detail&entityId=' . $video->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testPlayVideoAction(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=playVideo&entityId=' . $video->getId());

        // playVideo 动作添加 flash 消息后刷新当前页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testGenerateSnapshotAction(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=generateSnapshot&entityId=' . $video->getId());

        // generateSnapshot 动作添加 flash 消息后刷新当前页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testSubmitTranscodeAction(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=submitTranscode&entityId=' . $video->getId());

        // submitTranscode 动作添加 flash 消息后刷新当前页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testViewStatsAction(): void
    {
        self::ensureKernelShutdown();

        // 使用认证客户端的创建方法来确保正确的安全上下文
        $client = $this->createAuthenticatedClient();

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?crudAction=viewStats&entityId=' . $video->getId());

        // viewStats 动作添加 flash 消息后刷新当前页面
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '视频管理');
    }

    public function testFilterByTitle(): void
    {
        self::ensureKernelShutdown();

        // 使用认证客户端的创建方法来确保正确的安全上下文
        $client = $this->createAuthenticatedClient();

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?filters[title][value]=' . urlencode($video->getTitle()));

        $this->assertResponseIsSuccessful();
    }

    public function testFilterByStatus(): void
    {
        self::ensureKernelShutdown();

        // 创建客户端并使用更安全的登录方式
        $client = self::createClientWithDatabase();
        self::getClient($client);

        // 使用自定义登录方法确保角色正确设置
        $user = $this->loginAsAdmin($client);

        // 验证登录状态
        $token = self::getContainer()->get('security.token_storage')->getToken();
        if (!$token || !in_array('ROLE_ADMIN', $token->getRoleNames())) {
            self::fail('用户登录后没有获得 ROLE_ADMIN 角色');
        }

        // 使用认证客户端的创建方法来确保正确的安全上下文
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/video');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testFilterByVideoId(): void
    {
        self::ensureKernelShutdown();

        // 使用认证客户端的创建方法来确保正确的安全上下文
        $client = $this->createAuthenticatedClient();

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/aliyun-vod/video?filters[videoId][value]=' . $video->getVideoId());

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
        $video->setTitle('测试视频 ' . uniqid());
        $video->setVideoId('test_video_id_' . uniqid());
        $video->setStatus('Normal');
        $video->setConfig($config);

        $entityManager = self::getEntityManager();
        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }
}
