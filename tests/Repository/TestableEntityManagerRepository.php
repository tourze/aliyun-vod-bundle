<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;

/**
 * 测试EntityManager操作的Repository
 * @internal
 * @coversNothing
 * @phpstan-ignore-next-line
 */
class TestableEntityManagerRepository extends AliyunVodConfigRepository
{
    private ManagerRegistry $testRegistry;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
        $this->testRegistry = $registry;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $manager = $this->testRegistry->getManagerForClass(AliyunVodConfig::class);
        if (!$manager instanceof EntityManagerInterface) {
            throw new AliyunVodException('Expected EntityManagerInterface instance');
        }

        return $manager;
    }
}
