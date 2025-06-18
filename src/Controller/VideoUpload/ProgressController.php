<?php

namespace Tourze\AliyunVodBundle\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\VideoUploadService;

/**
 * 上传进度查询控制器
 */
class ProgressController extends AbstractController
{
    public function __construct(
        private readonly VideoUploadService $uploadService
    ) {
    }

    #[Route('/admin/video-upload/progress/{videoId}', name: 'admin_video_upload_progress', methods: ['GET'])]
    public function __invoke(string $videoId): JsonResponse
    {
        try {
            $result = $this->uploadService->getUploadProgress($videoId);

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