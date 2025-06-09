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
 * 生成统计报表
 */
#[AsCommand(
    name: self::NAME,
    description: '生成播放统计报表'
)]
class GenerateStatisticsCommand extends Command
{
    public const NAME = 'aliyun-vod:statistics:generate';

    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, '指定统计日期 (Y-m-d)', null)
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, '统计类型 (daily|weekly|monthly)', 'daily')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, '输出格式 (console|json|csv)', 'console')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, '输出文件路径', null)
            ->setHelp('此命令生成播放统计报表，支持日报、周报、月报等多种格式。');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = $input->getOption('date');
        $type = $input->getOption('type');
        $outputFormat = $input->getOption('output');
        $outputFile = $input->getOption('file');

        $io->title('生成播放统计报表');

        try {
            // 解析日期
            $targetDate = $date ? new \DateTime($date) : new \DateTime('yesterday');
            
            $io->info("生成 {$targetDate->format('Y-m-d')} 的{$this->getTypeLabel($type)}统计报表");

            // 生成统计数据
            $statistics = $this->generateStatistics($type, $targetDate);

            // 输出统计结果
            $this->outputStatistics($statistics, $outputFormat, $outputFile, $io);

            $this->logger->info('统计报表生成完成', [
                'type' => $type,
                'date' => $targetDate->format('Y-m-d'),
                'outputFormat' => $outputFormat,
                'outputFile' => $outputFile,
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error("生成统计报表时发生错误: {$e->getMessage()}");
            $this->logger->error('统计报表生成失败', [
                'error' => $e->getMessage(),
                'exception' => $e,
                'type' => $type,
                'date' => $date,
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * 生成统计数据
     */
    private function generateStatistics(string $type, \DateTime $targetDate): array
    {
        switch ($type) {
            case 'daily':
                return $this->generateDailyStatistics($targetDate);
            case 'weekly':
                return $this->generateWeeklyStatistics($targetDate);
            case 'monthly':
                return $this->generateMonthlyStatistics($targetDate);
            default:
                throw new \InvalidArgumentException("不支持的统计类型: {$type}");
        }
    }

    /**
     * 生成日统计
     */
    private function generateDailyStatistics(\DateTime $date): array
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0);
        $endDate = clone $date;
        $endDate->setTime(23, 59, 59);

        $stats = $this->statisticsService->getPlayStatsByDateRange($startDate, $endDate);
        $popularVideos = $this->statisticsService->getPopularVideos(10);

        return [
            'type' => 'daily',
            'date' => $date->format('Y-m-d'),
            'period' => $date->format('Y年m月d日'),
            'totalPlays' => $stats['totalPlays'],
            'uniqueVideos' => $stats['uniqueVideos'],
            'deviceStats' => $stats['deviceStats'],
            'popularVideos' => array_slice($popularVideos, 0, 5),
        ];
    }

    /**
     * 生成周统计
     */
    private function generateWeeklyStatistics(\DateTime $date): array
    {
        $startDate = clone $date;
        $startDate->modify('monday this week')->setTime(0, 0, 0);
        $endDate = clone $startDate;
        $endDate->modify('+6 days')->setTime(23, 59, 59);

        $stats = $this->statisticsService->getPlayStatsByDateRange($startDate, $endDate);
        $popularVideos = $this->statisticsService->getPopularVideos(10);

        return [
            'type' => 'weekly',
            'date' => $date->format('Y-m-d'),
            'period' => $startDate->format('Y年m月d日') . ' - ' . $endDate->format('Y年m月d日'),
            'totalPlays' => $stats['totalPlays'],
            'uniqueVideos' => $stats['uniqueVideos'],
            'deviceStats' => $stats['deviceStats'],
            'dailyStats' => $stats['dailyStats'],
            'popularVideos' => array_slice($popularVideos, 0, 10),
        ];
    }

    /**
     * 生成月统计
     */
    private function generateMonthlyStatistics(\DateTime $date): array
    {
        $startDate = clone $date;
        $startDate->modify('first day of this month')->setTime(0, 0, 0);
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        $stats = $this->statisticsService->getPlayStatsByDateRange($startDate, $endDate);
        $popularVideos = $this->statisticsService->getPopularVideos(20);

        return [
            'type' => 'monthly',
            'date' => $date->format('Y-m-d'),
            'period' => $date->format('Y年m月'),
            'totalPlays' => $stats['totalPlays'],
            'uniqueVideos' => $stats['uniqueVideos'],
            'deviceStats' => $stats['deviceStats'],
            'dailyStats' => $stats['dailyStats'],
            'popularVideos' => array_slice($popularVideos, 0, 20),
        ];
    }

    /**
     * 输出统计结果
     */
    private function outputStatistics(array $statistics, string $format, ?string $file, SymfonyStyle $io): void
    {
        switch ($format) {
            case 'console':
                $this->outputToConsole($statistics, $io);
                break;
            case 'json':
                $this->outputToJson($statistics, $file, $io);
                break;
            case 'csv':
                $this->outputToCsv($statistics, $file, $io);
                break;
            default:
                throw new \InvalidArgumentException("不支持的输出格式: {$format}");
        }
    }

    /**
     * 控制台输出
     */
    private function outputToConsole(array $statistics, SymfonyStyle $io): void
    {
        $io->section("📊 {$statistics['period']} 播放统计报表");

        $io->definitionList(
            ['总播放次数' => number_format($statistics['totalPlays'])],
            ['独立视频数' => number_format($statistics['uniqueVideos'])],
        );

        if (!empty($statistics['deviceStats'])) {
            $io->section('📱 设备类型分布');
            $deviceTable = [];
            foreach ($statistics['deviceStats'] as $device => $count) {
                $percentage = round(($count / $statistics['totalPlays']) * 100, 1);
                $deviceTable[] = [$device, number_format($count), "{$percentage}%"];
            }
            $io->table(['设备类型', '播放次数', '占比'], $deviceTable);
        }

        if (!empty($statistics['popularVideos'])) {
            $io->section('🔥 热门视频');
            $videoTable = [];
            foreach ($statistics['popularVideos'] as $index => $video) {
                $videoTable[] = [
                    $index + 1,
                    mb_substr($video['title'], 0, 30) . (mb_strlen($video['title']) > 30 ? '...' : ''),
                    number_format($video['play_count']),
                ];
            }
            $io->table(['排名', '视频标题', '播放次数'], $videoTable);
        }
    }

    /**
     * JSON格式输出
     */
    private function outputToJson(array $statistics, ?string $file, SymfonyStyle $io): void
    {
        $json = json_encode($statistics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($file) {
            file_put_contents($file, $json);
            $io->success("统计报表已保存到: {$file}");
        } else {
            $io->writeln($json);
        }
    }

    /**
     * CSV格式输出
     */
    private function outputToCsv(array $statistics, ?string $file, SymfonyStyle $io): void
    {
        $csv = "统计类型,日期,总播放次数,独立视频数\n";
        $csv .= "{$statistics['type']},{$statistics['date']},{$statistics['totalPlays']},{$statistics['uniqueVideos']}\n";
        
        if ($file) {
            file_put_contents($file, $csv);
            $io->success("统计报表已保存到: {$file}");
        } else {
            $io->writeln($csv);
        }
    }

    /**
     * 获取类型标签
     */
    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'daily' => '日',
            'weekly' => '周',
            'monthly' => '月',
            default => $type,
        };
    }
} 