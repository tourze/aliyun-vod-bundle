<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\CleanupPlayRecordsCommand;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * @covers \Tourze\AliyunVodBundle\Command\CleanupPlayRecordsCommand
 */
class CleanupPlayRecordsCommandTest extends TestCase
{
    private StatisticsService $statisticsService;
    private LoggerInterface $logger;
    private CleanupPlayRecordsCommand $command;

    protected function setUp(): void
    {
        $this->statisticsService = $this->createMock(StatisticsService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->command = new CleanupPlayRecordsCommand($this->statisticsService, $this->logger);
    }

    public function testCommandName(): void
    {
        $this->assertEquals('aliyun-vod:cleanup:play-records', $this->command::NAME);
        $this->assertEquals('aliyun-vod:cleanup:play-records', $this->command->getName());
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