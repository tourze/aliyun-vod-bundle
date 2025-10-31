<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Command;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\AliyunVodBundle\Exception\StatisticsGenerationException;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 生成统计报表
 */
#[AsCommand(name: self::NAME, description: '生成播放统计报表', help: <<<'TXT'
    此命令生成播放统计报表，支持日报、周报、月报等多种格式。
    TXT)]
#[WithMonologChannel(channel: 'aliyun_vod')]
class GenerateStatisticsCommand extends Command
{
    public const NAME = 'aliyun-vod:statistics:generate';

    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly LoggerInterface $logger,
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dateOption = $input->getOption('date');
        $date = is_string($dateOption) ? $dateOption : null;
        $typeOption = $input->getOption('type');
        $type = is_string($typeOption) ? $typeOption : 'daily';
        $outputFormatOption = $input->getOption('output');
        $outputFormat = is_string($outputFormatOption) ? $outputFormatOption : 'console';
        $outputFileOption = $input->getOption('file');
        $outputFile = is_string($outputFileOption) ? $outputFileOption : null;

        $io->title('生成播放统计报表');

        try {
            // 解析日期
            $targetDate = null !== $date ? new \DateTime($date) : new \DateTime('yesterday');

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
     *
     * @return array<string, mixed>
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
                throw new StatisticsGenerationException("不支持的统计类型: {$type}");
        }
    }

    /**
     * 生成日统计
     *
     * @return array<string, mixed>
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
     *
     * @return array<string, mixed>
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
     *
     * @return array<string, mixed>
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
     *
     * @param array<string, mixed> $statistics
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
                throw new StatisticsGenerationException("不支持的输出格式: {$format}");
        }
    }

    /**
     * 控制台输出
     *
     * @param array<string, mixed> $statistics
     */
    private function outputToConsole(array $statistics, SymfonyStyle $io): void
    {
        $this->outputBasicStatistics($statistics, $io);
        $this->outputDeviceStatistics($statistics, $io);
        $this->outputPopularVideos($statistics, $io);
    }

    /**
     * @param array<string, mixed> $statistics
     */
    private function outputBasicStatistics(array $statistics, SymfonyStyle $io): void
    {
        $period = is_string($statistics['period']) ? $statistics['period'] : '未知时间段';
        $io->section("📊 {$period} 播放统计报表");

        $io->definitionList(
            ['总播放次数' => number_format(is_numeric($statistics['totalPlays']) ? (float) $statistics['totalPlays'] : 0)],
            ['独立视频数' => number_format(is_numeric($statistics['uniqueVideos']) ? (float) $statistics['uniqueVideos'] : 0)],
        );
    }

    /**
     * @param array<string, mixed> $statistics
     */
    private function outputDeviceStatistics(array $statistics, SymfonyStyle $io): void
    {
        if (null === $statistics['deviceStats'] || [] === $statistics['deviceStats']) {
            return;
        }

        $io->section('📱 设备类型分布');
        $deviceStats = $statistics['deviceStats'];
        if (!is_array($deviceStats)) {
            return;
        }
        $deviceTable = [];
        $totalPlays = is_numeric($statistics['totalPlays']) ? (float) $statistics['totalPlays'] : 1;
        foreach ($deviceStats as $device => $count) {
            $countNum = is_numeric($count) ? (float) $count : 0;
            $percentage = round(($countNum / $totalPlays) * 100, 1);
            $deviceTable[] = [$device, number_format($countNum), "{$percentage}%"];
        }
        $io->table(['设备类型', '播放次数', '占比'], $deviceTable);
    }

    /**
     * @param array<string, mixed> $statistics
     */
    private function outputPopularVideos(array $statistics, SymfonyStyle $io): void
    {
        if (null === $statistics['popularVideos'] || [] === $statistics['popularVideos']) {
            return;
        }

        $io->section('🔥 热门视频');
        $popularVideos = $statistics['popularVideos'];
        if (!is_array($popularVideos)) {
            return;
        }
        $videoTable = [];
        foreach ($popularVideos as $index => $video) {
            if (!is_array($video)) {
                continue;
            }
            $title = isset($video['title']) && is_string($video['title']) ? $this->truncateTitle($video['title']) : '未知标题';
            $playCount = isset($video['playCount']) && is_numeric($video['playCount']) ? (float) $video['playCount'] : 0;
            $videoTable[] = [
                $index + 1,
                $title,
                number_format($playCount),
            ];
        }
        $io->table(['排名', '视频标题', '播放次数'], $videoTable);
    }

    private function truncateTitle(string $title): string
    {
        return mb_substr($title, 0, 30) . (mb_strlen($title) > 30 ? '...' : '');
    }

    /**
     * JSON格式输出
     *
     * @param array<string, mixed> $statistics
     */
    private function outputToJson(array $statistics, ?string $file, SymfonyStyle $io): void
    {
        $json = json_encode($statistics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (false === $json) {
            throw new StatisticsGenerationException('JSON编码失败');
        }

        if (null !== $file) {
            file_put_contents($file, $json);
            $io->success("统计报表已保存到: {$file}");
        } else {
            $io->writeln($json);
        }
    }

    /**
     * CSV格式输出
     *
     * @param array<string, mixed> $statistics
     */
    private function outputToCsv(array $statistics, ?string $file, SymfonyStyle $io): void
    {
        $csv = "统计类型,日期,总播放次数,独立视频数\n";
        $type = is_string($statistics['type']) ? $statistics['type'] : 'unknown';
        $date = is_string($statistics['date']) ? $statistics['date'] : 'unknown';
        $totalPlays = is_numeric($statistics['totalPlays']) ? $statistics['totalPlays'] : 0;
        $uniqueVideos = is_numeric($statistics['uniqueVideos']) ? $statistics['uniqueVideos'] : 0;
        $csv .= "{$type},{$date},{$totalPlays},{$uniqueVideos}\n";

        if (null !== $file) {
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
