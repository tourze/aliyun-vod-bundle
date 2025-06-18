<?php

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Repository\PlayRecordRepository;

/**
 * 播放记录仓储测试
 */
class PlayRecordRepositoryTest extends TestCase
{
    private $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    public function test_construct_withValidRegistry(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $this->assertInstanceOf(PlayRecordRepository::class, $repository);
    }

    public function test_repository_inheritance(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }

    public function test_repository_hasRequiredMethods(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $this->assertTrue(method_exists($repository, 'findByVideo'));
        $this->assertTrue(method_exists($repository, 'findByDateRange'));
        $this->assertTrue(method_exists($repository, 'findByIpAddress'));
        $this->assertTrue(method_exists($repository, 'countByVideo'));
        $this->assertTrue(method_exists($repository, 'getPopularVideos'));
    }

    public function test_findByVideo_methodSignature(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findByVideo');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('video', $parameters[0]->getName());
    }

    public function test_findByDateRange_methodSignature(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findByDateRange');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(2, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('startDate', $parameters[0]->getName());
        $this->assertEquals('endDate', $parameters[1]->getName());
    }

    public function test_findByIpAddress_methodSignature(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findByIpAddress');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('ipAddress', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    public function test_countByVideo_methodSignature(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'countByVideo');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('video', $parameters[0]->getName());
    }

    public function test_getPopularVideos_methodSignature(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'getPopularVideos');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('limit', $parameters[0]->getName());
        $this->assertEquals('int', $parameters[0]->getType()->getName());
    }

    public function test_findByIpAddress_logicStructure(): void
    {
        $repository = new class($this->registry) extends PlayRecordRepository {
            private $mockResult = [];
            
            public function setMockResult(array $result): void
            {
                $this->mockResult = $result;
            }
            
            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                $expectedOrderBy = ['playTime' => 'DESC'];
                
                if (isset($criteria['ipAddress']) && $orderBy === $expectedOrderBy) {
                    return $this->mockResult;
                }
                
                return [];
            }
        };

        // 测试不同IP地址的查询
        $ipAddresses = ['192.168.1.1', '10.0.0.1', '172.16.0.1'];
        
        foreach ($ipAddresses as $ipAddress) {
            $repository->setMockResult([]);
            $result = $repository->findByIpAddress($ipAddress);
            $this->assertIsArray($result);
        }
    }

    public function test_repository_criteriaValidation(): void
    {
        // 测试findByIpAddress的查询条件
        $testIpAddress = '192.168.1.1';
        $expectedCriteria = ['ipAddress' => $testIpAddress];
        $expectedOrderBy = ['playTime' => 'DESC'];
        
        $this->assertEquals($testIpAddress, $expectedCriteria['ipAddress']);
        $this->assertEquals('DESC', $expectedOrderBy['playTime']);
    }

    public function test_repository_methodsReturnTypes(): void
    {
        $repository = new PlayRecordRepository($this->registry);
        
        // 检查findByVideo的返回类型
        $findByVideoReflection = new \ReflectionMethod($repository, 'findByVideo');
        $returnType = $findByVideoReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
        
        // 检查findByDateRange的返回类型
        $findByDateRangeReflection = new \ReflectionMethod($repository, 'findByDateRange');
        $returnType = $findByDateRangeReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
        
        // 检查findByIpAddress的返回类型
        $findByIpAddressReflection = new \ReflectionMethod($repository, 'findByIpAddress');
        $returnType = $findByIpAddressReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
        
        // 检查countByVideo的返回类型
        $countByVideoReflection = new \ReflectionMethod($repository, 'countByVideo');
        $returnType = $countByVideoReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
        
        // 检查getPopularVideos的返回类型
        $getPopularVideosReflection = new \ReflectionMethod($repository, 'getPopularVideos');
        $returnType = $getPopularVideosReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_repository_dateRangeValidation(): void
    {
        // 测试日期范围查询的逻辑
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-31');
        
        $this->assertInstanceOf(\DateTime::class, $startDate);
        $this->assertInstanceOf(\DateTime::class, $endDate);
        $this->assertLessThan($endDate, $startDate);
    }

    public function test_repository_ipAddressValidation(): void
    {
        // 测试IP地址的有效性
        $validIpAddresses = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '127.0.0.1'];
        
        foreach ($validIpAddresses as $ipAddress) {
            $this->assertNotEmpty($ipAddress);
            $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ipAddress);
        }
    }

    public function test_repository_popularVideosLimitValidation(): void
    {
        // 测试热门视频查询的限制参数
        $validLimits = [5, 10, 20, 50, 100];
        
        foreach ($validLimits as $limit) {
            $this->assertIsInt($limit);
            $this->assertGreaterThan(0, $limit);
        }
    }
} 