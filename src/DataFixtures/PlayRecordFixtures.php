<?php

namespace Tourze\AliyunVodBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 播放记录数据填充
 * 创建示例播放记录数据用于统计分析
 */
class PlayRecordFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $demoVideo1 = $this->getReference(VideoFixtures::DEMO_VIDEO_1_REFERENCE, Video::class);
        $demoVideo2 = $this->getReference(VideoFixtures::DEMO_VIDEO_2_REFERENCE, Video::class);
        $demoVideo3 = $this->getReference(VideoFixtures::DEMO_VIDEO_3_REFERENCE, Video::class);
        $productVideo = $this->getReference(VideoFixtures::PRODUCT_VIDEO_REFERENCE, Video::class);

        // 模拟不同设备类型和播放质量的播放记录
        $deviceTypes = ['Desktop', 'Mobile', 'Tablet', 'Smart TV'];
        $playQualities = ['HD', 'SD', '4K', 'Auto'];
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
            'Mozilla/5.0 (iPad; CPU OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
        ];

        // 为产品介绍视频创建大量播放记录（热门视频）
        for ($i = 0; $i < 50; $i++) {
            $playRecord = new PlayRecord();
            $playRecord->setVideo($demoVideo1)
                ->setIpAddress($this->generateRandomIp())
                ->setUserAgent($userAgents[array_rand($userAgents)])
                ->setReferer('https://example.com/videos')
                ->setPlayDuration(rand(30, 180)) // 30秒到3分钟
                ->setPlayPosition(rand(60, 180)) // 播放进度
                ->setPlayQuality($playQualities[array_rand($playQualities)])
                ->setDeviceType($deviceTypes[array_rand($deviceTypes)])
                ->setPlayerVersion('2.1.0')
                ->setPlayTime(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));

            $manager->persist($playRecord);
        }

        // 为技术教程视频创建中等数量播放记录
        for ($i = 0; $i < 30; $i++) {
            $playRecord = new PlayRecord();
            $playRecord->setVideo($demoVideo2)
                ->setIpAddress($this->generateRandomIp())
                ->setUserAgent($userAgents[array_rand($userAgents)])
                ->setReferer('https://example.com/tutorials')
                ->setPlayDuration(rand(120, 720)) // 2分钟到12分钟
                ->setPlayPosition(rand(300, 720)) // 播放进度
                ->setPlayQuality($playQualities[array_rand($playQualities)])
                ->setDeviceType($deviceTypes[array_rand($deviceTypes)])
                ->setPlayerVersion('2.1.0')
                ->setPlayTime(new \DateTimeImmutable('-' . rand(1, 15) . ' days'));

            $manager->persist($playRecord);
        }

        // 为用户案例视频创建少量播放记录
        for ($i = 0; $i < 15; $i++) {
            $playRecord = new PlayRecord();
            $playRecord->setVideo($demoVideo3)
                ->setIpAddress($this->generateRandomIp())
                ->setUserAgent($userAgents[array_rand($userAgents)])
                ->setReferer('https://example.com/cases')
                ->setPlayDuration(rand(60, 300)) // 1分钟到5分钟
                ->setPlayPosition(rand(120, 300)) // 播放进度
                ->setPlayQuality($playQualities[array_rand($playQualities)])
                ->setDeviceType($deviceTypes[array_rand($deviceTypes)])
                ->setPlayerVersion('2.0.5')
                ->setPlayTime(new \DateTimeImmutable('-' . rand(1, 7) . ' days'));

            $manager->persist($playRecord);
        }

        // 为产品宣传视频创建最新的播放记录
        for ($i = 0; $i < 25; $i++) {
            $playRecord = new PlayRecord();
            $playRecord->setVideo($productVideo)
                ->setIpAddress($this->generateRandomIp())
                ->setUserAgent($userAgents[array_rand($userAgents)])
                ->setReferer('https://example.com/promotion')
                ->setPlayDuration(rand(45, 90)) // 45秒到1.5分钟
                ->setPlayPosition(rand(60, 90)) // 播放进度
                ->setPlayQuality($playQualities[array_rand($playQualities)])
                ->setDeviceType($deviceTypes[array_rand($deviceTypes)])
                ->setPlayerVersion('2.1.0')
                ->setPlayTime(new \DateTimeImmutable('-' . rand(1, 3) . ' days'));

            $manager->persist($playRecord);
        }

        // 创建一些今天的播放记录用于实时统计
        for ($i = 0; $i < 10; $i++) {
            $videos = [$demoVideo1, $demoVideo2, $productVideo];
            $selectedVideo = $videos[array_rand($videos)];

            $playRecord = new PlayRecord();
            $playRecord->setVideo($selectedVideo)
                ->setIpAddress($this->generateRandomIp())
                ->setUserAgent($userAgents[array_rand($userAgents)])
                ->setReferer('https://example.com/today')
                ->setPlayDuration(rand(30, 180))
                ->setPlayPosition(rand(60, 180))
                ->setPlayQuality($playQualities[array_rand($playQualities)])
                ->setDeviceType($deviceTypes[array_rand($deviceTypes)])
                ->setPlayerVersion('2.1.0')
                ->setPlayTime(new \DateTimeImmutable('-' . rand(1, 12) . ' hours'));

            $manager->persist($playRecord);
        }

        $manager->flush();
    }

    /**
     * 生成随机IP地址
     */
    private function generateRandomIp(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    public function getDependencies(): array
    {
        return [
            VideoFixtures::class,
        ];
    }
} 