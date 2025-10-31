<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 阿里云VOD管理菜单服务
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('视频管理')) {
            $item->addChild('视频管理');
        }

        $videoMenu = $item->getChild('视频管理');

        if (null === $videoMenu) {
            return;
        }

        // 阿里云VOD配置管理菜单
        $vodConfigMenu = $videoMenu->addChild('VOD配置');
        $vodConfigMenu->setUri($this->linkGenerator->getCurdListPage(AliyunVodConfig::class));
        $vodConfigMenu->setAttribute('icon', 'fas fa-cog');

        // 视频管理菜单
        $videoListMenu = $videoMenu->addChild('视频列表');
        $videoListMenu->setUri($this->linkGenerator->getCurdListPage(Video::class));
        $videoListMenu->setAttribute('icon', 'fas fa-video');

        // 转码任务管理菜单
        $transcodeTaskMenu = $videoMenu->addChild('转码任务');
        $transcodeTaskMenu->setUri($this->linkGenerator->getCurdListPage(TranscodeTask::class));
        $transcodeTaskMenu->setAttribute('icon', 'fas fa-tasks');

        // 播放记录管理菜单
        $playRecordMenu = $videoMenu->addChild('播放记录');
        $playRecordMenu->setUri($this->linkGenerator->getCurdListPage(PlayRecord::class));
        $playRecordMenu->setAttribute('icon', 'fas fa-play-circle');
    }
}
