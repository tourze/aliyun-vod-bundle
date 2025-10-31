<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\CleanupPlayRecordsCommand;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(CleanupPlayRecordsCommand::class)]
#[RunTestsInSeparateProcesses]
final class CleanupPlayRecordsCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // Command 测试无需特殊设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CleanupPlayRecordsCommand::class);
        $this->assertInstanceOf(CleanupPlayRecordsCommand::class, $command);

        return new CommandTester($command);
    }

    public function testCommandInheritance(): void
    {
        $command = self::getService(CleanupPlayRecordsCommand::class);
        $this->assertInstanceOf(Command::class, $command);
    }

    public function testCommandExecute(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试命令能正常执行（使用试运行模式避免实际删除数据）
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionDays(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--days选项
        $commandTester->execute(['--days' => '30', '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionDryRun(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--dry-run选项
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行成功且输出包含试运行信息
        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('试运行模式', $commandTester->getDisplay());
    }

    public function testOptionForce(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--force选项（结合dry-run避免实际删除）
        $commandTester->execute(['--force' => true, '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
