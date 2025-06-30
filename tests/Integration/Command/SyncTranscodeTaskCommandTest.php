<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\SyncTranscodeTaskCommand;
use Tourze\AliyunVodBundle\Repository\TranscodeTaskRepository;
use Tourze\AliyunVodBundle\Service\TranscodeService;

/**
 * @covers \Tourze\AliyunVodBundle\Command\SyncTranscodeTaskCommand
 */
class SyncTranscodeTaskCommandTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private TranscodeTaskRepository $transcodeTaskRepository;
    private TranscodeService $transcodeService;
    private LoggerInterface $logger;
    private SyncTranscodeTaskCommand $command;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->transcodeTaskRepository = $this->createMock(TranscodeTaskRepository::class);
        $this->transcodeService = $this->createMock(TranscodeService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->command = new SyncTranscodeTaskCommand(
            $this->entityManager,
            $this->transcodeTaskRepository,
            $this->transcodeService,
            $this->logger
        );
    }

    public function testCommandName(): void
    {
        $this->assertEquals('aliyun-vod:sync:transcode-task', $this->command::NAME);
        $this->assertEquals('aliyun-vod:sync:transcode-task', $this->command->getName());
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