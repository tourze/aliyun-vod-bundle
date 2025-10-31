<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * 阿里云VOD配置服务测试
 *
 * @internal
 */
#[CoversClass(AliyunVodConfigService::class)]
#[RunTestsInSeparateProcesses]
final class AliyunVodConfigServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 无需特殊设置
    }

    public function testCreateConfigWithValidParameters(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        $config = $service->createConfig(
            'test-config',
            'LTAI4Test123456789012345',
            'test-secret-key',
            'cn-shanghai',
            false
        );

        $this->assertInstanceOf(AliyunVodConfig::class, $config);
        $this->assertEquals('test-config', $config->getName());
        $this->assertEquals('LTAI4Test123456789012345', $config->getAccessKeyId());
        $this->assertEquals('cn-shanghai', $config->getRegionId());
        $this->assertFalse($config->isDefault());
    }

    public function testDecryptSecretWithValidBase64(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        $originalSecret = 'my-secret-key-123';
        $encryptedSecret = base64_encode($originalSecret);

        $result = $service->decryptSecret($encryptedSecret);
        $this->assertEquals($originalSecret, $result);
    }

    public function testDecryptSecretWithInvalidBase64ReturnsOriginal(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        $invalidBase64 = 'not-valid-base64-string!';
        $result = $service->decryptSecret($invalidBase64);

        $this->assertIsString($result);
        $this->assertEquals($invalidBase64, $result);
    }

    public function testDecryptSecretWithEmptyString(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        $result = $service->decryptSecret('');
        $this->assertEquals('', $result);
    }

    public function testDeleteConfig(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        // Create a config first
        $config = $service->createConfig(
            'test-config-to-delete',
            'LTAI4Test123456789012345',
            'test-secret-key',
            'cn-shanghai',
            false
        );

        $this->assertNotNull($config->getId());
        $configId = $config->getId();

        // Delete the config
        $service->deleteConfig($config);

        // Verify the config is deleted
        $em = self::getEntityManager();
        $deletedConfig = $em->find(AliyunVodConfig::class, $configId);
        $this->assertNull($deletedConfig);
    }

    public function testUpdateConfig(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        // Create a config first
        $config = $service->createConfig(
            'test-config-to-update',
            'LTAI4Test123456789012345',
            'test-secret-key',
            'cn-shanghai',
            false
        );

        $this->assertFalse($config->isDefault());

        // Update the config to be default
        $config->setIsDefault(true);
        $config->setName('updated-config-name');

        $service->updateConfig($config);

        // Verify the config is updated
        $em = self::getEntityManager();
        $em->refresh($config);

        $this->assertTrue($config->isDefault());
        $this->assertEquals('updated-config-name', $config->getName());
    }

    public function testUpdateConfigWithDefaultHandling(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        // Create first config as default
        $config1 = $service->createConfig(
            'first-config',
            'LTAI4Test123456789012345',
            'test-secret-key',
            'cn-shanghai',
            true
        );

        $this->assertTrue($config1->isDefault());

        // Create second config
        $config2 = $service->createConfig(
            'second-config',
            'LTAI4Test123456789012346',
            'test-secret-key-2',
            'cn-shanghai',
            false
        );

        $this->assertFalse($config2->isDefault());

        // Update second config to be default
        $config2->setIsDefault(true);
        $service->updateConfig($config2);

        // Verify first config is no longer default and second config is default
        $em = self::getEntityManager();
        $em->refresh($config1);
        $em->refresh($config2);

        $this->assertFalse($config1->isDefault());
        $this->assertTrue($config2->isDefault());
    }

    public function testDeleteConfigWithAssociatedEntities(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        // Create a config
        $config = $service->createConfig(
            'config-with-associations',
            'LTAI4Test123456789012345',
            'test-secret-key',
            'cn-shanghai',
            false
        );

        $configId = $config->getId();
        $this->assertNotNull($configId);

        // Delete the config (should handle cascading properly)
        $service->deleteConfig($config);

        // Verify the config is deleted
        $em = self::getEntityManager();
        $deletedConfig = $em->find(AliyunVodConfig::class, $configId);
        $this->assertNull($deletedConfig);
    }

    public function testUpdateConfigWithNonDefaultConfig(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        // Create a non-default config
        $config = $service->createConfig(
            'non-default-config',
            'LTAI4Test123456789012345',
            'test-secret-key',
            'cn-shanghai',
            false
        );

        $this->assertFalse($config->isDefault());

        // Update the config (keep it non-default)
        $config->setName('updated-non-default-config');
        $config->setRegionId('cn-beijing');

        $service->updateConfig($config);

        // Verify the config is updated but still not default
        $em = self::getEntityManager();
        $em->refresh($config);

        $this->assertFalse($config->isDefault());
        $this->assertEquals('updated-non-default-config', $config->getName());
        $this->assertEquals('cn-beijing', $config->getRegionId());
    }

    public function testDeleteDefaultConfig(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        // Create a default config
        $config = $service->createConfig(
            'default-config-to-delete',
            'LTAI4Test123456789012345',
            'test-secret-key',
            'cn-shanghai',
            true
        );

        $this->assertTrue($config->isDefault());
        $configId = $config->getId();

        // Delete the default config
        $service->deleteConfig($config);

        // Verify the config is deleted
        $em = self::getEntityManager();
        $deletedConfig = $em->find(AliyunVodConfig::class, $configId);
        $this->assertNull($deletedConfig);
    }

    public function testUpdateConfigMultipleConfigs(): void
    {
        $service = self::getService(AliyunVodConfigService::class);

        // Create multiple configs
        $config1 = $service->createConfig(
            'config-1',
            'LTAI4Test123456789012345',
            'test-secret-key-1',
            'cn-shanghai',
            true
        );

        $config2 = $service->createConfig(
            'config-2',
            'LTAI4Test123456789012346',
            'test-secret-key-2',
            'cn-beijing',
            false
        );

        $config3 = $service->createConfig(
            'config-3',
            'LTAI4Test123456789012347',
            'test-secret-key-3',
            'cn-hangzhou',
            false
        );

        // Update config2 to be default
        $config2->setIsDefault(true);
        $service->updateConfig($config2);

        // Verify only config2 is default
        $em = self::getEntityManager();
        $em->refresh($config1);
        $em->refresh($config2);
        $em->refresh($config3);

        $this->assertFalse($config1->isDefault());
        $this->assertTrue($config2->isDefault());
        $this->assertFalse($config3->isDefault());
    }
}
