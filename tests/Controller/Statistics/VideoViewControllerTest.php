<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Controller\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\AliyunVodBundle\Controller\Statistics\VideoViewController;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(VideoViewController::class)]
#[RunTestsInSeparateProcesses]
final class VideoViewControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidVideoId(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/statistics/video-view', [
            'videoId' => (string) $video->getId(),
        ]);

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('data', $content);
    }

    public function testInvokeWithPostMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        $client->request('POST', '/admin/statistics/video-view', [
            'videoId' => (string) $video->getId(),
        ]);

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
    }

    public function testInvokeWithPatchMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        $client->request('PATCH', '/admin/statistics/video-view', [
            'videoId' => (string) $video->getId(),
        ]);

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
    }

    public function testInvokeWithMissingVideoId(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/statistics/video-view');

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(400);

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
        $this->assertArrayHasKey('message', $content);
        $this->assertSame('缺少videoId参数', $content['message']);
    }

    public function testInvokeWithInvalidVideoId(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/statistics/video-view', [
            'videoId' => '99999',
        ]);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(404);

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
        $this->assertArrayHasKey('message', $content);
        $this->assertSame('视频不存在', $content['message']);
    }

    public function testUnauthorizedAccess(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->disableReboot();

        $video = $this->createTestVideo();

        try {
            $client->request('GET', '/admin/statistics/video-view', [
                'videoId' => (string) $video->getId(),
            ]);
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection() || 401 === $response->getStatusCode() || 403 === $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            $this->assertStringContainsString('Access Denied', $e->getMessage());
        }
    }

    public function testSupportedHttpMethods(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        // Test allowed methods
        $client->request('GET', '/admin/statistics/video-view', [
            'videoId' => (string) $video->getId(),
        ]);
        $this->assertResponseIsSuccessful();

        // Test not allowed methods
        try {
            $client->request('PUT', '/admin/statistics/video-view');
            $this->assertResponseStatusCodeSame(405);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }

        try {
            $client->request('DELETE', '/admin/statistics/video-view');
            $this->assertResponseStatusCodeSame(405);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    protected function onSetUp(): void
    {
        // Setup code if needed
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

    #[Test]
    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        // @phpstan-ignore-next-line
        $client->request($method, '/admin/statistics/video-view');
    }
}
