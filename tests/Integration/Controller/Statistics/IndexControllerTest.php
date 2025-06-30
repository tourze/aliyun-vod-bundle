<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tourze\AliyunVodBundle\Controller\Statistics\IndexController;
use Tourze\AliyunVodBundle\Service\StatisticsService;

class IndexControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $this->expectNotToPerformAssertions();
    }
}