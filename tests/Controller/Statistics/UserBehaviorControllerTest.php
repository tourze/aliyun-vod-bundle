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
use Tourze\AliyunVodBundle\Controller\Statistics\UserBehaviorController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(UserBehaviorController::class)]
#[RunTestsInSeparateProcesses]
final class UserBehaviorControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        // Setup code if needed
    }

    public function testInvokeWithValidIpAddress(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('POST', '/admin/statistics/user-behavior', [
            'ipAddress' => '192.168.1.1',
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

    public function testInvokeWithEmptyIpAddress(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('POST', '/admin/statistics/user-behavior', [
            'ipAddress' => '',
        ]);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(400);

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
        $this->assertArrayHasKey('message', $content);
        $this->assertSame('IP地址不能为空', $content['message']);
    }

    public function testInvokeWithMissingIpAddress(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('POST', '/admin/statistics/user-behavior');

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(400);

        $responseContent = $response->getContent();
        $this->assertNotFalse($responseContent);
        $content = json_decode($responseContent, true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
    }

    public function testUnauthorizedAccess(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);

        try {
            $client->request('POST', '/admin/statistics/user-behavior', [
                'ipAddress' => '192.168.1.1',
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

        $client->request('POST', '/admin/statistics/user-behavior', [
            'ipAddress' => '192.168.1.1',
        ]);
        $this->assertResponseIsSuccessful();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/admin/statistics/user-behavior');
    }

    public function testPutMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/statistics/user-behavior');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/statistics/user-behavior');
    }

    public function testPatchMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/statistics/user-behavior');
    }

    public function testOptionsMethodNotAllowed(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/statistics/user-behavior');
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
        $client->request($method, '/admin/statistics/user-behavior');
    }
}
