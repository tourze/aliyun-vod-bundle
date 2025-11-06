<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 视频仓储
 *
 * @extends ServiceEntityRepository<Video>
 */
#[AsRepository(entityClass: Video::class)]
final class VideoRepository extends ServiceEntityRepository
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
     *
     * @return array<int, Video>
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
     *
     * @return array<int, Video>
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(
            ['status' => $status, 'valid' => true],
            ['createdTime' => 'DESC']
        );
    }

    public function save(Video $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Video $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
