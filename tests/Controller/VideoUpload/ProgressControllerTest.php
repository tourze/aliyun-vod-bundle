<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Controller\VideoUpload;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\AliyunVodBundle\Controller\VideoUpload\ProgressController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(ProgressController::class)]
#[RunTestsInSeparateProcesses]
final class ProgressControllerTest extends AbstractWebTestCase
{
    public function testGetRequestWithValidVideoId(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/video-upload/progress/test-video-id-123');

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();

        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
    }

    public function testPostMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/admin/video-upload/progress/test-video-id');
    }

    public function testPutMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/video-upload/progress/test-video-id');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/video-upload/progress/test-video-id');
    }

    public function testPatchMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/video-upload/progress/test-video-id');
    }

    public function testHeadMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('HEAD', '/admin/video-upload/progress/test-video-id');

        $response = $client->getResponse();
        $this->assertContains($response->getStatusCode(), [200, 405]);
    }

    public function testOptionsMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/video-upload/progress/test-video-id');
    }

    public function testUnauthenticatedAccess(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/video-upload/progress/test-video-id');
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
        $client->request($method, '/admin/video-upload/progress/test-video-id');
    }
}
