<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tourze\AliyunVodBundle\Controller\VideoUpload\IndexController;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;

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