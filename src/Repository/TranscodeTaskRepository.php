<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 转码任务仓储
 *
 * @extends ServiceEntityRepository<TranscodeTask>
 */
#[AsRepository(entityClass: TranscodeTask::class)]
final class TranscodeTaskRepository extends ServiceEntityRepository
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
     *
     * @return array<int, TranscodeTask>
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
     *
     * @return array<int, TranscodeTask>
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
     *
     * @return array<int, TranscodeTask>
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
     *
     * @return array<int, TranscodeTask>
     */
    public function findCompletedTasks(): array
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.completedTime IS NOT NULL')
            ->orderBy('t.completedTime', 'DESC')
            ->getQuery();

        /** @var list<TranscodeTask> $tasks 查询结果为转码任务列表 */
        $tasks = $query->getResult();

        return $tasks;
    }

    public function save(TranscodeTask $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TranscodeTask $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
