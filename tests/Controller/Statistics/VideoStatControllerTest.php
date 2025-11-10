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
use Tourze\AliyunVodBundle\Controller\Statistics\VideoStatController;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(VideoStatController::class)]
#[RunTestsInSeparateProcesses]
final class VideoStatControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidVideo(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $videoId = $this->createTestVideo();
        $client->request('GET', "/admin/statistics/video-stat/{$videoId}");

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
        $this->assertIsArray($content['data']);
        $this->assertArrayHasKey('playStats', $content['data']);
        $this->assertArrayHasKey('completionRate', $content['data']);
    }

    public function testInvokeWithInvalidVideo(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/statistics/video-stat/99999');

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

    public function testInvokeWithPostMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $videoId = $this->createTestVideo();
        $client->request('POST', "/admin/statistics/video-stat/{$videoId}");

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

    public function testInvokeWithPutMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $videoId = $this->createTestVideo();
        $client->request('PUT', "/admin/statistics/video-stat/{$videoId}");

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

    public function testUnauthorizedAccess(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->disableReboot();

        try {
            $client->request('GET', '/admin/statistics/video-stat/1');
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

        $videoId = $this->createTestVideo();

        $client->request('GET', "/admin/statistics/video-stat/{$videoId}");
        $this->assertResponseIsSuccessful();

        try {
            $client->request('DELETE', "/admin/statistics/video-stat/{$videoId}");
            $this->assertResponseStatusCodeSame(405);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }

        try {
            $client->request('PATCH', "/admin/statistics/video-stat/{$videoId}");
            $this->assertResponseStatusCodeSame(405);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    private function createTestVideo(): int
    {
        $em = self::getEntityManager();

        $config = new AliyunVodConfig();
        $config->setName('Test Config');
        $config->setAccessKeyId('test_key');
        $config->setAccessKeySecret('test_secret');
        $config->setRegionId('cn-shanghai');
        $config->setStorageLocation('test-bucket');
        $config->setIsDefault(true);

        $em->persist($config);

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('Test Video');
        $video->setDescription('Test Description');
        $video->setDuration(300);
        $video->setSize(10485760);
        $video->setStatus('Normal');
        $video->setCoverUrl('https://example.com/cover.jpg');
        $video->setPlayUrl('https://example.com/video.mp4');
        $video->setTags('test');
        $video->setValid(true);

        $em->persist($video);
        $em->flush();

        return $video->getId();
    }

    protected function onSetUp(): void
    {
    }

    #[Test]
    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $videoId = $this->createTestVideo();

        $this->expectException(MethodNotAllowedHttpException::class);
        // @phpstan-ignore-next-line
        $client->request($method, '/admin/statistics/video-stat/' . $videoId);
    }
}
