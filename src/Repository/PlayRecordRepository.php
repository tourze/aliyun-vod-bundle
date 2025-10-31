<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 播放记录仓储
 *
 * @extends ServiceEntityRepository<PlayRecord>
 */
#[AsRepository(entityClass: PlayRecord::class)]
class PlayRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayRecord::class);
    }

    /**
     * 根据视频查找播放记录
     *
     * @return array<int, PlayRecord>
     */
    public function findByVideo(Video $video): array
    {
        return $this->findBy(
            ['video' => $video],
            ['playTime' => 'DESC']
        );
    }

    /**
     * 统计视频播放次数
     */
    public function countByVideo(Video $video): int
    {
        return $this->count(['video' => $video]);
    }

    /**
     * 根据IP地址查找播放记录
     *
     * @return array<int, PlayRecord>
     */
    public function findByIpAddress(string $ipAddress): array
    {
        return $this->findBy(
            ['ipAddress' => $ipAddress],
            ['playTime' => 'DESC']
        );
    }

    /**
     * 获取热门视频（按播放次数排序）
     *
     * @return array<int, array{id: int, title: string, playCount: int}>
     */
    public function getPopularVideos(int $limit = 10): array
    {
        return $this->createQueryBuilder('pr')
            ->select('v.id, v.title, COUNT(pr.id) as playCount')
            ->join('pr.video', 'v')
            ->where('v.valid = :valid')
            ->setParameter('valid', true)
            ->groupBy('v.id')
            ->orderBy('playCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取指定时间范围内的播放记录
     *
     * @return array<int, PlayRecord>
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('pr')
            ->where('pr.playTime >= :startDate')
            ->andWhere('pr.playTime <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('pr.playTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(PlayRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PlayRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
