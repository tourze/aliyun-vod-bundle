<?php

namespace Tourze\AliyunVodBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 转码任务数据填充
 * 创建示例转码任务数据
 */
class TranscodeTaskFixtures extends Fixture implements DependentFixtureInterface
{
    // 转码任务引用常量
    public const TRANSCODE_TASK_1_REFERENCE = 'transcode-task-1';
    public const TRANSCODE_TASK_2_REFERENCE = 'transcode-task-2';
    public const TRANSCODE_TASK_3_REFERENCE = 'transcode-task-3';

    public function load(ObjectManager $manager): void
    {
        $demoVideo1 = $this->getReference(VideoFixtures::DEMO_VIDEO_1_REFERENCE, Video::class);
        $demoVideo2 = $this->getReference(VideoFixtures::DEMO_VIDEO_2_REFERENCE, Video::class);
        $tutorialVideo = $this->getReference(VideoFixtures::TUTORIAL_VIDEO_REFERENCE, Video::class);

        // 创建已完成的转码任务
        $transcodeTask1 = new TranscodeTask();
        $transcodeTask1->setVideo($demoVideo1)
            ->setTaskId('transcode_task_001_completed')
            ->setTemplateId('VOD_TEMPLATE_HD_001')
            ->setStatus('TranscodeSuccess')
            ->setProgress(100)
            ->setCompletedTime(new \DateTimeImmutable('-2 hours'));

        $manager->persist($transcodeTask1);
        $this->addReference(self::TRANSCODE_TASK_1_REFERENCE, $transcodeTask1);

        // 创建进行中的转码任务
        $transcodeTask2 = new TranscodeTask();
        $transcodeTask2->setVideo($tutorialVideo)
            ->setTaskId('transcode_task_002_processing')
            ->setTemplateId('VOD_TEMPLATE_4K_001')
            ->setStatus('Processing')
            ->setProgress(65);

        $manager->persist($transcodeTask2);
        $this->addReference(self::TRANSCODE_TASK_2_REFERENCE, $transcodeTask2);

        // 创建失败的转码任务
        $transcodeTask3 = new TranscodeTask();
        $transcodeTask3->setVideo($demoVideo2)
            ->setTaskId('transcode_task_003_failed')
            ->setTemplateId('VOD_TEMPLATE_SD_001')
            ->setStatus('TranscodeFail')
            ->setProgress(0)
            ->setErrorCode('InvalidVideo.Format')
            ->setErrorMessage('视频格式不支持，请检查输入文件格式');

        $manager->persist($transcodeTask3);
        $this->addReference(self::TRANSCODE_TASK_3_REFERENCE, $transcodeTask3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VideoFixtures::class,
        ];
    }
} 