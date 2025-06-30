<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\Statistics\CleanupController;
use Tourze\AliyunVodBundle\Service\StatisticsService;

class CleanupControllerTest extends WebTestCase
{
    private StatisticsService $statisticsService;
    private CleanupController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = $this->createMock(StatisticsService::class);
        $this->controller = new CleanupController($this->statisticsService);
    }

    public function testInvokeSuccess(): void
    {
        $request = new Request();
        $request->request->set('daysToKeep', '30');
        
        $this->statisticsService
            ->expects($this->once())
            ->method('cleanExpiredPlayRecords')
            ->with(30)
            ->willReturn(100);

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame(100, $data['deletedCount']);
    }

    public function testInvokeWithDefaultDaysToKeep(): void
    {
        $request = new Request();
        
        $this->statisticsService
            ->expects($this->once())
            ->method('cleanExpiredPlayRecords')
            ->with(90)
            ->willReturn(50);

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame(50, $data['deletedCount']);
    }

    public function testInvokeWithException(): void
    {
        $request = new Request();
        
        $this->statisticsService
            ->expects($this->once())
            ->method('cleanExpiredPlayRecords')
            ->willThrowException(new \Exception('Test error'));

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(500, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame('Test error', $data['message']);
    }
}