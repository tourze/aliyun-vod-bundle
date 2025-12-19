<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $linkGenerator = new class implements LinkGeneratorInterface {
            public function getCurdListPage(string $entityClass): string
            {
                return match ($entityClass) {
                    AliyunVodConfig::class => '/admin/aliyun-vod-config',
                    Video::class => '/admin/video',
                    TranscodeTask::class => '/admin/transcode-task',
                    PlayRecord::class => '/admin/play-record',
                    default => '/admin/unknown',
                };
            }

            public function extractEntityFqcn(string $url): ?string
            {
                return null;
            }

            public function setDashboard(string $dashboardControllerFqcn): void
            {
            }
        };
        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    protected function getMenuProvider(): object
    {
        return $this->adminMenu;
    }

    public function testInvokeCreatesVideoManagementMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        ($this->adminMenu)($rootMenu);

        $videoMenu = $rootMenu->getChild('视频管理');
        self::assertNotNull($videoMenu);

        // 验证 VOD 配置菜单
        $vodConfigMenu = $videoMenu->getChild('VOD配置');
        self::assertNotNull($vodConfigMenu);
        self::assertSame('/admin/aliyun-vod-config', $vodConfigMenu->getUri());
        self::assertSame('fas fa-cog', $vodConfigMenu->getAttribute('icon'));

        // 验证视频列表菜单
        $videoListMenu = $videoMenu->getChild('视频列表');
        self::assertNotNull($videoListMenu);
        self::assertSame('/admin/video', $videoListMenu->getUri());
        self::assertSame('fas fa-video', $videoListMenu->getAttribute('icon'));

        // 验证转码任务菜单
        $transcodeTaskMenu = $videoMenu->getChild('转码任务');
        self::assertNotNull($transcodeTaskMenu);
        self::assertSame('/admin/transcode-task', $transcodeTaskMenu->getUri());
        self::assertSame('fas fa-tasks', $transcodeTaskMenu->getAttribute('icon'));

        // 验证播放记录菜单
        $playRecordMenu = $videoMenu->getChild('播放记录');
        self::assertNotNull($playRecordMenu);
        self::assertSame('/admin/play-record', $playRecordMenu->getUri());
        self::assertSame('fas fa-play-circle', $playRecordMenu->getAttribute('icon'));
    }

    public function testInvokeUsesExistingVideoManagementMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);
        $existingVideoMenu = $rootMenu->addChild('视频管理');

        ($this->adminMenu)($rootMenu);

        $videoMenu = $rootMenu->getChild('视频管理');
        self::assertSame($existingVideoMenu, $videoMenu);

        // 验证子菜单仍然被创建
        $vodConfigMenu = $videoMenu->getChild('VOD配置');
        self::assertNotNull($vodConfigMenu);
        self::assertSame('/admin/aliyun-vod-config', $vodConfigMenu->getUri());

        $videoListMenu = $videoMenu->getChild('视频列表');
        self::assertNotNull($videoListMenu);
        self::assertSame('/admin/video', $videoListMenu->getUri());

        $transcodeTaskMenu = $videoMenu->getChild('转码任务');
        self::assertNotNull($transcodeTaskMenu);
        self::assertSame('/admin/transcode-task', $transcodeTaskMenu->getUri());

        $playRecordMenu = $videoMenu->getChild('播放记录');
        self::assertNotNull($playRecordMenu);
        self::assertSame('/admin/play-record', $playRecordMenu->getUri());
    }

    public function testMenuItemsHaveCorrectIcons(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        ($this->adminMenu)($rootMenu);

        $videoMenu = $rootMenu->getChild('视频管理');
        self::assertNotNull($videoMenu);

        $expectedIcons = [
            'VOD配置' => 'fas fa-cog',
            '视频列表' => 'fas fa-video',
            '转码任务' => 'fas fa-tasks',
            '播放记录' => 'fas fa-play-circle',
        ];

        foreach ($expectedIcons as $menuName => $expectedIcon) {
            $menuItem = $videoMenu->getChild($menuName);
            self::assertNotNull($menuItem, sprintf('菜单项 "%s" 应该存在', $menuName));
            self::assertSame($expectedIcon, $menuItem->getAttribute('icon'), sprintf('菜单项 "%s" 的图标应该是 "%s"', $menuName, $expectedIcon));
        }
    }
}
