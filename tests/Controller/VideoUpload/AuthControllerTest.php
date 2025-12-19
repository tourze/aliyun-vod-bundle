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
use Tourze\AliyunVodBundle\Controller\VideoUpload\AuthController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(AuthController::class)]
#[RunTestsInSeparateProcesses]
final class AuthControllerTest extends AbstractWebTestCase
{
    public function testPostRequestWithValidData(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);

        // 使用更可靠的登录方法，直接创建内存管理员用户
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('POST', '/admin/video-upload/auth', [
            'title' => 'Test Video',
            'fileName' => 'test.mp4',
        ]);

        $response = $client->getResponse();
        // 由于没有真实的阿里云配置，服务会返回 500 错误
        $this->assertResponseStatusCodeSame(500);

        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('message', $data);
    }

    public function testPostRequestWithMissingData(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('POST', '/admin/video-upload/auth');

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/admin/video-upload/auth');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/video-upload/auth');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/video-upload/auth');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/video-upload/auth');
    }

    public function testHeadMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('HEAD', '/admin/video-upload/auth');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/video-upload/auth');
    }

    public function testUnauthenticatedAccess(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('POST', '/admin/video-upload/auth', [
            'title' => 'Test Video',
            'fileName' => 'test.mp4',
        ]);
    }

    #[Test]
    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/video-upload/auth');
    }
}
