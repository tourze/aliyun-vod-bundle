<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Service\StatisticsService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(StatisticsService::class)]
#[RunTestsInSeparateProcesses]
final class StatisticsServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Service 测试无需特殊设置
    }

    public function testServiceCanBeInstantiated(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);
    }

    public function testRecordPlayCreatesPlayRecord(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // 创建测试配置
        $config = new AliyunVodConfig();
        $config->setName('测试配置');
        $config->setAccessKeyId('LTAI4Test');
        $config->setAccessKeySecret('test_secret');
        $config->setRegionId('cn-shanghai');
        $config->setValid(true);
        $entityManager->persist($config);

        // 创建测试视频
        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test-video-' . uniqid());
        $video->setTitle('测试视频');
        $video->setDuration(120);
        $entityManager->persist($video);
        $entityManager->flush();

        // 测试记录播放
        $playRecord = $service->recordPlay(
            $video,
            '192.168.1.1',
            'Mozilla/5.0',
            'https://example.com',
            60,
            30,
            '720p',
            'desktop',
            '1.0.0'
        );

        $this->assertInstanceOf(PlayRecord::class, $playRecord);
        $this->assertSame($video, $playRecord->getVideo());
        $this->assertEquals('192.168.1.1', $playRecord->getIpAddress());
        $this->assertEquals('Mozilla/5.0', $playRecord->getUserAgent());
        $this->assertEquals('https://example.com', $playRecord->getReferer());
        $this->assertEquals(60, $playRecord->getPlayDuration());
        $this->assertEquals(30, $playRecord->getPlayPosition());
        $this->assertEquals('720p', $playRecord->getPlayQuality());
        $this->assertEquals('desktop', $playRecord->getDeviceType());
        $this->assertEquals('1.0.0', $playRecord->getPlayerVersion());
    }

    public function testRecordPlayWithMinimalParameters(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // 创建测试配置
        $config = new AliyunVodConfig();
        $config->setName('最小参数测试配置');
        $config->setAccessKeyId('LTAI4TestMin');
        $config->setAccessKeySecret('test_secret_min');
        $config->setRegionId('cn-shanghai');
        $config->setValid(true);
        $entityManager->persist($config);

        // 创建测试视频
        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test-video-minimal-' . uniqid());
        $video->setTitle('最小参数测试视频');
        $entityManager->persist($video);
        $entityManager->flush();

        // 测试只使用必需参数
        $playRecord = $service->recordPlay($video);

        $this->assertInstanceOf(PlayRecord::class, $playRecord);
        $this->assertSame($video, $playRecord->getVideo());
        $this->assertNull($playRecord->getIpAddress());
        $this->assertNull($playRecord->getUserAgent());
        $this->assertNull($playRecord->getReferer());
        $this->assertNull($playRecord->getPlayDuration());
        $this->assertNull($playRecord->getPlayPosition());
        $this->assertNull($playRecord->getPlayQuality());
        $this->assertNull($playRecord->getDeviceType());
        $this->assertNull($playRecord->getPlayerVersion());
    }

    public function testGetVideoPlayStatsReturnsCorrectStructure(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // 创建测试配置
        $config = new AliyunVodConfig();
        $config->setName('统计测试配置');
        $config->setAccessKeyId('LTAI4TestStats');
        $config->setAccessKeySecret('test_secret_stats');
        $config->setRegionId('cn-shanghai');
        $config->setValid(true);
        $entityManager->persist($config);

        // 创建测试视频
        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test-video-stats-' . uniqid());
        $video->setTitle('统计测试视频');
        $video->setDuration(120);
        $entityManager->persist($video);
        $entityManager->flush();

        // 获取统计信息
        $stats = $service->getVideoPlayStats($video);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('videoId', $stats);
        $this->assertArrayHasKey('title', $stats);
        $this->assertArrayHasKey('totalPlays', $stats);
        $this->assertArrayHasKey('averagePlayDuration', $stats);
        $this->assertArrayHasKey('deviceStats', $stats);
        $this->assertArrayHasKey('qualityStats', $stats);

        $this->assertEquals($video->getVideoId(), $stats['videoId']);
        $this->assertEquals($video->getTitle(), $stats['title']);
        $this->assertIsInt($stats['totalPlays']);
        $this->assertIsNumeric($stats['averagePlayDuration']);
        $this->assertIsArray($stats['deviceStats']);
        $this->assertIsArray($stats['qualityStats']);
    }

    public function testGetPopularVideosReturnsArray(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);

        // 获取热门视频
        $popularVideos = $service->getPopularVideos(5);

        $this->assertIsArray($popularVideos);
        // 即使没有数据，也应该返回空数组而不是null
        $this->assertIsArray($popularVideos);
    }

    public function testGetPlayStatsByDateRangeReturnsCorrectStructure(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);

        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-07');

        // 获取时间范围统计
        $stats = $service->getPlayStatsByDateRange($startDate, $endDate);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('startDate', $stats);
        $this->assertArrayHasKey('endDate', $stats);
        $this->assertArrayHasKey('totalPlays', $stats);
        $this->assertArrayHasKey('uniqueVideos', $stats);
        $this->assertArrayHasKey('dailyStats', $stats);
        $this->assertArrayHasKey('hourlyStats', $stats);
        $this->assertArrayHasKey('deviceStats', $stats);

        $this->assertEquals($startDate->format('Y-m-d'), $stats['startDate']);
        $this->assertEquals($endDate->format('Y-m-d'), $stats['endDate']);
        $this->assertIsInt($stats['totalPlays']);
        $this->assertIsInt($stats['uniqueVideos']);
        $this->assertIsArray($stats['dailyStats']);
        $this->assertIsArray($stats['hourlyStats']);
        $this->assertIsArray($stats['deviceStats']);
    }

    public function testGetRealTimeStatsReturnsCorrectStructure(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);

        // 获取实时统计
        $stats = $service->getRealTimeStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('today', $stats);
        $this->assertArrayHasKey('yesterday', $stats);
        $this->assertArrayHasKey('growth', $stats);

        $this->assertIsArray($stats['today']);
        $this->assertIsArray($stats['yesterday']);
        $this->assertIsArray($stats['growth']);

        $this->assertArrayHasKey('plays', $stats['growth']);
        $this->assertArrayHasKey('videos', $stats['growth']);
    }

    public function testGetVideoCompletionRateReturnsCorrectStructure(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // 创建测试配置
        $config = new AliyunVodConfig();
        $config->setName('完播率测试配置');
        $config->setAccessKeyId('LTAI4TestCompletion');
        $config->setAccessKeySecret('test_secret_completion');
        $config->setRegionId('cn-shanghai');
        $config->setValid(true);
        $entityManager->persist($config);

        // 创建测试视频
        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test-video-completion-' . uniqid());
        $video->setTitle('完播率测试视频');
        $video->setDuration(120);
        $entityManager->persist($video);
        $entityManager->flush();

        // 获取完播率统计
        $completionRate = $service->getVideoCompletionRate($video);

        $this->assertIsArray($completionRate);
        $this->assertArrayHasKey('videoId', $completionRate);
        $this->assertArrayHasKey('completionRate', $completionRate);
        $this->assertArrayHasKey('totalPlays', $completionRate);
        $this->assertArrayHasKey('completedPlays', $completionRate);

        $this->assertEquals($video->getVideoId(), $completionRate['videoId']);
        $this->assertIsNumeric($completionRate['completionRate']);
        $this->assertIsInt($completionRate['totalPlays']);
        $this->assertIsInt($completionRate['completedPlays']);
    }

    public function testCleanExpiredPlayRecordsReturnsInteger(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);

        // 清理过期记录
        $deletedCount = $service->cleanExpiredPlayRecords(90);

        $this->assertIsInt($deletedCount);
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testCleanExpiredPlayRecordsWithCustomDays(): void
    {
        $service = self::getContainer()->get(StatisticsService::class);
        $this->assertInstanceOf(StatisticsService::class, $service);

        // 使用自定义天数清理过期记录
        $deletedCount = $service->cleanExpiredPlayRecords(30);

        $this->assertIsInt($deletedCount);
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }
}
