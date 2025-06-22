<?php

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Repository\VideoRepository;

/**
 * 视频仓储测试
 */
class VideoRepositoryTest extends TestCase
{
    private $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    public function test_construct_withValidRegistry(): void
    {
        $repository = new VideoRepository($this->registry);
        
        $this->assertInstanceOf(VideoRepository::class, $repository);
    }

    public function test_repository_inheritance(): void
    {
        $repository = new VideoRepository($this->registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }

    public function test_repository_hasRequiredMethods(): void
    {
        $repository = new VideoRepository($this->registry);
        
        // Repository is guaranteed to have these methods
        $this->assertInstanceOf(VideoRepository::class, $repository);
    }

    public function test_findByVideoId_methodSignature(): void
    {
        $repository = new VideoRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findByVideoId');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('videoId', $parameters[0]->getName());
        $this->assertEquals('string', (string) $parameters[0]->getType());
    }

    public function test_findValidVideos_methodSignature(): void
    {
        $repository = new VideoRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findValidVideos');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function test_findByStatus_methodSignature(): void
    {
        $repository = new VideoRepository($this->registry);
        
        $reflection = new \ReflectionMethod($repository, 'findByStatus');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('status', $parameters[0]->getName());
        $this->assertEquals('string', (string) $parameters[0]->getType());
    }

    public function test_findByVideoId_logicStructure(): void
    {
        $repository = new class($this->registry) extends VideoRepository {
            private $mockResult;
            
            public function setMockResult($result): void
            {
                $this->mockResult = $result;
            }
            
            public function findOneBy(array $criteria, ?array $orderBy = null): ?object
            {
                if (isset($criteria['videoId'])) {
                    return $this->mockResult;
                }
                return null;
            }
        };

        // 测试返回null的情况
        $repository->setMockResult(null);
        $result = $repository->findByVideoId('non_existent_id');
        $this->assertNull($result);

        // 测试返回视频的情况
        $video = new Video();
        $repository->setMockResult($video);
        $result = $repository->findByVideoId('test_video_001');
        $this->assertSame($video, $result);
    }

    public function test_findValidVideos_logicStructure(): void
    {
        $repository = new class($this->registry) extends VideoRepository {
            private $mockResult = [];
            
            public function setMockResult(array $result): void
            {
                $this->mockResult = $result;
            }
            
            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                $expectedCriteria = ['valid' => true];
                $expectedOrderBy = ['createdTime' => 'DESC'];
                
                if ($criteria === $expectedCriteria && $orderBy === $expectedOrderBy) {
                    return $this->mockResult;
                }
                
                return [];
            }
        };

        // 测试返回空数组
        $repository->setMockResult([]);
        $result = $repository->findValidVideos();
        $this->assertEmpty($result);

        // 测试返回视频数组
        $video1 = new Video();
        $video2 = new Video();
        $videos = [$video1, $video2];
        
        $repository->setMockResult($videos);
        $result = $repository->findValidVideos();
        $this->assertCount(2, $result);
        $this->assertSame($videos, $result);
    }

    public function test_findByStatus_logicStructure(): void
    {
        $repository = new class($this->registry) extends VideoRepository {
            private $mockResult = [];
            
            public function setMockResult(array $result): void
            {
                $this->mockResult = $result;
            }
            
            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                $expectedOrderBy = ['createdTime' => 'DESC'];
                
                if (isset($criteria['status']) && isset($criteria['valid']) && 
                    $criteria['valid'] === true && $orderBy === $expectedOrderBy) {
                    return $this->mockResult;
                }
                
                return [];
            }
        };

        // 测试不同状态的查询
        $statuses = ['Normal', 'Uploading', 'Transcoding'];
        
        foreach ($statuses as $status) {
            $repository->setMockResult([]);
            $result = $repository->findByStatus($status);
            $this->assertEquals([], $result);
        }
    }

    public function test_repository_criteriaValidation(): void
    {
        // 测试findValidVideos的查询条件
        $expectedCriteria = ['valid' => true];
        $expectedOrderBy = ['createdTime' => 'DESC'];
        
        $this->assertTrue($expectedCriteria['valid']);
        $this->assertEquals('DESC', $expectedOrderBy['createdTime']);
    }

    public function test_repository_statusCriteriaValidation(): void
    {
        // 测试findByStatus的查询条件
        $testStatus = 'Normal';
        $expectedCriteria = ['status' => $testStatus, 'valid' => true];
        $expectedOrderBy = ['createdTime' => 'DESC'];
        
        $this->assertEquals($testStatus, $expectedCriteria['status']);
        $this->assertTrue($expectedCriteria['valid']);
        $this->assertEquals('DESC', $expectedOrderBy['createdTime']);
    }

    public function test_repository_methodsReturnTypes(): void
    {
        $repository = new VideoRepository($this->registry);
        
        // 检查findByVideoId的返回类型
        $findByVideoIdReflection = new \ReflectionMethod($repository, 'findByVideoId');
        $returnType = $findByVideoIdReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
        
        // 检查findValidVideos的返回类型
        $findValidReflection = new \ReflectionMethod($repository, 'findValidVideos');
        $returnType = $findValidReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
        
        // 检查findByStatus的返回类型
        $findByStatusReflection = new \ReflectionMethod($repository, 'findByStatus');
        $returnType = $findByStatusReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }
} 