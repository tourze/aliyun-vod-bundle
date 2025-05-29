<?php

namespace Tourze\AliyunVodBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 视频数据填充
 * 创建示例视频数据用于测试和演示
 */
class VideoFixtures extends Fixture implements DependentFixtureInterface
{
    // 视频引用常量
    public const DEMO_VIDEO_1_REFERENCE = 'video-demo-1';
    public const DEMO_VIDEO_2_REFERENCE = 'video-demo-2';
    public const DEMO_VIDEO_3_REFERENCE = 'video-demo-3';
    public const TUTORIAL_VIDEO_REFERENCE = 'video-tutorial';
    public const PRODUCT_VIDEO_REFERENCE = 'video-product';

    public function load(ObjectManager $manager): void
    {
        $defaultConfig = $this->getReference(AliyunVodConfigFixtures::DEFAULT_CONFIG_REFERENCE, AliyunVodConfig::class);
        $testConfig = $this->getReference(AliyunVodConfigFixtures::TEST_CONFIG_REFERENCE, AliyunVodConfig::class);

        // 创建演示视频1 - 产品介绍
        $demoVideo1 = new Video();
        $demoVideo1->setConfig($defaultConfig)
            ->setVideoId('demo_video_001_product_intro')
            ->setTitle('产品介绍视频')
            ->setDescription('这是一个产品介绍的演示视频，展示了我们产品的核心功能和特色。')
            ->setDuration(180) // 3分钟
            ->setSize(52428800) // 50MB
            ->setStatus('Normal')
            ->setCoverUrl('https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&h=450&fit=crop')
            ->setPlayUrl('https://vod.example.com/play/demo_video_001_product_intro.mp4')
            ->setTags('产品介绍,演示,功能展示')
            ->setValid(true);

        $manager->persist($demoVideo1);
        $this->addReference(self::DEMO_VIDEO_1_REFERENCE, $demoVideo1);

        // 创建演示视频2 - 技术教程
        $demoVideo2 = new Video();
        $demoVideo2->setConfig($defaultConfig)
            ->setVideoId('demo_video_002_tech_tutorial')
            ->setTitle('技术教程：如何使用阿里云VOD')
            ->setDescription('详细介绍如何集成和使用阿里云视频点播服务，包括上传、转码、播放等功能。')
            ->setDuration(720) // 12分钟
            ->setSize(157286400) // 150MB
            ->setStatus('Normal')
            ->setCoverUrl('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=450&fit=crop')
            ->setPlayUrl('https://vod.example.com/play/demo_video_002_tech_tutorial.mp4')
            ->setTags('教程,技术,阿里云,VOD')
            ->setValid(true);

        $manager->persist($demoVideo2);
        $this->addReference(self::DEMO_VIDEO_2_REFERENCE, $demoVideo2);

        // 创建演示视频3 - 用户案例
        $demoVideo3 = new Video();
        $demoVideo3->setConfig($testConfig)
            ->setVideoId('demo_video_003_user_case')
            ->setTitle('用户成功案例分享')
            ->setDescription('真实用户分享使用我们产品后的成功经验和收获。')
            ->setDuration(300) // 5分钟
            ->setSize(78643200) // 75MB
            ->setStatus('Normal')
            ->setCoverUrl('https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=450&fit=crop')
            ->setPlayUrl('https://vod.example.com/play/demo_video_003_user_case.mp4')
            ->setTags('案例,用户分享,成功故事')
            ->setValid(true);

        $manager->persist($demoVideo3);
        $this->addReference(self::DEMO_VIDEO_3_REFERENCE, $demoVideo3);

        // 创建教程视频
        $tutorialVideo = new Video();
        $tutorialVideo->setConfig($defaultConfig)
            ->setVideoId('tutorial_video_001_getting_started')
            ->setTitle('快速入门指南')
            ->setDescription('从零开始学习如何使用我们的平台，包括注册、配置和基本操作。')
            ->setDuration(600) // 10分钟
            ->setSize(125829120) // 120MB
            ->setStatus('Transcoding') // 转码中
            ->setCoverUrl('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&h=450&fit=crop')
            ->setPlayUrl(null) // 转码中，暂无播放地址
            ->setTags('入门,指南,教程,基础')
            ->setValid(true);

        $manager->persist($tutorialVideo);
        $this->addReference(self::TUTORIAL_VIDEO_REFERENCE, $tutorialVideo);

        // 创建产品宣传视频
        $productVideo = new Video();
        $productVideo->setConfig($defaultConfig)
            ->setVideoId('product_video_001_promotion')
            ->setTitle('2024年度产品宣传片')
            ->setDescription('展示我们2024年的产品创新和技术突破，精彩不容错过！')
            ->setDuration(90) // 1.5分钟
            ->setSize(41943040) // 40MB
            ->setStatus('Normal')
            ->setCoverUrl('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&h=450&fit=crop')
            ->setPlayUrl('https://vod.example.com/play/product_video_001_promotion.mp4')
            ->setTags('宣传,产品,2024,创新')
            ->setValid(true);

        $manager->persist($productVideo);
        $this->addReference(self::PRODUCT_VIDEO_REFERENCE, $productVideo);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AliyunVodConfigFixtures::class,
        ];
    }
} 