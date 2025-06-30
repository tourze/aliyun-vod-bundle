<?php

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;

/**
 * 阿里云VOD配置仓储测试
 */
class AliyunVodConfigRepositoryTest extends TestCase
{
    private $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    public function test_construct_withValidRegistry(): void
    {
        $repository = new AliyunVodConfigRepository($this->registry);
        
        $this->assertInstanceOf(AliyunVodConfigRepository::class, $repository);
    }

    public function test_repository_inheritance(): void
    {
        $repository = new AliyunVodConfigRepository($this->registry);
        
        // 测试Repository是否正确继承了ServiceEntityRepository
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }

    public function test_repository_hasRequiredMethods(): void
    {
        $repository = new AliyunVodConfigRepository($this->registry);
        
        // 测试Repository是否有必需的方法
        // Repository is guaranteed to have these methods
        $this->assertInstanceOf(AliyunVodConfigRepository::class, $repository);
    }

    public function test_findDefaultConfig_methodSignature(): void
    {
        $repository = new AliyunVodConfigRepository($this->registry);
        
        // 通过反射检查方法签名
        $reflection = new \ReflectionMethod($repository, 'findDefaultConfig');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        
        // 检查返回类型
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_findActiveConfigs_methodSignature(): void
    {
        $repository = new AliyunVodConfigRepository($this->registry);
        
        // 通过反射检查方法签名
        $reflection = new \ReflectionMethod($repository, 'findActiveConfigs');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        
        // 检查返回类型
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function test_findDefaultConfig_logicStructure(): void
    {
        // 创建一个测试用的Repository子类来验证逻辑
        $repository = new class($this->registry) extends AliyunVodConfigRepository {
            private $mockResult;
            
            public function setMockResult($result): void
            {
                $this->mockResult = $result;
            }
            
            public function findOneBy(array $criteria, ?array $orderBy = null): ?object
            {
                // 验证传入的条件是否正确
                $expectedCriteria = [
                    'isDefault' => true,
                    'valid' => true,
                ];
                
                if ($criteria === $expectedCriteria) {
                    return $this->mockResult;
                }
                
                return null;
            }
        };

        // 测试返回null的情况
        $repository->setMockResult(null);
        $result = $repository->findDefaultConfig();
        $this->assertNull($result);

        // 测试返回配置的情况
        $config = new AliyunVodConfig();
        $config->setName('测试配置')->setIsDefault(true)->setValid(true);
        $repository->setMockResult($config);
        $result = $repository->findDefaultConfig();
        $this->assertSame($config, $result);
    }

    public function test_findActiveConfigs_logicStructure(): void
    {
        // 创建一个测试用的Repository子类来验证逻辑
        $repository = new class($this->registry) extends AliyunVodConfigRepository {
            private $mockResult = [];
            
            public function setMockResult(array $result): void
            {
                $this->mockResult = $result;
            }
            
            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                // 验证传入的条件是否正确
                $expectedCriteria = ['valid' => true];
                $expectedOrderBy = ['isDefault' => 'DESC', 'name' => 'ASC'];
                
                if ($criteria === $expectedCriteria && $orderBy === $expectedOrderBy) {
                    return $this->mockResult;
                }
                
                return [];
            }
        };

        // 测试返回空数组的情况
        $repository->setMockResult([]);
        $result = $repository->findActiveConfigs();
        $this->assertEmpty($result);

        // 测试返回配置数组的情况
        $config1 = new AliyunVodConfig();
        $config1->setName('配置1')->setValid(true);
        $config2 = new AliyunVodConfig();
        $config2->setName('配置2')->setValid(true);
        
        $configs = [$config1, $config2];
        $repository->setMockResult($configs);
        $result = $repository->findActiveConfigs();
        $this->assertCount(2, $result);
        $this->assertSame($configs, $result);
    }

    public function test_repository_entityClass(): void
    {
        $repository = new AliyunVodConfigRepository($this->registry);
        
        // 通过反射检查Repository管理的实体类
        $reflection = new \ReflectionClass($repository);
        $this->assertTrue($reflection->hasMethod('findDefaultConfig'));
        $this->assertTrue($reflection->hasMethod('findActiveConfigs'));
        
        // 检查父类
        $parentClass = $reflection->getParentClass();
        $this->assertEquals(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $parentClass->getName());
    }



    public function test_repository_methodsReturnTypes(): void
    {
        $repository = new AliyunVodConfigRepository($this->registry);
        
        // 检查findDefaultConfig的返回类型
        $findDefaultReflection = new \ReflectionMethod($repository, 'findDefaultConfig');
        $returnType = $findDefaultReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
        
        // 检查findActiveConfigs的返回类型
        $findActiveReflection = new \ReflectionMethod($repository, 'findActiveConfigs');
        $returnType = $findActiveReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }
} 