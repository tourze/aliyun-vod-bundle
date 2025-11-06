<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 阿里云VOD配置仓储
 *
 * @extends ServiceEntityRepository<AliyunVodConfig>
 */
#[AsRepository(entityClass: AliyunVodConfig::class)]
final class AliyunVodConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AliyunVodConfig::class);
    }

    /**
     * 查找默认配置
     */
    public function findDefaultConfig(): ?AliyunVodConfig
    {
        /* @var AliyunVodConfig|null */
        return $this->findOneBy([
            'isDefault' => true,
            'valid' => true,
        ]);
    }

    /**
     * 查找所有激活的配置
     *
     * @return array<int, AliyunVodConfig>
     */
    public function findActiveConfigs(): array
    {
        return $this->findBy(
            ['valid' => true],
            ['isDefault' => 'DESC', 'name' => 'ASC']
        );
    }

    public function save(AliyunVodConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AliyunVodConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
