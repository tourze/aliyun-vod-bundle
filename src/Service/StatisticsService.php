<?php

namespace Tourze\AliyunVodBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\AliyunVodBundle\Repository\PlayRecordRepository;

/**
 * 播放统计服务
 */
class StatisticsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlayRecordRepository $playRecordRepository
    ) {
    }

    /**
     * 记录播放行为
     */
    public function recordPlay(
        Video $video,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $referer = null,
        ?int $playDuration = null,
        ?int $playPosition = null,
        ?string $playQuality = null,
        ?string $deviceType = null,
        ?string $playerVersion = null
    ): PlayRecord {
        $playRecord = new PlayRecord();
        $playRecord->setVideo($video)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setReferer($referer)
            ->setPlayDuration($playDuration)
            ->setPlayPosition($playPosition)
            ->setPlayQuality($playQuality)
            ->setDeviceType($deviceType)
            ->setPlayerVersion($playerVersion);

        $this->entityManager->persist($playRecord);
        $this->entityManager->flush();

        return $playRecord;
    }

    /**
     * 获取视频播放统计
     */
    public function getVideoPlayStats(Video $video): array
    {
        $totalPlays = $this->playRecordRepository->countByVideo($video);
        $playRecords = $this->playRecordRepository->findByVideo($video);

        // 计算平均播放时长
        $totalDuration = 0;
        $validDurationCount = 0;
        $deviceStats = [];
        $qualityStats = [];

        foreach ($playRecords as $record) {
            if ($record->getPlayDuration()) {
                $totalDuration += $record->getPlayDuration();
                $validDurationCount++;
            }

            // 设备类型统计
            $deviceType = $record->getDeviceType() ?: 'Unknown';
            $deviceStats[$deviceType] = ($deviceStats[$deviceType] ?? 0) + 1;

            // 播放质量统计
            $quality = $record->getPlayQuality() ?: 'Unknown';
            $qualityStats[$quality] = ($qualityStats[$quality] ?? 0) + 1;
        }

        $avgDuration = $validDurationCount > 0 ? round($totalDuration / $validDurationCount, 2) : 0;

        return [
            'videoId' => $video->getVideoId(),
            'title' => $video->getTitle(),
            'totalPlays' => $totalPlays,
            'averagePlayDuration' => $avgDuration,
            'deviceStats' => $deviceStats,
            'qualityStats' => $qualityStats,
        ];
    }

    /**
     * 获取热门视频排行
     */
    public function getPopularVideos(int $limit = 10): array
    {
        return $this->playRecordRepository->getPopularVideos($limit);
    }

    /**
     * 获取指定时间范围内的播放统计
     */
    public function getPlayStatsByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $playRecords = $this->playRecordRepository->findByDateRange($startDate, $endDate);

        $dailyStats = [];
        $totalPlays = count($playRecords);
        $uniqueVideos = [];
        $deviceStats = [];

        foreach ($playRecords as $record) {
            $date = $record->getPlayTime()->format('Y-m-d');
            $dailyStats[$date] = ($dailyStats[$date] ?? 0) + 1;

            $uniqueVideos[$record->getVideo()->getId()] = true;

            $deviceType = $record->getDeviceType() ?: 'Unknown';
            $deviceStats[$deviceType] = ($deviceStats[$deviceType] ?? 0) + 1;
        }

        return [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalPlays' => $totalPlays,
            'uniqueVideos' => count($uniqueVideos),
            'dailyStats' => $dailyStats,
            'deviceStats' => $deviceStats,
        ];
    }

    /**
     * 获取用户播放行为分析
     */
    public function getUserPlayBehavior(string $ipAddress): array
    {
        $playRecords = $this->playRecordRepository->findByIpAddress($ipAddress);

        $totalPlays = count($playRecords);
        $uniqueVideos = [];
        $totalDuration = 0;
        $validDurationCount = 0;
        $preferredQuality = [];

        foreach ($playRecords as $record) {
            $uniqueVideos[$record->getVideo()->getId()] = $record->getVideo()->getTitle();

            if ($record->getPlayDuration()) {
                $totalDuration += $record->getPlayDuration();
                $validDurationCount++;
            }

            $quality = $record->getPlayQuality() ?: 'Unknown';
            $preferredQuality[$quality] = ($preferredQuality[$quality] ?? 0) + 1;
        }

        $avgDuration = $validDurationCount > 0 ? round($totalDuration / $validDurationCount, 2) : 0;

        return [
            'ipAddress' => $ipAddress,
            'totalPlays' => $totalPlays,
            'uniqueVideosWatched' => count($uniqueVideos),
            'averagePlayDuration' => $avgDuration,
            'preferredQuality' => $preferredQuality,
            'watchedVideos' => $uniqueVideos,
        ];
    }

    /**
     * 获取实时播放统计
     */
    public function getRealTimeStats(): array
    {
        $today = new \DateTime('today');
        $yesterday = new \DateTime('yesterday');

        $todayStats = $this->getPlayStatsByDateRange($today, new \DateTime());
        $yesterdayStats = $this->getPlayStatsByDateRange($yesterday, $today);

        return [
            'today' => $todayStats,
            'yesterday' => $yesterdayStats,
            'growth' => [
                'plays' => $todayStats['totalPlays'] - $yesterdayStats['totalPlays'],
                'videos' => $todayStats['uniqueVideos'] - $yesterdayStats['uniqueVideos'],
            ],
        ];
    }

    /**
     * 获取视频完播率统计
     */
    public function getVideoCompletionRate(Video $video): array
    {
        $playRecords = $this->playRecordRepository->findByVideo($video);
        $videoDuration = $video->getDuration();

        if ($videoDuration === null || empty($playRecords)) {
            return [
                'videoId' => $video->getVideoId(),
                'completionRate' => 0,
                'totalPlays' => 0,
                'completedPlays' => 0,
            ];
        }

        $totalPlays = count($playRecords);
        $completedPlays = 0;

        foreach ($playRecords as $record) {
            $playPosition = $record->getPlayPosition();
            if ($playPosition && $playPosition >= $videoDuration * 0.9) { // 播放90%以上算完播
                $completedPlays++;
            }
        }

        $completionRate = $totalPlays > 0 ? round(($completedPlays / $totalPlays) * 100, 2) : 0;

        return [
            'videoId' => $video->getVideoId(),
            'completionRate' => $completionRate,
            'totalPlays' => $totalPlays,
            'completedPlays' => $completedPlays,
        ];
    }

    /**
     * 清理过期的播放记录
     */
    public function cleanExpiredPlayRecords(int $daysToKeep = 90): int
    {
        $expireDate = new \DateTime("-{$daysToKeep} days");

        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(PlayRecord::class, 'pr')
            ->where('pr.playTime < :expireDate')
            ->setParameter('expireDate', $expireDate);

        return $qb->getQuery()->execute();
    }
}
