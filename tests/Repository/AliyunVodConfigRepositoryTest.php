<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * 阿里云VOD配置仓储测试
 *
 * @internal
 */
#[CoversClass(AliyunVodConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class AliyunVodConfigRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository test setup - ensure clean state before each test
        self::getEntityManager()->clear();
    }

    public function testRepositoryConstruction(): void
    {
        try {
            $repository = self::getService(AliyunVodConfigRepository::class);

            $this->assertNotNull($repository);
            $this->assertInstanceOf(AliyunVodConfigRepository::class, $repository);
            $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
        } catch (\LogicException $e) {
            // 如果实体管理器不可用，跳过此测试
            if (str_contains($e->getMessage(), 'Could not find the entity manager')) {
                self::markTestSkipped('Entity manager not available for AliyunVodConfig');
            }
            throw $e;
        }
    }

    public function testFindDefaultConfig(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        // Test null case - no default config
        $result = $repository->findDefaultConfig();
        $this->assertNull($result);

        // Create valid default config
        $config = $this->createTestConfig(['name' => '默认配置', 'isDefault' => true, 'valid' => true]);
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $result = $repository->findDefaultConfig();
        $this->assertInstanceOf(AliyunVodConfig::class, $result);
        $this->assertTrue($result->isDefault());
        $this->assertTrue($result->getValid());
        $this->assertEquals('默认配置', $result->getName());
    }

    public function testFindDefaultConfigWithInvalidConfig(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        // Create invalid config - should not be returned
        $invalidConfig = $this->createTestConfig(['name' => '无效配置', 'isDefault' => true, 'valid' => false]);
        self::getEntityManager()->persist($invalidConfig);
        self::getEntityManager()->flush();

        $result = $repository->findDefaultConfig();
        $this->assertNull($result, 'Repository should not return invalid configs');
    }

    public function testFindDefaultConfigWithNonDefaultConfig(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        // Create non-default config - should not be returned
        $nonDefaultConfig = $this->createTestConfig(['name' => '非默认配置', 'isDefault' => false, 'valid' => true]);
        self::getEntityManager()->persist($nonDefaultConfig);
        self::getEntityManager()->flush();

        $result = $repository->findDefaultConfig();
        $this->assertNull($result, 'Repository should not return non-default configs');
    }

    public function testFindActiveConfigs(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        // Test empty result
        $result = $repository->findActiveConfigs();
        $this->assertEmpty($result);
        $this->assertIsArray($result);

        // Create test configs
        $configA = $this->createTestConfig(['name' => 'A配置', 'isDefault' => true, 'valid' => true]);
        $configB = $this->createTestConfig(['name' => 'B配置', 'isDefault' => false, 'valid' => true]);
        self::getEntityManager()->persist($configA);
        self::getEntityManager()->persist($configB);
        self::getEntityManager()->flush();

        $result = $repository->findActiveConfigs();
        $this->assertCount(2, $result);
        $this->assertTrue($result[0]->getIsDefault());
        $this->assertFalse($result[1]->isDefault());
        $this->assertEquals('A配置', $result[0]->getName());
    }

    public function testFindActiveConfigsOrdering(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        // Create configs with specific ordering requirements (isDefault DESC, name ASC)
        $configB = $this->createTestConfig(['name' => 'B配置', 'isDefault' => true, 'valid' => true]);
        $configA = $this->createTestConfig(['name' => 'A配置', 'isDefault' => false, 'valid' => true]);
        $configC = $this->createTestConfig(['name' => 'C配置', 'isDefault' => false, 'valid' => true]);
        self::getEntityManager()->persist($configB);
        self::getEntityManager()->persist($configA);
        self::getEntityManager()->persist($configC);
        self::getEntityManager()->flush();

        $result = $repository->findActiveConfigs();
        $this->assertCount(3, $result);
        // First should be default config
        $this->assertTrue($result[0]->getIsDefault());
        $this->assertEquals('B配置', $result[0]->getName());
        // Then by name ASC
        $this->assertEquals('A配置', $result[1]->getName());
        $this->assertEquals('C配置', $result[2]->getName());
    }

    public function testFindActiveConfigsOnlyValidConfigs(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        // Create valid and invalid configs
        $validConfig1 = $this->createTestConfig(['name' => '有效配置1', 'isDefault' => true, 'valid' => true]);
        $validConfig2 = $this->createTestConfig(['name' => '有效配置2', 'isDefault' => false, 'valid' => true]);
        $invalidConfig = $this->createTestConfig(['name' => '无效配置', 'isDefault' => false, 'valid' => false]);
        self::getEntityManager()->persist($validConfig1);
        self::getEntityManager()->persist($validConfig2);
        self::getEntityManager()->persist($invalidConfig);
        self::getEntityManager()->flush();

        $result = $repository->findActiveConfigs();
        $this->assertCount(2, $result, 'Should only return valid configs');
        foreach ($result as $config) {
            $this->assertTrue($config->getValid());
        }
    }

    public function testAliyunVodConfigSaveWithFlush(): void
    {
        try {
            $repository = self::getService(AliyunVodConfigRepository::class);
            $config = $this->createTestConfig();

            $repository->save($config);

            // 验证实体已保存
            $entityManager = self::getEntityManager();
            $this->assertTrue($entityManager->contains($config));
        } catch (\LogicException $e) {
            // 如果实体管理器不可用，跳过此测试
            if (str_contains($e->getMessage(), 'Could not find the entity manager')) {
                self::markTestSkipped('Entity manager not available for AliyunVodConfig');
            }
            throw $e;
        }
    }

    public function testAliyunVodConfigSaveWithoutFlush(): void
    {
        try {
            $repository = self::getService(AliyunVodConfigRepository::class);
            $config = $this->createTestConfig();

            $repository->save($config, false);

            // 验证实体已持久化但未刷新
            $entityManager = self::getEntityManager();
            $this->assertTrue($entityManager->contains($config));
        } catch (\LogicException $e) {
            // 如果实体管理器不可用，跳过此测试
            if (str_contains($e->getMessage(), 'Could not find the entity manager')) {
                self::markTestSkipped('Entity manager not available for AliyunVodConfig');
            }
            throw $e;
        }
    }

    public function testAliyunVodConfigRemoveWithFlush(): void
    {
        try {
            $repository = self::getService(AliyunVodConfigRepository::class);
            $config = $this->createTestConfig();

            // 先保存实体
            $repository->save($config);

            // 然后删除
            $repository->remove($config);

            // 验证实体已从管理器中移除
            $entityManager = self::getEntityManager();
            $this->assertFalse($entityManager->contains($config));
        } catch (\LogicException $e) {
            // 如果实体管理器不可用，跳过此测试
            if (str_contains($e->getMessage(), 'Could not find the entity manager')) {
                self::markTestSkipped('Entity manager not available for AliyunVodConfig');
            }
            throw $e;
        }
    }

    public function testAliyunVodConfigRemoveWithoutFlush(): void
    {
        try {
            $repository = self::getService(AliyunVodConfigRepository::class);
            $config = $this->createTestConfig();

            // 先保存实体
            $repository->save($config);

            // 然后删除但不刷新
            $repository->remove($config, false);

            // 验证实体已标记为删除
            $entityManager = self::getEntityManager();
            $this->assertFalse($entityManager->contains($config));
        } catch (\LogicException $e) {
            // 如果实体管理器不可用，跳过此测试
            if (str_contains($e->getMessage(), 'Could not find the entity manager')) {
                self::markTestSkipped('Entity manager not available for AliyunVodConfig');
            }
            throw $e;
        }
    }

    /**
     * 测试可空字段的查询
     * 注意：基础的 findBy/count/findOneBy 功能已在 AbstractRepositoryTestCase 中覆盖
     */
    public function testFindByNullableFields(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        // Create config with null optional fields
        $config = $this->createTestConfig([
            'name' => '测试配置',
            'templateGroupId' => null,
            'storageLocation' => null,
        ]);
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        // Test querying by null values
        $result = $repository->findBy(['templateGroupId' => null]);
        $this->assertCount(1, $result);
        $this->assertEquals('测试配置', $result[0]->getName());
    }

    public function testFindByRegionId(): void
    {
        $repository = $this->getRepository();

        // 清空现有数据
        foreach ($repository->findAll() as $config) {
            self::getEntityManager()->remove($config);
        }
        self::getEntityManager()->flush();

        $config = $this->createTestConfig([
            'name' => '上海区域配置',
            'regionId' => 'cn-shanghai',
        ]);
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $result = $repository->findBy(['regionId' => 'cn-shanghai']);
        $this->assertCount(1, $result);
        $this->assertEquals('cn-shanghai', $result[0]->getRegionId());
        $this->assertEquals('上海区域配置', $result[0]->getName());
    }

    /** @return ServiceEntityRepository<AliyunVodConfig> */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(AliyunVodConfigRepository::class);
    }

    protected function createNewEntity(): object
    {
        return $this->createTestConfig([
            'name' => '测试配置_' . uniqid(),
            'accessKeyId' => 'LTAI4Test_' . uniqid(),
            'accessKeySecret' => 'test_secret_' . uniqid(),
            'regionId' => 'cn-shanghai',
            'valid' => true,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function createTestConfig(array $attributes = []): AliyunVodConfig
    {
        $config = new AliyunVodConfig();

        $defaults = [
            'name' => 'Test Config',
            'accessKeyId' => 'LTAI4Test',
            'accessKeySecret' => 'test_secret',
            'valid' => true,
            'isDefault' => false,
        ];

        $data = array_merge($defaults, $attributes);
        $this->setConfigProperties($config, $data);

        return $config;
    }

    /** @param array<string, mixed> $data */
    private function setConfigProperties(AliyunVodConfig $config, array $data): void
    {
        foreach ($data as $property => $value) {
            $this->setConfigProperty($config, $property, $value);
        }
    }

    private function setConfigProperty(AliyunVodConfig $config, string $property, mixed $value): void
    {
        switch ($property) {
            case 'name':
                if (is_string($value)) {
                    $config->setName($value);
                }
                break;
            case 'accessKeyId':
                if (is_string($value)) {
                    $config->setAccessKeyId($value);
                }
                break;
            case 'accessKeySecret':
                if (is_string($value)) {
                    $config->setAccessKeySecret($value);
                }
                break;
            case 'regionId':
                if (is_string($value)) {
                    $config->setRegionId($value);
                }
                break;
            case 'templateGroupId':
                if (null === $value || is_string($value)) {
                    $config->setTemplateGroupId($value);
                }
                break;
            case 'storageLocation':
                if (null === $value || is_string($value)) {
                    $config->setStorageLocation($value);
                }
                break;
            case 'callbackUrl':
                if (null === $value || is_string($value)) {
                    $config->setCallbackUrl($value);
                }
                break;
            case 'isDefault':
                if (is_bool($value)) {
                    $config->setIsDefault($value);
                }
                break;
            case 'valid':
                if (is_bool($value)) {
                    $config->setValid($value);
                }
                break;
        }
    }

}
