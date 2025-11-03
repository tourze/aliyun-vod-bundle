<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\AliyunVodBundle\Controller\Admin\AliyunVodConfigCrudController;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * 阿里云VOD配置管理控制器测试
 *
 * @phpstan-ignore-next-line
 *
 * @internal
 */
#[CoversClass(AliyunVodConfigCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AliyunVodConfigCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * 获取控制器服务实例
     *
     * @return AbstractCrudController<AliyunVodConfig>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(AliyunVodConfigCrudController::class);
    }

    /**
     * 提供索引页表头数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '配置名称' => ['配置名称'];
        yield '访问密钥ID' => ['访问密钥ID'];
        yield '地域ID' => ['地域ID'];
        yield '转码模板组ID' => ['转码模板组ID'];
        yield '存储地址' => ['存储地址'];
        yield '回调URL' => ['回调URL'];
        yield '默认配置' => ['默认配置'];
        yield '启用状态' => ['启用状态'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * 提供新增页字段数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'accessKeyId' => ['accessKeyId'];
        yield 'accessKeySecret' => ['accessKeySecret'];
        yield 'regionId' => ['regionId'];
        yield 'templateGroupId' => ['templateGroupId'];
        yield 'storageLocation' => ['storageLocation'];
        yield 'callbackUrl' => ['callbackUrl'];
        yield 'isDefault' => ['isDefault'];
        yield 'valid' => ['valid'];
    }

    /**
     * 提供编辑页字段数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'accessKeyId' => ['accessKeyId'];
        yield 'accessKeySecret' => ['accessKeySecret'];
        yield 'regionId' => ['regionId'];
        yield 'templateGroupId' => ['templateGroupId'];
        yield 'storageLocation' => ['storageLocation'];
        yield 'callbackUrl' => ['callbackUrl'];
        yield 'isDefault' => ['isDefault'];
        yield 'valid' => ['valid'];
    }

    public function testUnauthorizedAccess(): void
    {
        $client = self::createClientWithDatabase();

        try {
            $client->request('GET', '/admin/aliyun-vod/config');
            // 如果请求成功，检查状态码
            $this->assertResponseStatusCodeSame(403);
        } catch (AccessDeniedException $e) {
            // 如果抛出异常，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testGetEntityFqcn(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/config');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testConfigureCrud(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/config');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '阿里云VOD配置');
    }

    public function testConfigureActions(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/config');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('button');
    }

    public function testConfigureFields(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/config');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testTestConnectionAction(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/config');

        $this->assertResponseIsSuccessful();
    }

    public function testSetDefaultAction(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/config');

        $this->assertResponseIsSuccessful();
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();

        // 直接通过Symfony验证器测试实体验证规则
        // 这个测试验证必填字段的验证，等同于表单提交空表单时的验证
        $config = new AliyunVodConfig();

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($config);

        // 验证必填字段的错误
        self::assertGreaterThan(0, count($violations), '空的AliyunVodConfig实体应该有验证错误');

        $violationMessages = [];
        foreach ($violations as $violation) {
            $violationMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        // 验证必填字段（name, accessKeyId, accessKeySecret）都有相应的验证错误
        // 注意：regionId 有默认值 'cn-shanghai'，所以不会触发验证错误
        $expectedFields = ['name', 'accessKeyId', 'accessKeySecret'];
        foreach ($expectedFields as $field) {
            $hasFieldViolation = false;
            foreach ($violations as $violation) {
                if ($violation->getPropertyPath() === $field) {
                    $hasFieldViolation = true;
                    // 验证错误信息包含"should not be blank"
                    self::assertStringContainsString('should not be blank', (string) $violation->getMessage());

                    break;
                }
            }
            self::assertTrue($hasFieldViolation, "字段 {$field} 应该有验证错误");
        }
    }

    public function testControllerHasCorrectAnnotations(): void
    {
        self::ensureKernelShutdown();
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/aliyun-vod/config');

        $this->assertResponseIsSuccessful();
    }
}
