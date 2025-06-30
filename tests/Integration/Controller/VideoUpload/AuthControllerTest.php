<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\AliyunVodBundle\Controller\VideoUpload\AuthController;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoUploadService;

class AuthControllerTest extends WebTestCase
{
    private VideoUploadService $videoUploadService;
    private AliyunVodConfigService $configService;
    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->videoUploadService = $this->createMock(VideoUploadService::class);
        $this->configService = $this->createMock(AliyunVodConfigService::class);
        $this->controller = new AuthController($this->videoUploadService, $this->configService);
    }

    public function testInvoke(): void
    {
        $request = new Request();
        $request->request->set('title', 'Test Video');
        $request->request->set('fileName', 'test.mp4');
        
        $this->videoUploadService
            ->expects($this->once())
            ->method('createUploadAuth')
            ->willReturn([
                'uploadAuth' => 'test-auth',
                'uploadAddress' => 'test-address',
                'videoId' => 'test-video-id'
            ]);

        $response = $this->controller->__invoke($request);
        
        $this->assertSame(200, $response->getStatusCode());
    }
}