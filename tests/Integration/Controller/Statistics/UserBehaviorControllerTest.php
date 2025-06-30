<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\Statistics\UserBehaviorController;
use Tourze\AliyunVodBundle\Service\StatisticsService;

class UserBehaviorControllerTest extends WebTestCase
{
    private StatisticsService $statisticsService;
    private UserBehaviorController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = $this->createMock(StatisticsService::class);
        $this->controller = new UserBehaviorController($this->statisticsService);
    }

    public function testInvoke(): void
    {
        $request = new Request();
        $request->request->set('ipAddress', '192.168.1.1');
        
        $this->statisticsService
            ->expects($this->once())
            ->method('getUserPlayBehavior')
            ->with('192.168.1.1')
            ->willReturn([]);

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(200, $response->getStatusCode());
    }
}