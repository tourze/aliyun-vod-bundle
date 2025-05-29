<?php

namespace Tourze\AliyunVodBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 视频仓储
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[] findAll()
 * @method Video[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    /**
     * 根据阿里云视频ID查找视频
     */
    public function findByVideoId(string $videoId): ?Video
    {
        return $this->findOneBy(['videoId' => $videoId]);
    }

    /**
     * 查找有效的视频
     */
    public function findValidVideos(): array
    {
        return $this->findBy(
            ['valid' => true],
            ['createdTime' => 'DESC']
        );
    }

    /**
     * 根据状态查找视频
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(
            ['status' => $status, 'valid' => true],
            ['createdTime' => 'DESC']
        );
    }
}
