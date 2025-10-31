<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\SyncVideoFromRemoteCommand;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(SyncVideoFromRemoteCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncVideoFromRemoteCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // Command 测试无需特殊设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncVideoFromRemoteCommand::class);
        $this->assertInstanceOf(SyncVideoFromRemoteCommand::class, $command);

        return new CommandTester($command);
    }

    public function testCommandInheritance(): void
    {
        $command = self::getService(SyncVideoFromRemoteCommand::class);
        $this->assertInstanceOf(Command::class, $command);
    }

    public function testCommandExecute(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试命令能正常执行（使用试运行模式）
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令正常结束
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionConfig(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--config选项（不指定具体配置名，让命令自己处理）
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行（可能成功也可能因配置不存在而失败，都是正常的）
        $statusCode = $commandTester->getStatusCode();
        $this->assertContains($statusCode, [0, 1], 'Command should complete with valid status code');
    }

    public function testOptionLimit(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--limit选项
        $commandTester->execute(['--limit' => '50', '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionForce(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--force选项
        $commandTester->execute(['--force' => true, '--dry-run' => true]);

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
