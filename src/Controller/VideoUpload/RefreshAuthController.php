<?php

namespace Tourze\AliyunVodBundle\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoUploadService;

/**
 * 刷新上传凭证控制器
 */
class RefreshAuthController extends AbstractController
{
    public function __construct(
        private readonly VideoUploadService $uploadService,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    #[Route(path: '/admin/video-upload/refresh-auth', name: 'admin_video_upload_refresh_auth', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $videoId = $request->request->get('videoId');
            $configName = $request->request->get('config');

            if ($videoId === null) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '视频ID不能为空',
                ], 400);
            }

            $config = null;
            if ($configName !== null) {
                $config = $this->configService->getConfigByName($configName);
            }

            $result = $this->uploadService->refreshUploadAuth($videoId, $config);

            return new JsonResponse([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}