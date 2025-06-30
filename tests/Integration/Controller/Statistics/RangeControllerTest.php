<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\Statistics\RangeController;
use Tourze\AliyunVodBundle\Service\StatisticsService;

class RangeControllerTest extends WebTestCase
{
    private StatisticsService $statisticsService;
    private RangeController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = $this->createMock(StatisticsService::class);
        $this->controller = new RangeController($this->statisticsService);
    }

    public function testInvoke(): void
    {
        $request = new Request();
        $request->request->set('startDate', '2023-01-01');
        $request->request->set('endDate', '2023-12-31');
        
        $this->statisticsService
            ->expects($this->once())
            ->method('getPlayStatsByDateRange')
            ->willReturn([
                'totalPlays' => 100,
                'uniqueVideos' => 10,
                'deviceStats' => [],
                'dailyStats' => []
            ]);

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(200, $response->getStatusCode());
    }
}