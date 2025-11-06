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
final class PlayRecordRepository extends ServiceEntityRepository
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
        $query = $this->createQueryBuilder('pr')
            ->select('v.id AS id')
            ->addSelect('v.title AS title')
            ->addSelect('COUNT(pr.id) AS playCount')
            ->join('pr.video', 'v')
            ->where('v.valid = :valid')
            ->setParameter('valid', true)
            ->groupBy('v.id')
            ->orderBy('playCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        /** @var list<array{id: string|int, title: string, playCount: string|int}> $rawResult 原始聚合结果 */
        $rawResult = $query->getArrayResult();

        return array_values(array_map(
            static function (array $row): array {
                return [
                    'id' => (int) $row['id'],
                    'title' => $row['title'],
                    'playCount' => (int) $row['playCount'],
                ];
            },
            $rawResult
        ));
    }

    /**
     * 获取指定时间范围内的播放记录
     *
     * @return array<int, PlayRecord>
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $query = $this->createQueryBuilder('pr')
            ->where('pr.playTime >= :startDate')
            ->andWhere('pr.playTime <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('pr.playTime', 'DESC')
            ->getQuery();

        /** @var list<PlayRecord> $records 查询结果为播放记录列表 */
        $records = $query->getResult();

        return $records;
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
