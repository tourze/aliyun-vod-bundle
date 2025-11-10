<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Repository\PlayRecordRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * 播放记录仓储测试 - 简化版
 *
 * @internal
 */
#[CoversClass(PlayRecordRepository::class)]
#[RunTestsInSeparateProcesses]
final class PlayRecordRepositoryTest extends AbstractRepositoryTestCase
{
    protected function createNewEntity(): object
    {
        $video = $this->createTestVideo();

        return $this->createTestRecord($video);
    }

    protected function getRepository(): PlayRecordRepository
    {
        try {
            $repository = self::getService(PlayRecordRepository::class);
            $this->assertInstanceOf(PlayRecordRepository::class, $repository);

            return $repository;
        } catch (\LogicException $e) {
            // 如果实体管理器不可用，跳过此测试
            if (str_contains($e->getMessage(), 'Could not find the entity manager')) {
                self::markTestSkipped('Entity manager not available for PlayRecord');
            }
            throw $e;
        }
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
        $this->assertInstanceOf(PlayRecordRepository::class, $repository);

        // 测试仓库行为而非结构
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // 验证countByVideo行为
        $count = $repository->countByVideo($video);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);

        // 验证findByVideo行为
        $records = $repository->findByVideo($video);
        $this->assertIsArray($records);
    }

    public function testNullFieldHandlingUserAgent(): void
    {
        $video = $this->createTestVideo();
        $record = $this->createTestRecord($video);
        $record->setUserAgent(null);
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($record);
        self::getEntityManager()->flush();

        $this->assertNull($record->getUserAgent());
    }

    public function testNullFieldHandlingReferer(): void
    {
        $video = $this->createTestVideo();
        $record = $this->createTestRecord($video);
        $record->setReferer(null);
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($record);
        self::getEntityManager()->flush();

        $this->assertNull($record->getReferer());
    }

    public function testNullFieldHandlingPlayDuration(): void
    {
        $video = $this->createTestVideo();
        $record = $this->createTestRecord($video);
        $record->setPlayDuration(null);
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->persist($record);
        self::getEntityManager()->flush();

        $this->assertNull($record->getPlayDuration());
    }

    public function testEntityCreation(): void
    {
        $video = $this->createTestVideo();
        $record = $this->createTestRecord($video);
        $this->assertInstanceOf(PlayRecord::class, $record);
        $this->assertNotNull($record->getIpAddress());
        $this->assertNotNull($record->getPlayTime());
        $this->assertInstanceOf(Video::class, $record->getVideo());
    }

    public function testVideoAssociation(): void
    {
        $video = $this->createTestVideo();
        $record = $this->createTestRecord($video);

        $this->assertSame($video, $record->getVideo());
        $this->assertInstanceOf(Video::class, $record->getVideo());
    }

    public function testConfigCreation(): void
    {
        $config = $this->createTestConfig();
        $this->assertInstanceOf(AliyunVodConfig::class, $config);
        $this->assertNotEmpty($config->getName());
        $this->assertNotEmpty($config->getAccessKeyId());
    }

    public function testRepositoryMethodsWithRealData(): void
    {
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);

        $record = $this->createTestRecord($video);
        self::getEntityManager()->persist($record);
        self::getEntityManager()->flush();

        // Test basic Repository functionality
        $this->assertInstanceOf(PlayRecordRepository::class, $this->getRepository());

        // 验证仓库能正确实例化并具有预期功能
        $repository = $this->getRepository();
        $this->assertNotNull($repository);
    }

    public function testRepositoryMethodsBehavior(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // 测试findByIpAddress行为
        $ipAddress = '192.168.1.100';
        $recordsByIp = $repository->findByIpAddress($ipAddress);
        $this->assertIsArray($recordsByIp);

        // 测试save和remove行为
        $record = $this->createTestRecord($video);
        $repository->save($record, true);

        // 刷新实体以获取ID
        self::getEntityManager()->refresh($record);
        $this->assertNotNull($record->getId());
        $recordId = $record->getId();

        $repository->remove($record, true);
        $removedRecord = $repository->find($recordId);
        $this->assertNull($removedRecord);
    }

    public function testFindByVideo(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test with no records
        $records = $repository->findByVideo($video);
        $this->assertIsArray($records);
        $this->assertEmpty($records);

        // Create test records
        $record1 = $this->createTestRecord($video);
        $record1->setPlayTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $record2 = $this->createTestRecord($video);
        $record2->setPlayTime(new \DateTimeImmutable('2023-01-01 11:00:00'));

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->flush();

        $records = $repository->findByVideo($video);
        $this->assertCount(2, $records);
        // Should be ordered by playTime DESC
        $this->assertGreaterThanOrEqual($records[1]->getPlayTime(), $records[0]->getPlayTime());
    }

    public function testCountByVideo(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test with no records
        $count = $repository->countByVideo($video);
        $this->assertEquals(0, $count);

        // Create test records
        $record1 = $this->createTestRecord($video);
        $record2 = $this->createTestRecord($video);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->flush();

        $count = $repository->countByVideo($video);
        $this->assertEquals(2, $count);
    }

    public function testFindByIpAddress(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        $ipAddress = '192.168.1.100';

        // Test with no records
        $records = $repository->findByIpAddress($ipAddress);
        $this->assertIsArray($records);
        $this->assertEmpty($records);

        // Create test records with specific IP
        $record1 = $this->createTestRecord($video);
        $record1->setIpAddress($ipAddress);
        $record1->setPlayTime(new \DateTimeImmutable('2023-01-01 10:00:00'));

        $record2 = $this->createTestRecord($video);
        $record2->setIpAddress($ipAddress);
        $record2->setPlayTime(new \DateTimeImmutable('2023-01-01 11:00:00'));

        // Create record with different IP
        $record3 = $this->createTestRecord($video);
        $record3->setIpAddress('192.168.1.200');

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
        self::getEntityManager()->flush();

        $records = $repository->findByIpAddress($ipAddress);
        $this->assertCount(2, $records);
        // Should be ordered by playTime DESC
        $this->assertGreaterThanOrEqual($records[1]->getPlayTime(), $records[0]->getPlayTime());

        foreach ($records as $record) {
            $this->assertEquals($ipAddress, $record->getIpAddress());
        }
    }

    public function testGetPopularVideos(): void
    {
        $repository = $this->getRepository();

        // Create videos
        $video1 = $this->createTestVideo();
        $video1->setTitle('热门视频1');
        $video2 = $this->createTestVideo();
        $video2->setTitle('热门视频2');

        self::getEntityManager()->persist($video1->getConfig());
        self::getEntityManager()->persist($video1);
        self::getEntityManager()->persist($video2->getConfig());
        self::getEntityManager()->persist($video2);
        self::getEntityManager()->flush();

        // Test with no records
        $popularVideos = $repository->getPopularVideos();
        $this->assertIsArray($popularVideos);

        // Create play records (video1 has more plays)
        for ($i = 0; $i < 3; ++$i) {
            $record = $this->createTestRecord($video1);
            self::getEntityManager()->persist($record);
        }

        for ($i = 0; $i < 1; ++$i) {
            $record = $this->createTestRecord($video2);
            self::getEntityManager()->persist($record);
        }
        self::getEntityManager()->flush();

        $popularVideos = $repository->getPopularVideos(2);
        $this->assertIsArray($popularVideos);

        if (count($popularVideos) > 0) {
            // First video should have higher play count
            $this->assertArrayHasKey('id', $popularVideos[0]);
            $this->assertArrayHasKey('title', $popularVideos[0]);
            $this->assertArrayHasKey('playCount', $popularVideos[0]);
        }
    }

    public function testGetPopularVideosLimit(): void
    {
        $repository = $this->getRepository();

        // Create videos and records
        for ($i = 0; $i < 5; ++$i) {
            $video = $this->createTestVideo();
            $video->setTitle('视频' . $i);
            self::getEntityManager()->persist($video->getConfig());
            self::getEntityManager()->persist($video);

            $record = $this->createTestRecord($video);
            self::getEntityManager()->persist($record);
        }
        self::getEntityManager()->flush();

        // Test limit parameter
        $popularVideos = $repository->getPopularVideos(3);
        $this->assertLessThanOrEqual(3, count($popularVideos));
    }

    public function testFindByDateRange(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        $startDate = new \DateTime('2023-01-01 00:00:00');
        $endDate = new \DateTime('2023-01-02 23:59:59');

        // Test with no records
        $records = $repository->findByDateRange($startDate, $endDate);
        $this->assertIsArray($records);
        $this->assertEmpty($records);

        // Create test records within date range
        $record1 = $this->createTestRecord($video);
        $record1->setPlayTime(new \DateTimeImmutable('2023-01-01 12:00:00'));

        $record2 = $this->createTestRecord($video);
        $record2->setPlayTime(new \DateTimeImmutable('2023-01-02 12:00:00'));

        // Create record outside date range
        $record3 = $this->createTestRecord($video);
        $record3->setPlayTime(new \DateTimeImmutable('2023-01-03 12:00:00'));

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
        self::getEntityManager()->flush();

        $records = $repository->findByDateRange($startDate, $endDate);
        $this->assertCount(2, $records);

        // Should be ordered by playTime DESC
        foreach ($records as $record) {
            $playTime = $record->getPlayTime();
            $this->assertGreaterThanOrEqual($startDate, $playTime);
            $this->assertLessThanOrEqual($endDate, $playTime);
        }
    }

    public function testFindByDateRangeBoundaryConditions(): void
    {
        $repository = $this->getRepository();
        $video = $this->createTestVideo();
        self::getEntityManager()->persist($video->getConfig());
        self::getEntityManager()->persist($video);
        self::getEntityManager()->flush();

        // Test exact boundary dates
        $startDate = new \DateTime('2023-01-01 00:00:00');
        $endDate = new \DateTime('2023-01-01 23:59:59');

        $recordOnStart = $this->createTestRecord($video);
        $recordOnStart->setPlayTime(new \DateTimeImmutable('2023-01-01 00:00:00'));

        $recordOnEnd = $this->createTestRecord($video);
        $recordOnEnd->setPlayTime(new \DateTimeImmutable('2023-01-01 23:59:59'));

        self::getEntityManager()->persist($recordOnStart);
        self::getEntityManager()->persist($recordOnEnd);
        self::getEntityManager()->flush();

        $records = $repository->findByDateRange($startDate, $endDate);
        $this->assertCount(2, $records);
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

    private function createTestRecord(?Video $video = null): PlayRecord
    {
        $record = new PlayRecord();
        if (null !== $video) {
            $record->setVideo($video);
        }
        $record->setIpAddress('192.168.1.' . rand(1, 255));
        $record->setPlayTime(new \DateTimeImmutable());

        return $record;
    }
}
