<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Controller\VideoUpload;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoUploadService;

/**
 * 获取上传凭证控制器
 */
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly VideoUploadService $uploadService,
        private readonly AliyunVodConfigService $configService,
    ) {
    }

    #[Route(path: '/admin/video-upload/auth', name: 'admin_video_upload_auth', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $title = $request->request->get('title');
            $fileName = $request->request->get('fileName');
            $description = $request->request->get('description');
            $tags = $request->request->get('tags');
            $configName = $request->request->get('config');

            if (!is_string($title) || !is_string($fileName)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '标题和文件名不能为空',
                ], 400);
            }

            $config = null;
            if (is_string($configName)) {
                $config = $this->configService->getConfigByName($configName);
            }

            $result = $this->uploadService->createUploadAuth(
                $title,
                $fileName,
                is_string($description) ? $description : null,
                is_string($tags) ? $tags : null,
                $config
            );

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
