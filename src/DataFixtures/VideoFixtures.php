<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 视频数据填充
 * 创建示例视频数据用于测试和演示
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class VideoFixtures extends Fixture implements DependentFixtureInterface
{
    // 视频引用常量
    public const DEMO_VIDEO_1_REFERENCE = 'demo-video-1';
    public const DEMO_VIDEO_2_REFERENCE = 'demo-video-2';
    public const DEMO_VIDEO_3_REFERENCE = 'demo-video-3';
    public const TUTORIAL_VIDEO_REFERENCE = 'tutorial-video';
    public const PRODUCT_VIDEO_REFERENCE = 'product-video';

    public function load(ObjectManager $manager): void
    {
        $defaultConfig = $this->getReference(AliyunVodConfigFixtures::DEFAULT_CONFIG_REFERENCE, AliyunVodConfig::class);
        $testConfig = $this->getReference(AliyunVodConfigFixtures::TEST_CONFIG_REFERENCE, AliyunVodConfig::class);

        // 创建演示视频1 - 产品介绍
        $demoVideo1 = new Video();
        $demoVideo1->setConfig($defaultConfig);
        $demoVideo1->setVideoId('demo_video_001_product_intro');
        $demoVideo1->setTitle('产品介绍视频');
        $demoVideo1->setDescription('这是一个产品介绍的演示视频，展示了我们产品的核心功能和特色。');
        $demoVideo1->setDuration(180); // 3分钟
        $demoVideo1->setSize(52428800); // 50MB
        $demoVideo1->setStatus('Normal');
        $demoVideo1->setCoverUrl('https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&h=450&fit=crop');
        $demoVideo1->setPlayUrl('https://images.unsplash.com/video/demo_video_001_product_intro.mp4');
        $demoVideo1->setTags('产品介绍,演示,功能展示');
        $demoVideo1->setValid(true);

        $manager->persist($demoVideo1);
        $this->addReference(self::DEMO_VIDEO_1_REFERENCE, $demoVideo1);

        // 创建演示视频2 - 技术教程
        $demoVideo2 = new Video();
        $demoVideo2->setConfig($defaultConfig);
        $demoVideo2->setVideoId('demo_video_002_tech_tutorial');
        $demoVideo2->setTitle('技术教程：如何使用阿里云VOD');
        $demoVideo2->setDescription('详细介绍如何集成和使用阿里云视频点播服务，包括上传、转码、播放等功能。');
        $demoVideo2->setDuration(720); // 12分钟
        $demoVideo2->setSize(157286400); // 150MB
        $demoVideo2->setStatus('Normal');
        $demoVideo2->setCoverUrl('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=450&fit=crop');
        $demoVideo2->setPlayUrl('https://images.unsplash.com/video/demo_video_002_tech_tutorial.mp4');
        $demoVideo2->setTags('教程,技术,阿里云,VOD');
        $demoVideo2->setValid(true);

        $manager->persist($demoVideo2);
        $this->addReference(self::DEMO_VIDEO_2_REFERENCE, $demoVideo2);

        // 创建演示视频3 - 用户案例
        $demoVideo3 = new Video();
        $demoVideo3->setConfig($testConfig);
        $demoVideo3->setVideoId('demo_video_003_user_case');
        $demoVideo3->setTitle('用户成功案例分享');
        $demoVideo3->setDescription('真实用户分享使用我们产品后的成功经验和收获。');
        $demoVideo3->setDuration(300); // 5分钟
        $demoVideo3->setSize(78643200); // 75MB
        $demoVideo3->setStatus('Normal');
        $demoVideo3->setCoverUrl('https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=450&fit=crop');
        $demoVideo3->setPlayUrl('https://images.unsplash.com/video/demo_video_003_user_case.mp4');
        $demoVideo3->setTags('案例,用户分享,成功故事');
        $demoVideo3->setValid(true);

        $manager->persist($demoVideo3);
        $this->addReference(self::DEMO_VIDEO_3_REFERENCE, $demoVideo3);

        // 创建教程视频
        $tutorialVideo = new Video();
        $tutorialVideo->setConfig($defaultConfig);
        $tutorialVideo->setVideoId('tutorial_video_001_getting_started');
        $tutorialVideo->setTitle('快速入门指南');
        $tutorialVideo->setDescription('从零开始学习如何使用我们的平台，包括注册、配置和基本操作。');
        $tutorialVideo->setDuration(600); // 10分钟
        $tutorialVideo->setSize(125829120); // 120MB
        $tutorialVideo->setStatus('Transcoding'); // 转码中
        $tutorialVideo->setCoverUrl('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&h=450&fit=crop');
        $tutorialVideo->setPlayUrl(null); // 转码中，暂无播放地址
        $tutorialVideo->setTags('入门,指南,教程,基础');
        $tutorialVideo->setValid(true);

        $manager->persist($tutorialVideo);
        $this->addReference(self::TUTORIAL_VIDEO_REFERENCE, $tutorialVideo);

        // 创建产品宣传视频
        $productVideo = new Video();
        $productVideo->setConfig($defaultConfig);
        $productVideo->setVideoId('product_video_001_promotion');
        $productVideo->setTitle('2024年度产品宣传片');
        $productVideo->setDescription('展示我们2024年的产品创新和技术突破，精彩不容错过！');
        $productVideo->setDuration(90); // 1.5分钟
        $productVideo->setSize(41943040); // 40MB
        $productVideo->setStatus('Normal');
        $productVideo->setCoverUrl('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&h=450&fit=crop');
        $productVideo->setPlayUrl('https://images.unsplash.com/video/product_video_001_promotion.mp4');
        $productVideo->setTags('宣传,产品,2024,创新');
        $productVideo->setValid(true);

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
