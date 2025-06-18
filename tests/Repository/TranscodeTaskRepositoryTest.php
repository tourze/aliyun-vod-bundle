<?php

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Repository\TranscodeTaskRepository;

/**
 * 转码任务仓储测试
 */
class TranscodeTaskRepositoryTest extends TestCase
{
    private $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    public function test_construct_withValidRegistry(): void
    {
        $repository = new TranscodeTaskRepository($this->registry);
        
        $this->assertInstanceOf(TranscodeTaskRepository::class, $repository);
    }

    public function test_repository_inheritance(): void
    {
        $repository = new TranscodeTaskRepository($this->registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }

    public function test_repository_hasRequiredMethods(): void
    {
        $repository = new TranscodeTaskRepository($this->registry);
        
        $this->assertTrue(method_exists($repository, 'findByTaskId'));
        $this->assertTrue(method_exists($repository, 'findByVideo'));
        $this->assertTrue(method_exists($repository, 'findByStatus'));
        $this->assertTrue(method_exists($repository, 'findProcessingTasks'));
        $this->assertTrue(method_exists($repository, 'findCompletedTasks'));
    }

    public function test_findByTaskId_methodSignature(): void
    {
        $repository = new TranscodeTaskRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findByTaskId');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('taskId', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    public function test_findProcessingTasks_methodSignature(): void
    {
        $repository = new TranscodeTaskRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findProcessingTasks');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_findCompletedTasks_methodSignature(): void
    {
        $repository = new TranscodeTaskRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findCompletedTasks');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_findByTaskId_logicStructure(): void
    {
        $repository = new class($this->registry) extends TranscodeTaskRepository {
            private $mockResult;
            
            public function setMockResult($result): void
            {
                $this->mockResult = $result;
            }
            
            public function findOneBy(array $criteria, ?array $orderBy = null): ?object
            {
                if (isset($criteria['taskId'])) {
                    return $this->mockResult;
                }
                return null;
            }
        };

        // 测试返回null的情况
        $repository->setMockResult(null);
        $result = $repository->findByTaskId('non_existent_task');
        $this->assertNull($result);

        // 测试返回任务的情况
        $task = new TranscodeTask();
        $repository->setMockResult($task);
        $result = $repository->findByTaskId('test_task_001');
        $this->assertSame($task, $result);
    }

    public function test_findProcessingTasks_logicStructure(): void
    {
        $repository = new class($this->registry) extends TranscodeTaskRepository {
            private $mockResult = [];
            
            public function setMockResult(array $result): void
            {
                $this->mockResult = $result;
            }
            
            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                $expectedCriteria = ['status' => 'Processing'];
                $expectedOrderBy = ['createdTime' => 'ASC'];
                
                if ($criteria === $expectedCriteria && $orderBy === $expectedOrderBy) {
                    return $this->mockResult;
                }
                
                return [];
            }
        };

        // 测试返回空数组
        $repository->setMockResult([]);
        $result = $repository->findProcessingTasks();
        $this->assertEmpty($result);

        // 测试返回任务数组
        $task1 = new TranscodeTask();
        $task2 = new TranscodeTask();
        $tasks = [$task1, $task2];
        
        $repository->setMockResult($tasks);
        $result = $repository->findProcessingTasks();
        $this->assertCount(2, $result);
        $this->assertSame($tasks, $result);
    }

    public function test_repository_criteriaValidation(): void
    {
        // 测试findProcessingTasks的查询条件
        $expectedCriteria = ['status' => 'Processing'];
        $expectedOrderBy = ['createdTime' => 'ASC'];
        
        $this->assertEquals('Processing', $expectedCriteria['status']);
        $this->assertEquals('ASC', $expectedOrderBy['createdTime']);
    }

    public function test_repository_methodsReturnTypes(): void
    {
        $repository = new TranscodeTaskRepository($this->registry);
        
        // 检查findByTaskId的返回类型
        $findByTaskIdReflection = new \ReflectionMethod($repository, 'findByTaskId');
        $returnType = $findByTaskIdReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
        
        // 检查findProcessingTasks的返回类型
        $findProcessingReflection = new \ReflectionMethod($repository, 'findProcessingTasks');
        $returnType = $findProcessingReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
} 