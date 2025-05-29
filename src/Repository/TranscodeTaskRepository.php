<?php

namespace Tourze\AliyunVodBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 转码任务仓储
 *
 * @method TranscodeTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranscodeTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranscodeTask[] findAll()
 * @method TranscodeTask[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranscodeTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranscodeTask::class);
    }

    /**
     * 根据任务ID查找转码任务
     */
    public function findByTaskId(string $taskId): ?TranscodeTask
    {
        return $this->findOneBy(['taskId' => $taskId]);
    }

    /**
     * 根据视频查找转码任务
     */
    public function findByVideo(Video $video): array
    {
        return $this->findBy(
            ['video' => $video],
            ['createdTime' => 'DESC']
        );
    }

    /**
     * 根据状态查找转码任务
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(
            ['status' => $status],
            ['createdTime' => 'DESC']
        );
    }

    /**
     * 查找进行中的转码任务
     */
    public function findProcessingTasks(): array
    {
        return $this->findBy(
            ['status' => 'Processing'],
            ['createdTime' => 'ASC']
        );
    }

    /**
     * 查找已完成的转码任务
     */
    public function findCompletedTasks(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.completedTime IS NOT NULL')
            ->orderBy('t.completedTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
