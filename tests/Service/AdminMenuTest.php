<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
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

    private LinkGeneratorInterface $linkGenerator;

    protected function onSetUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    protected function getMenuProvider(): object
    {
        return $this->adminMenu;
    }

    public function testInvokeAddsVideoManagementMenu(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);

        $mainItem = $this->createMock(ItemInterface::class);
        $videoItem = $this->createMock(ItemInterface::class);
        $configMenuItem = $this->createMock(ItemInterface::class);
        $videoMenuItem = $this->createMock(ItemInterface::class);
        $transcodeMenuItem = $this->createMock(ItemInterface::class);
        $playRecordMenuItem = $this->createMock(ItemInterface::class);

        // 模拟LinkGenerator行为 - 四次调用
        $this->assertInstanceOf(MockObject::class, $this->linkGenerator);
        $this->linkGenerator->expects($this->exactly(4))
            ->method('getCurdListPage')
            ->willReturnMap([
                [AliyunVodConfig::class, '/admin/aliyun-vod-config'],
                [Video::class, '/admin/video'],
                [TranscodeTask::class, '/admin/transcode-task'],
                [PlayRecord::class, '/admin/play-record'],
            ])
        ;

        // 第一次调用 getChild 返回 null，第二次返回已创建的菜单项
        $mainItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('视频管理')
            ->willReturnOnConsecutiveCalls(null, $videoItem)
        ;

        // 创建视频管理父菜单
        $mainItem->expects($this->once())
            ->method('addChild')
            ->with('视频管理')
            ->willReturn($videoItem)
        ;

        // 添加四个子菜单
        $videoItem->expects($this->exactly(4))
            ->method('addChild')
            ->willReturnMap([
                ['VOD配置', [], $configMenuItem],
                ['视频列表', [], $videoMenuItem],
                ['转码任务', [], $transcodeMenuItem],
                ['播放记录', [], $playRecordMenuItem],
            ])
        ;

        // 配置菜单项设置
        $configMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/aliyun-vod-config')
        ;
        $configMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-cog')
        ;

        // 视频菜单项设置
        $videoMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/video')
        ;
        $videoMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-video')
        ;

        // 转码任务菜单项设置
        $transcodeMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/transcode-task')
        ;
        $transcodeMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-tasks')
        ;

        // 播放记录菜单项设置
        $playRecordMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/play-record')
        ;
        $playRecordMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-play-circle')
        ;

        // 调用管理菜单
        ($this->adminMenu)($mainItem);
    }

    public function testInvokeWithExistingVideoManagementMenu(): void
    {
        $mainItem = $this->createMock(ItemInterface::class);
        $videoItem = $this->createMock(ItemInterface::class);
        $configMenuItem = $this->createMock(ItemInterface::class);
        $videoMenuItem = $this->createMock(ItemInterface::class);
        $transcodeMenuItem = $this->createMock(ItemInterface::class);
        $playRecordMenuItem = $this->createMock(ItemInterface::class);

        // 模拟LinkGenerator行为
        $this->assertInstanceOf(MockObject::class, $this->linkGenerator);
        $this->linkGenerator->expects($this->exactly(4))
            ->method('getCurdListPage')
            ->willReturnMap([
                [AliyunVodConfig::class, '/admin/aliyun-vod-config'],
                [Video::class, '/admin/video'],
                [TranscodeTask::class, '/admin/transcode-task'],
                [PlayRecord::class, '/admin/play-record'],
            ])
        ;

        // 两次调用 getChild 都返回已存在的菜单项
        $mainItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('视频管理')
            ->willReturn($videoItem)
        ;

        // 不应该创建新的父菜单
        $mainItem->expects($this->never())
            ->method('addChild')
        ;

        // 添加四个子菜单
        $videoItem->expects($this->exactly(4))
            ->method('addChild')
            ->willReturnMap([
                ['VOD配置', [], $configMenuItem],
                ['视频列表', [], $videoMenuItem],
                ['转码任务', [], $transcodeMenuItem],
                ['播放记录', [], $playRecordMenuItem],
            ])
        ;

        // 设置菜单项属性

        // 调用管理菜单
        ($this->adminMenu)($mainItem);
    }

    public function testInvokeReturnsEarlyWhenVideoMenuIsNull(): void
    {
        $mainItem = $this->createMock(ItemInterface::class);
        $nullMenuItem = $this->createMock(ItemInterface::class);

        // 第一次调用 getChild 返回 null，第二次也返回 null
        $mainItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('视频管理')
            ->willReturnOnConsecutiveCalls(null, null)
        ;

        // 创建视频管理父菜单但getChild仍返回null（模拟创建失败的情况）
        $mainItem->expects($this->once())
            ->method('addChild')
            ->with('视频管理')
            ->willReturn($nullMenuItem)
        ;

        // LinkGenerator不应该被调用
        $this->assertInstanceOf(MockObject::class, $this->linkGenerator);
        $this->linkGenerator->expects($this->never())
            ->method('getCurdListPage')
        ;

        // 调用管理菜单
        ($this->adminMenu)($mainItem);
    }
}
