<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\Statistics\VideoDetailController;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\AliyunVodBundle\Service\StatisticsService;

class VideoDetailControllerTest extends WebTestCase
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