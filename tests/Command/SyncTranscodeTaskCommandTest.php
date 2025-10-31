<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\SyncTranscodeTaskCommand;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(SyncTranscodeTaskCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncTranscodeTaskCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // Command 测试无需特殊设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncTranscodeTaskCommand::class);
        $this->assertInstanceOf(SyncTranscodeTaskCommand::class, $command);

        return new CommandTester($command);
    }

    public function testCommandInheritance(): void
    {
        $command = self::getService(SyncTranscodeTaskCommand::class);
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

    public function testOptionTaskId(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--task-id选项
        $commandTester->execute(['--task-id' => 'test-task-id', '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionStatus(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--status选项
        $commandTester->execute(['--status' => 'Processing', '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionLimit(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--limit选项
        $commandTester->execute(['--limit' => '10', '--dry-run' => true]);

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
