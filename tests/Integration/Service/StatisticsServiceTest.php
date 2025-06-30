<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\AliyunVodBundle\Repository\PlayRecordRepository;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * @covers \Tourze\AliyunVodBundle\Service\StatisticsService
 */
class StatisticsServiceTest extends TestCase
{
    private StatisticsService $statisticsService;
    private MockObject|EntityManagerInterface $entityManagerMock;
    private MockObject|PlayRecordRepository $playRecordRepositoryMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->playRecordRepositoryMock = $this->createMock(PlayRecordRepository::class);
        
        $this->statisticsService = new StatisticsService(
            $this->entityManagerMock,
            $this->playRecordRepositoryMock
        );
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(StatisticsService::class, $this->statisticsService);
    }
}