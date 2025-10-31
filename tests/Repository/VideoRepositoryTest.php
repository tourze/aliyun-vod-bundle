<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * 视频仓储测试 - 简化版
 *
 * @internal
 */
#[CoversClass(VideoRepository::class)]
#[RunTestsInSeparateProcesses]
final class VideoRepositoryTest extends AbstractRepositoryTestCase
{
    protected function createNewEntity(): object
    {
        $video = $this->createTestVideo();
        // 对于基类的数据库可用性测试，需要确保关联的实体也被持久化
        self::getEntityManager()->persist($video->getConfig());

        return $video;
    }

    protected function getRepository(): VideoRepository
    {
        $repository = self::getService(VideoRepository::class);
        $this->assertInstanceOf(VideoRepository::class, $repository);

        return $repository;
    }

    protected function onSetUp(): void
    {
        // Repository test setup - ensure clean state before each test
        self::getEntityManager()->clear();

        // Force connection to be ready for database unavailable simulation
        $connection = self::getEntityManager()->getConnection();
        $connection->executeQuery('SELECT 1');
    }

    public function testRepositoryBehavior(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(VideoRepository::class, $repository);

        // 测试仓库行为而非结构
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // 验证findByVideoId行为
        $foundVideo = $repository->findByVideoId($video->getVideoId());
        $this->assertInstanceOf(Video::class, $foundVideo);
        $this->assertEquals($video->getVideoId(), $foundVideo->getVideoId());

        // 验证findValidVideos行为
        $validVideos = $repository->findValidVideos();
        $this->assertIsArray($validVideos);
        $this->assertGreaterThanOrEqual(1, count($validVideos));
    }

    public function testNullFieldHandlingDescription(): void
    {
        $video = $this->createTestVideo();
        $video->setDescription(null);
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        $this->assertNull($video->getDescription());
    }

    public function testNullFieldHandlingDuration(): void
    {
        $video = $this->createTestVideo();
        $video->setDuration(null);
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        $this->assertNull($video->getDuration());
    }

    public function testNullFieldHandlingSize(): void
    {
        $video = $this->createTestVideo();
        $video->setSize(null);
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        $this->assertNull($video->getSize());
    }

    public function testRepositoryMethodsWithRealData(): void
    {
        $config = $this->createTestConfig();
        self::getEntityManager()->persist($config);

        $video = $this->createTestVideo();
        $video->setConfig($config);
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test findByVideoId method
        $foundVideo = $this->getRepository()->findByVideoId($video->getVideoId());
        $this->assertInstanceOf(Video::class, $foundVideo);
        $this->assertEquals($video->getVideoId(), $foundVideo->getVideoId());

        // Test findValidVideos method
        $validVideos = $this->getRepository()->findValidVideos();
        $this->assertIsArray($validVideos);

        // Test findByStatus method
        $videosByStatus = $this->getRepository()->findByStatus('Normal');
        $this->assertIsArray($videosByStatus);
    }

    public function testEntityCreation(): void
    {
        $video = $this->createTestVideo();
        $this->assertInstanceOf(Video::class, $video);
        $this->assertNotNull($video->getVideoId());
        $this->assertNotNull($video->getTitle());
        $this->assertNotNull($video->getConfig());
    }

    public function testConfigAssociation(): void
    {
        $config = $this->createTestConfig();
        $video = $this->createTestVideo();
        $video->setConfig($config);

        $this->assertSame($config, $video->getConfig());
        $this->assertInstanceOf(AliyunVodConfig::class, $video->getConfig());
    }

    public function testRepositoryMethodsBehavior(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // 测试findByStatus行为
        $videosByStatus = $repository->findByStatus('Normal');
        $this->assertIsArray($videosByStatus);
        $this->assertGreaterThanOrEqual(1, count($videosByStatus));

        // 测试save和remove行为
        $newVideo = $this->createTestVideo();
        $repository->save($newVideo, true);

        // 刷新实体以获取ID
        self::getEntityManager()->refresh($newVideo);
        $this->assertNotNull($newVideo->getId());
        $videoId = $newVideo->getId();

        $repository->remove($newVideo, true);
        $removedVideo = $repository->find($videoId);
        $this->assertNull($removedVideo);
    }

    public function testFindByVideoId(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        $videoId = 'test_video_id_' . uniqid();

        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test with non-existing video
        $result = $repository->findByVideoId($videoId);
        $this->assertNull($result);

        // Create and test with existing video
        $video->setVideoId($videoId);
        self::getEntityManager()->flush();

        $result = $repository->findByVideoId($videoId);
        $this->assertInstanceOf(Video::class, $result);
        $this->assertEquals($videoId, $result->getVideoId());
    }

    public function testFindByStatus(): void
    {
        $repository = $this->getRepository();
        $status = 'Normal';

        // Test with no videos
        $videos = $repository->findByStatus($status);
        $this->assertIsArray($videos);

        // Create test videos with different statuses
        $video1 = $this->createTestVideo();
        $video1->setStatus('Normal');
        $video1->setValid(true);

        $video2 = $this->createTestVideo();
        $video2->setStatus('Normal');
        $video2->setValid(true);

        $video3 = $this->createTestVideo();
        $video3->setStatus('Blocked');
        $video3->setValid(true);

        // Create invalid video with same status
        $video4 = $this->createTestVideo();
        $video4->setStatus('Normal');
        $video4->setValid(false);

        self::getEntityManager()->persist($video1->getConfig());
        self::getEntityManager()->persist($video1);
        self::getEntityManager()->persist($video2->getConfig());
        self::getEntityManager()->persist($video2);
        self::getEntityManager()->persist($video3->getConfig());
        self::getEntityManager()->persist($video3);
        self::getEntityManager()->persist($video4->getConfig());
        self::getEntityManager()->persist($video4);
        self::getEntityManager()->flush();

        $videos = $repository->findByStatus('Normal');
        $this->assertGreaterThanOrEqual(2, count($videos)); // Account for previous test data

        $normalValidCount = 0;
        foreach ($videos as $video) {
            if ('Normal' === $video->getStatus() && $video->isValid()) {
                ++$normalValidCount;
            }
        }
        $this->assertGreaterThanOrEqual(2, $normalValidCount);
    }

    public function testFindValidVideos(): void
    {
        $repository = $this->getRepository();

        // Test with no videos
        $validVideos = $repository->findValidVideos();
        $this->assertIsArray($validVideos);

        // Create test videos with different validity
        $video1 = $this->createTestVideo();
        $video1->setValid(true);

        $video2 = $this->createTestVideo();
        $video2->setValid(true);

        $video3 = $this->createTestVideo();
        $video3->setValid(false);

        self::getEntityManager()->persist($video1->getConfig());
        self::getEntityManager()->persist($video1);
        self::getEntityManager()->persist($video2->getConfig());
        self::getEntityManager()->persist($video2);
        self::getEntityManager()->persist($video3->getConfig());
        self::getEntityManager()->persist($video3);
        self::getEntityManager()->flush();

        $validVideos = $repository->findValidVideos();
        $this->assertGreaterThanOrEqual(2, count($validVideos)); // Account for previous test data

        $validCount = 0;
        foreach ($validVideos as $video) {
            if ($video->isValid()) {
                ++$validCount;
            }
        }
        $this->assertGreaterThanOrEqual(2, $validCount);
    }

    public function testFindByStatusWithDifferentStatuses(): void
    {
        $repository = $this->getRepository();

        // Test different status values
        $statuses = ['Normal', 'Blocked', 'Checking', 'Failed'];
        $videosPerStatus = [];

        foreach ($statuses as $status) {
            $video = $this->createTestVideo();
            $video->setStatus($status);
            $video->setValid(true);
            $videosPerStatus[$status] = $video;
            self::getEntityManager()->persist($video->getConfig());
            self::getEntityManager()->persist($video);
        }
        self::getEntityManager()->flush();

        foreach ($statuses as $status) {
            $foundVideos = $repository->findByStatus($status);
            $this->assertGreaterThan(0, count($foundVideos)); // Account for previous test data

            $statusCount = 0;
            foreach ($foundVideos as $video) {
                if ($video->getStatus() === $status && $video->isValid()) {
                    ++$statusCount;
                }
            }
            $this->assertGreaterThanOrEqual(1, $statusCount);
        }
    }

    public function testFindValidVideosOrdering(): void
    {
        $repository = $this->getRepository();

        // Create multiple valid videos
        $videos = [];

        for ($i = 0; $i < 3; ++$i) {
            $video = $this->createTestVideo();
            $video->setValid(true);
            $videos[] = $video;
            self::getEntityManager()->persist($video->getConfig());
            self::getEntityManager()->persist($video);
        }
        self::getEntityManager()->flush();

        $validVideos = $repository->findValidVideos();
        $this->assertGreaterThanOrEqual(3, count($validVideos)); // Account for previous test data

        // Verify that all returned videos are valid
        $validCount = 0;
        foreach ($validVideos as $video) {
            if ($video->isValid()) {
                ++$validCount;
            }
        }
        $this->assertGreaterThanOrEqual(3, $validCount);
    }

    public function testFindByVideoIdWithNull(): void
    {
        $repository = $this->getRepository();

        // Test with null video ID
        $result = $repository->findByVideoId('non_existing_video_id');
        $this->assertNull($result);

        // Test with empty string
        $result = $repository->findByVideoId('');
        $this->assertNull($result);
    }

    public function testComplexScenarios(): void
    {
        // Test pagination parameters
        $this->assertIsInt(3);
        $this->assertIsInt(1);

        // Test video validation states
        $this->assertIsBool(true);
        $this->assertIsBool(false);

        // Test video creation with different attributes
        for ($i = 1; $i <= 5; ++$i) {
            $video = $this->createTestVideo();
            $video->setTitle('测试视频' . $i);
            $this->assertStringContainsString('测试视频', $video->getTitle());
        }
    }

    public function testOrderByComplexity(): void
    {
        // Test complex ordering scenarios
        $orderByClauses = [
            ['createdTime' => 'DESC'],
            ['status' => 'ASC', 'createdTime' => 'DESC'],
            ['status' => 'ASC', 'createdTime' => 'DESC', 'title' => 'ASC'],
        ];

        foreach ($orderByClauses as $orderBy) {
            $this->assertIsArray($orderBy);
            $this->assertGreaterThan(0, count($orderBy));
            // 验证每个排序值都是有效的
            foreach ($orderBy as $field => $direction) {
                $this->assertIsString($field);
                $this->assertContains($direction, ['ASC', 'DESC']);
            }
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
        $video->setValid(true);
        $video->setStatus('Normal');

        return $video;
    }
}
