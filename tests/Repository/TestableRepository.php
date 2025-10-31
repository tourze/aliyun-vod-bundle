<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Repository;

use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;

/**
 * 简化的测试用Repository，消除匿名类复杂性
 * @internal
 * @coversNothing
 * @phpstan-ignore-next-line
 */
class TestableRepository extends AliyunVodConfigRepository
{
    private ?AliyunVodConfig $findOneByResult = null;

    /** @var array<AliyunVodConfig> */
    private array $findByResult = [];

    private int $countResult = 0;

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

    public function findOneBy(array $criteria, ?array $orderBy = null): ?AliyunVodConfig
    {
        return $this->findOneByResult;
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        if (null !== $limit && null !== $offset) {
            return array_values(array_slice($this->findByResult, $offset, $limit));
        }

        return array_values($this->findByResult);
    }

    public function count(array $criteria = []): int
    {
        /** @var int<0, max> */
        return $this->countResult;
    }
}
