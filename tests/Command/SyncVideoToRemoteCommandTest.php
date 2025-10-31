<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\SyncVideoToRemoteCommand;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(SyncVideoToRemoteCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncVideoToRemoteCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // Command 测试无需特殊设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncVideoToRemoteCommand::class);
        $this->assertInstanceOf(SyncVideoToRemoteCommand::class, $command);

        return new CommandTester($command);
    }

    public function testCommandInheritance(): void
    {
        $command = self::getService(SyncVideoToRemoteCommand::class);
        $this->assertInstanceOf(Command::class, $command);
    }

    public function testCommandExecute(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试命令能正常执行（使用试运行模式）
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionVideoId(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--video-id选项
        $commandTester->execute(['--video-id' => '123', '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionStatus(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--status选项
        $commandTester->execute(['--status' => 'Normal', '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionLimit(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--limit选项
        $commandTester->execute(['--limit' => '30', '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionDryRun(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--dry-run选项
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
