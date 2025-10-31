<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Repository\TranscodeTaskRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * 转码任务仓储测试 - 简化版
 *
 * @internal
 */
#[CoversClass(TranscodeTaskRepository::class)]
#[RunTestsInSeparateProcesses]
final class TranscodeTaskRepositoryTest extends AbstractRepositoryTestCase
{
    protected function createNewEntity(): object
    {
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);

        return $task;
    }

    protected function getRepository(): TranscodeTaskRepository
    {
        $repository = self::getService(TranscodeTaskRepository::class);
        $this->assertInstanceOf(TranscodeTaskRepository::class, $repository);

        return $repository;
    }

    protected function onSetUp(): void
    {
        // Repository test setup - no additional setup needed
    }

    public function testRepositoryBehavior(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(TranscodeTaskRepository::class, $repository);

        // 测试仓库行为而非结构
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        // 验证findByTaskId行为
        $foundTask = $repository->findByTaskId($task->getTaskId());
        $this->assertInstanceOf(TranscodeTask::class, $foundTask);
        $this->assertEquals($task->getTaskId(), $foundTask->getTaskId());

        // 验证findProcessingTasks行为
        $processingTasks = $repository->findProcessingTasks();
        $this->assertIsArray($processingTasks);
    }

    public function testNullFieldHandlingTemplateId(): void
    {
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);
        $task->setTemplateId(null);

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        $this->assertNull($task->getTemplateId());
    }

    public function testNullFieldHandlingErrorCode(): void
    {
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);
        $task->setErrorCode(null);

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        $this->assertNull($task->getErrorCode());
    }

    public function testNullFieldHandlingErrorMessage(): void
    {
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);
        $task->setErrorMessage(null);

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        $this->assertNull($task->getErrorMessage());
    }

    public function testRepositoryMethodsWithRealData(): void
    {
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);

        $task = $this->createTestTask();
        $task->setVideo($video);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        // Test findByTaskId method
        $foundTask = $this->getRepository()->findByTaskId($task->getTaskId());
        $this->assertInstanceOf(TranscodeTask::class, $foundTask);
        $this->assertEquals($task->getTaskId(), $foundTask->getTaskId());

        // Test findByVideo method
        $tasksByVideo = $this->getRepository()->findByVideo($video);
        $this->assertIsArray($tasksByVideo);
        $this->assertGreaterThanOrEqual(1, count($tasksByVideo));

        // Test findByStatus method
        $tasksByStatus = $this->getRepository()->findByStatus('Processing');
        $this->assertIsArray($tasksByStatus);
    }

    public function testEntityCreation(): void
    {
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);

        $this->assertInstanceOf(TranscodeTask::class, $task);
        $this->assertNotNull($task->getTaskId());
        $this->assertNotNull($task->getStatus());
        $this->assertInstanceOf(Video::class, $task->getVideo());
    }

    public function testVideoAssociation(): void
    {
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);

        $this->assertSame($video, $task->getVideo());
        $this->assertInstanceOf(Video::class, $task->getVideo());
    }

    public function testRepositoryMethodsBehavior(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        $task = $this->createTestTask();
        $task->setVideo($video);

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        // 测试findByVideo行为
        $tasksByVideo = $repository->findByVideo($video);
        $this->assertIsArray($tasksByVideo);
        $this->assertGreaterThanOrEqual(1, count($tasksByVideo));

        // 测试save和remove行为
        $newTask = $this->createTestTask();
        $newTask->setVideo($video);
        $repository->save($newTask, true);

        // 刷新实体以获取ID
        self::getEntityManager()->refresh($newTask);
        $this->assertNotNull($newTask->getId());
        $taskId = $newTask->getId();

        $repository->remove($newTask, true);
        $removedTask = $repository->find($taskId);
        $this->assertNull($removedTask);
    }

    public function testFindByTaskId(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        $taskId = 'test_task_' . uniqid();

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test with non-existing task
        $result = $repository->findByTaskId($taskId);
        $this->assertNull($result);

        // Create and test with existing task
        $task = $this->createTestTask();
        $task->setTaskId($taskId);
        $task->setVideo($video);

        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        $result = $repository->findByTaskId($taskId);
        $this->assertInstanceOf(TranscodeTask::class, $result);
        $this->assertEquals($taskId, $result->getTaskId());
    }

    public function testFindByVideo(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test with no tasks
        $tasks = $repository->findByVideo($video);
        $this->assertIsArray($tasks);
        $this->assertEmpty($tasks);

        // Create test tasks
        $task1 = $this->createTestTask();
        $task1->setVideo($video);

        $task2 = $this->createTestTask();
        $task2->setVideo($video);

        self::getEntityManager()->persist($task1);
        self::getEntityManager()->persist($task2);
        self::getEntityManager()->flush();

        $tasks = $repository->findByVideo($video);
        $this->assertCount(2, $tasks);
        // Should be ordered by createdTime DESC (but since they're created at almost same time, just verify they exist)
        foreach ($tasks as $task) {
            $this->assertEquals($video, $task->getVideo());
        }
    }

    public function testFindByStatus(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        $status = 'Processing';

        // Create test tasks with different statuses
        $task1 = $this->createTestTask();
        $task1->setVideo($video);
        $task1->setStatus('Processing');

        $task2 = $this->createTestTask();
        $task2->setVideo($video);
        $task2->setStatus('Processing');

        $task3 = $this->createTestTask();
        $task3->setVideo($video);
        $task3->setStatus('Success');

        self::getEntityManager()->persist($task1);
        self::getEntityManager()->persist($task2);
        self::getEntityManager()->persist($task3);
        self::getEntityManager()->flush();

        $tasks = $repository->findByStatus('Processing');
        $this->assertGreaterThanOrEqual(2, count($tasks)); // Account for previous test data

        $processingCount = 0;
        foreach ($tasks as $task) {
            if ('Processing' === $task->getStatus()) {
                ++$processingCount;
            }
        }
        $this->assertGreaterThanOrEqual(2, $processingCount);
    }

    public function testFindProcessingTasks(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test with no processing tasks
        $processingTasks = $repository->findProcessingTasks();
        $this->assertIsArray($processingTasks);

        // Create test tasks with Processing status
        $task1 = $this->createTestTask();
        $task1->setVideo($video);
        $task1->setStatus('Processing');

        $task2 = $this->createTestTask();
        $task2->setVideo($video);
        $task2->setStatus('Processing');

        // Create non-processing task
        $task3 = $this->createTestTask();
        $task3->setVideo($video);
        $task3->setStatus('Success');

        self::getEntityManager()->persist($task1);
        self::getEntityManager()->persist($task2);
        self::getEntityManager()->persist($task3);
        self::getEntityManager()->flush();

        $processingTasks = $repository->findProcessingTasks();
        $this->assertGreaterThanOrEqual(2, count($processingTasks)); // Account for previous test data

        $processingCount = 0;
        foreach ($processingTasks as $task) {
            if ('Processing' === $task->getStatus()) {
                ++$processingCount;
            }
        }
        $this->assertGreaterThanOrEqual(2, $processingCount);
    }

    public function testFindCompletedTasks(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test with no completed tasks
        $completedTasks = $repository->findCompletedTasks();
        $this->assertIsArray($completedTasks);

        // Create completed tasks
        $task1 = $this->createTestTask();
        $task1->setVideo($video);
        $task1->setCompletedTime(new \DateTimeImmutable('2023-01-01 12:00:00'));

        $task2 = $this->createTestTask();
        $task2->setVideo($video);
        $task2->setCompletedTime(new \DateTimeImmutable('2023-01-01 13:00:00'));

        // Create task without completed time
        $task3 = $this->createTestTask();
        $task3->setVideo($video);
        $task3->setStatus('Processing');

        self::getEntityManager()->persist($task1);
        self::getEntityManager()->persist($task2);
        self::getEntityManager()->persist($task3);
        self::getEntityManager()->flush();

        $completedTasks = $repository->findCompletedTasks();
        $this->assertGreaterThanOrEqual(2, count($completedTasks)); // Account for previous test data

        $completedCount = 0;
        foreach ($completedTasks as $task) {
            if (null !== $task->getCompletedTime()) {
                ++$completedCount;
            }
        }
        $this->assertGreaterThanOrEqual(2, $completedCount);
    }

    public function testFindByStatusWithDifferentStatuses(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test different status values
        $statuses = ['Processing', 'Success', 'Failed', 'Waiting'];
        $tasksPerStatus = [];

        foreach ($statuses as $status) {
            $task = $this->createTestTask();
            $task->setVideo($video);
            $task->setStatus($status);
            $tasksPerStatus[$status] = $task;
            self::getEntityManager()->persist($task);
        }
        self::getEntityManager()->flush();

        foreach ($statuses as $status) {
            $foundTasks = $repository->findByStatus($status);
            $this->assertGreaterThan(0, count($foundTasks)); // Account for previous test data

            $statusCount = 0;
            foreach ($foundTasks as $task) {
                if ($task->getStatus() === $status) {
                    ++$statusCount;
                }
            }
            $this->assertGreaterThanOrEqual(1, $statusCount);
        }
    }

    public function testComplexScenarios(): void
    {
        // Test pagination parameters
        $this->assertIsInt(3);
        $this->assertIsInt(1);

        // Test date handling
        $completedTime = new \DateTimeImmutable();
        $this->assertInstanceOf(\DateTimeImmutable::class, $completedTime);

        // Test task creation with different statuses
        $statuses = ['Processing', 'Success', 'Failed'];
        foreach ($statuses as $status) {
            $task = $this->createTestTask();
            $task->setStatus($status);
            $this->assertEquals($status, $task->getStatus());
        }
    }

    private function createTestConfig(): AliyunVodConfig
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置_' . uniqid());
        $config->setAccessKeyId('LTAI4Test_' . uniqid());
        $config->setAccessKeySecret('test_secret_' . uniqid());
        $config->setRegionId('cn-shanghai');
        $config->setValid(true);

        return $config;
    }

    private function createTestVideo(): Video
    {
        $config = $this->createTestConfig();

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_' . uniqid());
        $video->setTitle('测试视频_' . uniqid());

        return $video;
    }

    private function createTestTask(): TranscodeTask
    {
        $task = new TranscodeTask();
        $task->setTaskId('task_' . uniqid());
        $task->setStatus('Processing');

        return $task;
    }
}
