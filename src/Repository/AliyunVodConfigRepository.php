<?php

namespace Tourze\AliyunVodBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 阿里云VOD配置仓储
 */
class AliyunVodConfigRepository extends ServiceEntityRepository
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
        return $this->findOneBy([
            'isDefault' => true,
            'valid' => true,
        ]);
    }

    /**
     * 查找所有激活的配置
     */
    public function findActiveConfigs(): array
    {
        return $this->findBy(
            ['valid' => true],
            ['isDefault' => 'DESC', 'name' => 'ASC']
        );
    }
}
