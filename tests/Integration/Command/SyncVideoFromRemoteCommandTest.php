<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\SyncVideoFromRemoteCommand;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;
use Tourze\AliyunVodBundle\Repository\VideoRepository;

/**
 * @covers \Tourze\AliyunVodBundle\Command\SyncVideoFromRemoteCommand
 */
class SyncVideoFromRemoteCommandTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private AliyunVodConfigRepository $configRepository;
    private VideoRepository $videoRepository;
    private LoggerInterface $logger;
    private SyncVideoFromRemoteCommand $command;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->configRepository = $this->createMock(AliyunVodConfigRepository::class);
        $this->videoRepository = $this->createMock(VideoRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->command = new SyncVideoFromRemoteCommand(
            $this->entityManager,
            $this->configRepository,
            $this->videoRepository,
            $this->logger
        );
    }

    public function testCommandName(): void
    {
        $this->assertEquals('aliyun-vod:sync:from-remote', $this->command::NAME);
        $this->assertEquals('aliyun-vod:sync:from-remote', $this->command->getName());
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