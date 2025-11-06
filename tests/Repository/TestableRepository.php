<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;

/**
 * 简化的测试用Repository，使用组合而非继承以避免继承final类
 * 这不是测试类，而是测试辅助类，用于在单元测试中提供可控的Repository行为
 * @internal
 * @coversNothing
 * @phpstan-ignore-next-line phpat.repositoryTestsMustExtendsAbstractRepositoryTestCase
 */
class TestableRepository
{
    private AliyunVodConfigRepository $innerRepository;
    private ?AliyunVodConfig $findOneByResult = null;

    /** @var array<AliyunVodConfig> */
    private array $findByResult = [];

    private int $countResult = 0;

    public function __construct(ManagerRegistry $registry)
    {
        $this->innerRepository = new AliyunVodConfigRepository($registry);
    }

    public function setFindOneByResult(?AliyunVodConfig $result): void
    {
        $this->findOneByResult = $result;
    }

    /** @param array<AliyunVodConfig> $result */
    public function setFindByResult(array $result): void
    {
        $this->findByResult = $result;
    }

    public function setCountResult(int $result): void
    {
        $this->countResult = $result;
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?AliyunVodConfig
    {
        return $this->findOneByResult;
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @return array<AliyunVodConfig>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        if (null !== $limit && null !== $offset) {
            return array_values(array_slice($this->findByResult, $offset, $limit));
        }

        return array_values($this->findByResult);
    }

    /** @param array<string, mixed> $criteria */
    public function count(array $criteria = []): int
    {
        /** @var int<0, max> */
        return $this->countResult;
    }

    public function findDefaultConfig(): ?AliyunVodConfig
    {
        return $this->innerRepository->findDefaultConfig();
    }

    /** @return array<int, AliyunVodConfig> */
    public function findActiveConfigs(): array
    {
        return $this->innerRepository->findActiveConfigs();
    }
}
