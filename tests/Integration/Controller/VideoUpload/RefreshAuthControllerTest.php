<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\VideoUpload\RefreshAuthController;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoUploadService;

class RefreshAuthControllerTest extends WebTestCase
{
    private VideoUploadService $videoUploadService;
    private AliyunVodConfigService $configService;
    private RefreshAuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->videoUploadService = $this->createMock(VideoUploadService::class);
        $this->configService = $this->createMock(AliyunVodConfigService::class);
        $this->controller = new RefreshAuthController($this->videoUploadService, $this->configService);
    }

    public function testInvoke(): void
    {
        $request = new Request();
        $request->request->set('videoId', 'test-video-id');
        
        $this->videoUploadService
            ->expects($this->once())
            ->method('refreshUploadAuth')
            ->with('test-video-id', null)
            ->willReturn([
                'uploadAuth' => 'new-auth',
                'uploadAddress' => 'new-address'
            ]);

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(200, $response->getStatusCode());
    }
}