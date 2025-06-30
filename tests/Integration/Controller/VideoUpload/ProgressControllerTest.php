<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\VideoUpload\ProgressController;
use Tourze\AliyunVodBundle\Service\VideoUploadService;

class ProgressControllerTest extends WebTestCase
{
    private VideoUploadService $videoUploadService;
    private ProgressController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->videoUploadService = $this->createMock(VideoUploadService::class);
        $this->controller = new ProgressController($this->videoUploadService);
    }

    public function testInvoke(): void
    {
        $this->videoUploadService
            ->expects($this->once())
            ->method('getUploadProgress')
            ->with('test-video-id')
            ->willReturn(['progress' => 50]);

        $response = $this->controller->__invoke('test-video-id');
        
        $this->assertSame(200, $response->getStatusCode());
    }
}