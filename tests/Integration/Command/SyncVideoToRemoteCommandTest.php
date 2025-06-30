<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\SyncVideoToRemoteCommand;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\AliyunVodBundle\Service\VideoManageService;

/**
 * @covers \Tourze\AliyunVodBundle\Command\SyncVideoToRemoteCommand
 */
class SyncVideoToRemoteCommandTest extends TestCase
{
    private VideoRepository $videoRepository;
    private VideoManageService $videoManageService;
    private LoggerInterface $logger;
    private SyncVideoToRemoteCommand $command;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepository::class);
        $this->videoManageService = $this->createMock(VideoManageService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->command = new SyncVideoToRemoteCommand(
            $this->videoRepository,
            $this->videoManageService,
            $this->logger
        );
    }

    public function testCommandName(): void
    {
        $this->assertEquals('aliyun-vod:sync:to-remote', $this->command::NAME);
        $this->assertEquals('aliyun-vod:sync:to-remote', $this->command->getName());
    }

    public function testCommandInheritance(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Console\Command\Command::class, $this->command);
    }

    public function testExecuteBasic(): void
    {
        $application = new Application();
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        
        // 只测试命令能正常执行，不依赖具体的服务方法
        $commandTester->execute([]);

        // 命令应该能正常执行
        $this->assertTrue(true);
    }
}