<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\GenerateStatisticsCommand;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * @covers \Tourze\AliyunVodBundle\Command\GenerateStatisticsCommand
 */
class GenerateStatisticsCommandTest extends TestCase
{
    private StatisticsService $statisticsService;
    private LoggerInterface $logger;
    private GenerateStatisticsCommand $command;

    protected function setUp(): void
    {
        $this->statisticsService = $this->createMock(StatisticsService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->command = new GenerateStatisticsCommand($this->statisticsService, $this->logger);
    }

    public function testCommandName(): void
    {
        $this->assertEquals('aliyun-vod:statistics:generate', $this->command::NAME);
        $this->assertEquals('aliyun-vod:statistics:generate', $this->command->getName());
    }

    public function testCommandInheritance(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Console\Command\Command::class, $this->command);
    }

    public function testExecuteBasic(): void
    {
        // 配置 mock 返回有效的统计数据结构
        $this->statisticsService
            ->method('getPlayStatsByDateRange')
            ->willReturn([
                'totalPlays' => 100,
                'uniqueVideos' => 10,
                'deviceStats' => ['mobile' => 60, 'desktop' => 40],
                'dailyStats' => []
            ]);
            
        $this->statisticsService
            ->method('getPopularVideos')
            ->willReturn([
                ['title' => 'Video 1', 'play_count' => 50],
                ['title' => 'Video 2', 'play_count' => 30]
            ]);

        $application = new Application();
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        
        // 只测试命令能正常执行，不依赖具体的服务方法
        $commandTester->execute([]);

        // 命令应该能正常执行
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}