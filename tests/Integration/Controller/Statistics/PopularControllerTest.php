<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\Statistics\PopularController;
use Tourze\AliyunVodBundle\Service\StatisticsService;

class PopularControllerTest extends WebTestCase
{
    private StatisticsService $statisticsService;
    private PopularController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = $this->createMock(StatisticsService::class);
        $this->controller = new PopularController($this->statisticsService);
    }

    public function testInvoke(): void
    {
        $request = new Request();
        $request->query->set('limit', '20');
        
        $this->statisticsService
            ->expects($this->once())
            ->method('getPopularVideos')
            ->with(20)
            ->willReturn([]);

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(200, $response->getStatusCode());
    }
}