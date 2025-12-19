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
use Tourze\AliyunVodBundle\Controller\Statistics\PopularController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(PopularController::class)]
#[RunTestsInSeparateProcesses]
final class PopularControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        // Setup code if needed
    }

    public function testGetRequestSuccess(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/statistics/popular');

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();

        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
    }

    public function testGetRequestWithLimit(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/statistics/popular?limit=20');

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
        $client->request('POST', '/admin/statistics/popular');
    }

    public function testPutMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/statistics/popular');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/statistics/popular');
    }

    public function testPatchMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/statistics/popular');
    }

    public function testHeadMethod(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('HEAD', '/admin/statistics/popular');

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
        $client->request('OPTIONS', '/admin/statistics/popular');
    }

    public function testUnauthenticatedAccess(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/statistics/popular');
    }

    #[Test]
    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/statistics/popular');
    }
}
