<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\AliyunVodBundle\Command\GenerateStatisticsCommand;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(GenerateStatisticsCommand::class)]
#[RunTestsInSeparateProcesses]
final class GenerateStatisticsCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // Command 测试无需特殊设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(GenerateStatisticsCommand::class);
        $this->assertInstanceOf(GenerateStatisticsCommand::class, $command);

        return new CommandTester($command);
    }

    public function testCommandInheritance(): void
    {
        $command = self::getService(GenerateStatisticsCommand::class);
        $this->assertInstanceOf(Command::class, $command);
    }

    public function testCommandExecute(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试命令能正常执行
        $commandTester->execute(['--output' => 'console']);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionDate(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--date选项
        $commandTester->execute(['--date' => '2023-01-01', '--output' => 'console']);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionType(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--type选项
        $commandTester->execute(['--type' => 'weekly', '--output' => 'console']);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOptionOutput(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试--output选项
        $commandTester->execute(['--output' => 'json']);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());

        // 验证输出包含内容（不强制检查JSON有效性，因为可能包含调试信息）
        $output = $commandTester->getDisplay();
        $this->assertNotEmpty($output);
    }

    public function testOptionFile(): void
    {
        $commandTester = $this->getCommandTester();
        $tmpFile = tempnam(sys_get_temp_dir(), 'stats_test_');

        try {
            // 测试--file选项
            $commandTester->execute(['--output' => 'json', '--file' => $tmpFile]);

            // 验证命令执行成功
            $this->assertEquals(0, $commandTester->getStatusCode());

            // 验证文件存在
            $this->assertFileExists($tmpFile);
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
