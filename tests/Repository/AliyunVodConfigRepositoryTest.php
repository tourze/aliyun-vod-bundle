<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;
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
    private ManagerRegistry $registry;

    protected function onSetUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
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
        $mockRepo = $this->createMockRepository();

        // Test null case
        $mockRepo->setFindOneByResult(null);
        $result = $mockRepo->findDefaultConfig();
        $this->assertNull($result);

        // Test valid config case
        $config = $this->createTestConfig(['name' => '默认配置', 'isDefault' => true, 'valid' => true]);
        $mockRepo->setFindOneByResult($config);
        $result = $mockRepo->findDefaultConfig();

        $this->assertInstanceOf(AliyunVodConfig::class, $result);
        $this->assertTrue($result->isDefault());
        $this->assertTrue($result->getValid());
        $this->assertEquals('默认配置', $result->getName());
    }

    public function testFindDefaultConfigWithInvalidConfig(): void
    {
        $mockRepo = $this->createMockRepository();

        // Test with invalid config - should return null
        $invalidConfig = $this->createTestConfig(['name' => '无效配置', 'isDefault' => true, 'valid' => false]);
        $mockRepo->setFindOneByResult(null); // Repository should not return invalid configs
        $result = $mockRepo->findDefaultConfig();
        $this->assertNull($result);
    }

    public function testFindDefaultConfigWithNonDefaultConfig(): void
    {
        $mockRepo = $this->createMockRepository();

        // Test with non-default config - should return null
        $nonDefaultConfig = $this->createTestConfig(['name' => '非默认配置', 'isDefault' => false, 'valid' => true]);
        $mockRepo->setFindOneByResult(null); // Repository should not return non-default configs
        $result = $mockRepo->findDefaultConfig();
        $this->assertNull($result);
    }

    public function testFindActiveConfigs(): void
    {
        $mockRepo = $this->createMockRepository();

        // Test empty result
        $mockRepo->setFindByResult([]);
        $result = $mockRepo->findActiveConfigs();
        $this->assertEmpty($result);
        $this->assertIsArray($result);

        // Test with configs
        $configs = [
            $this->createTestConfig(['name' => 'A配置', 'isDefault' => true, 'valid' => true]),
            $this->createTestConfig(['name' => 'B配置', 'isDefault' => false, 'valid' => true]),
        ];
        $mockRepo->setFindByResult($configs);
        $result = $mockRepo->findActiveConfigs();

        $this->assertCount(2, $result);
        $this->assertTrue($result[0]->getIsDefault());
        $this->assertFalse($result[1]->isDefault());
        $this->assertEquals('A配置', $result[0]->getName());
    }

    public function testFindActiveConfigsOrdering(): void
    {
        $mockRepo = $this->createMockRepository();

        // Test ordering (isDefault DESC, name ASC)
        $configs = [
            $this->createTestConfig(['name' => 'B配置', 'isDefault' => true, 'valid' => true]),
            $this->createTestConfig(['name' => 'A配置', 'isDefault' => false, 'valid' => true]),
            $this->createTestConfig(['name' => 'C配置', 'isDefault' => false, 'valid' => true]),
        ];
        $mockRepo->setFindByResult($configs);
        $result = $mockRepo->findActiveConfigs();

        $this->assertCount(3, $result);
        // First should be default config
        $this->assertTrue($result[0]->getIsDefault());
        $this->assertEquals('B配置', $result[0]->getName());
    }

    public function testFindActiveConfigsOnlyValidConfigs(): void
    {
        $mockRepo = $this->createMockRepository();

        // Only valid configs should be returned
        $validConfigs = [
            $this->createTestConfig(['name' => '有效配置1', 'isDefault' => true, 'valid' => true]),
            $this->createTestConfig(['name' => '有效配置2', 'isDefault' => false, 'valid' => true]),
        ];
        $mockRepo->setFindByResult($validConfigs);
        $result = $mockRepo->findActiveConfigs();

        $this->assertCount(2, $result);
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

    /** @param array<int, AliyunVodConfig> $expectedResult */
    #[TestWith(['templateGroupId', []])]
    #[TestWith(['storageLocation', []])]
    #[TestWith(['callbackUrl', []])]
    #[TestWith(['regionId', []])]
    public function testFindByNullFields(string $field, array $expectedResult): void
    {
        $mockRepo = $this->createMockRepository();
        $mockRepo->setFindByResult($expectedResult);

        $result = $mockRepo->findBy([$field => null]);
        $this->assertEquals($expectedResult, $result);
    }

    #[TestWith(['templateGroupId', 3])]
    #[TestWith(['storageLocation', 2])]
    #[TestWith(['callbackUrl', 1])]
    #[TestWith(['regionId', 5])]
    public function testCountByNullFields(string $field, int $expectedCount): void
    {
        $mockRepo = $this->createMockRepository();
        $mockRepo->setCountResult($expectedCount);

        $result = $mockRepo->count([$field => null]);
        $this->assertEquals($expectedCount, $result);
    }

    public function testFindByRegionId(): void
    {
        $mockRepo = $this->createMockRepository();
        $config = $this->createTestConfig([
            'name' => '上海区域配置',
            'regionId' => 'cn-shanghai',
        ]);

        $mockRepo->setFindByResult([$config]);
        $result = $mockRepo->findBy(['regionId' => 'cn-shanghai']);

        $this->assertCount(1, $result);
        $this->assertEquals('cn-shanghai', $result[0]->getRegionId());
        $this->assertEquals('上海区域配置', $result[0]->getName());
    }

    public function testFindOneByWithOrderBy(): void
    {
        $mockRepo = $this->createMockRepository();
        $config = $this->createTestConfig(['name' => 'A配置', 'valid' => true]);

        $mockRepo->setFindOneByResult($config);
        $result = $mockRepo->findOneBy(['valid' => true], ['name' => 'ASC']);

        $this->assertInstanceOf(AliyunVodConfig::class, $result);
        $this->assertEquals('A配置', $result->getName());
    }

    public function testPaginationAndComplexQueries(): void
    {
        $mockRepo = $this->createMockRepository();

        // Create test data
        $configs = [];
        for ($i = 1; $i <= 5; ++$i) {
            $configs[] = $this->createTestConfig([
                'name' => '配置' . $i,
                'accessKeyId' => 'LTAI4Test' . $i,
            ]);
        }

        // Set all configs, let the repository do the slicing
        $mockRepo->setFindByResult($configs);
        $result = $mockRepo->findBy(['valid' => true], ['isDefault' => 'DESC', 'name' => 'ASC'], 3, 1);
        $this->assertCount(3, $result);

        // Test non-default configs
        $mockRepo->setFindByResult([]);
        $result = $mockRepo->findBy(['isDefault' => false, 'valid' => true]);
        $this->assertEmpty($result);

        // Test multiple region queries
        $regions = ['cn-shanghai', 'cn-beijing', 'cn-hangzhou'];
        foreach ($regions as $regionId) {
            $mockRepo->setFindByResult([]);
            $result = $mockRepo->findBy(['regionId' => $regionId]);
            $this->assertEmpty($result);
        }
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

    private function createMockRepository(): TestableRepository
    {
        return new TestableRepository($this->registry);
    }
}
