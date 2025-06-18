<?php

namespace Tourze\AliyunVodBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 清理过期播放记录
 */
#[AsCommand(
    name: self::NAME,
    description: '清理过期的播放记录数据'
)]
class CleanupPlayRecordsCommand extends Command
{
    public const NAME = 'aliyun-vod:cleanup:play-records';

    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, '保留天数', 90)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际删除数据')
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制执行，不询问确认')
            ->setHelp('此命令清理指定天数之前的播放记录，默认保留90天的数据。');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $daysToKeep = (int) $input->getOption('days');
        $dryRun = (bool) $input->getOption('dry-run');
        $force = (bool) $input->getOption('force');

        $io->title('清理过期播放记录');

        if ($daysToKeep < 1) {
            $io->error('保留天数必须大于0');
            return Command::FAILURE;
        }

        $expireDate = new \DateTime("-{$daysToKeep} days");
        $io->info("将清理 {$expireDate->format('Y-m-d H:i:s')} 之前的播放记录");

        if (!$force && !$dryRun) {
            if (!$io->confirm('确定要执行清理操作吗？此操作不可逆！', false)) {
                $io->note('操作已取消');
                return Command::SUCCESS;
            }
        }

        try {
            if ($dryRun) {
                $io->note('试运行模式：不会实际删除数据');
                $io->success('试运行完成');
                return Command::SUCCESS;
            }

            $deletedCount = $this->statisticsService->cleanExpiredPlayRecords($daysToKeep);

            $io->success([
                "清理完成！",
                "删除记录数: {$deletedCount}",
                "保留天数: {$daysToKeep}",
            ]);

            $this->logger->info('播放记录清理完成', [
                'deletedCount' => $deletedCount,
                'daysToKeep' => $daysToKeep,
                'expireDate' => $expireDate->format('Y-m-d H:i:s'),
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error("清理过程中发生错误: {$e->getMessage()}");
            $this->logger->error('播放记录清理失败', [
                'error' => $e->getMessage(),
                'exception' => $e,
                'daysToKeep' => $daysToKeep,
            ]);
            return Command::FAILURE;
        }
    }
} 