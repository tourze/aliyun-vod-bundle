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
use Tourze\AliyunVodBundle\Controller\Statistics\VideoPlayController;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(VideoPlayController::class)]
#[RunTestsInSeparateProcesses]
final class VideoPlayControllerTest extends AbstractWebTestCase
{
    public function testGetRequestWithValidVideo(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        $client->request('GET', '/admin/statistics/video-play/' . $video->getId());

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
    }

    public function testPostRequestWithValidVideo(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        $client->request('POST', '/admin/statistics/video-play/' . $video->getId());

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
    }

    public function testGetRequestWithInvalidVideo(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/statistics/video-play/99999');

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPutMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        try {
            $client->request('PUT', '/admin/statistics/video-play/' . $video->getId());
            $response = $client->getResponse();
            $this->assertResponseStatusCodeSame(405);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    public function testDeleteMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        try {
            $client->request('DELETE', '/admin/statistics/video-play/' . $video->getId());
            $response = $client->getResponse();
            $this->assertResponseStatusCodeSame(405);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    public function testPatchMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        try {
            $client->request('PATCH', '/admin/statistics/video-play/' . $video->getId());
            $response = $client->getResponse();
            $this->assertResponseStatusCodeSame(405);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    public function testHeadMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        $client->request('HEAD', '/admin/statistics/video-play/' . $video->getId());

        $response = $client->getResponse();
        $this->assertContains($response->getStatusCode(), [200, 405]);
    }

    public function testOptionsMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $video = $this->createTestVideo();

        try {
            $client->request('OPTIONS', '/admin/statistics/video-play/' . $video->getId());
            $response = $client->getResponse();
            $this->assertContains($response->getStatusCode(), [200, 405]);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    public function testUnauthenticatedAccess(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);

        $video = $this->createTestVideo();

        try {
            $client->request('GET', '/admin/statistics/video-play/' . $video->getId());
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection());
        } catch (AccessDeniedException $e) {
            $this->assertStringContainsString('Access Denied', $e->getMessage());
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

        $video = $this->createTestVideo();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/statistics/video-play/' . $video->getId());
    }
}
