<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;

/**
 * 视频上传页面控制器
 */
final class IndexController extends AbstractController
{
    public function __construct(
        private readonly AliyunVodConfigService $configService,
    ) {
    }

    #[Route(path: '/admin/video-upload', name: 'admin_video_upload_index', methods: ['GET'])]
    public function __invoke(): Response
    {
        $configs = $this->configService->getActiveConfigs();

        return $this->render('@AliyunVod/admin/upload/index.html.twig', [
            'configs' => $configs,
        ]);
    }
}
