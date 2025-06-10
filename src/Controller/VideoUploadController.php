<?php

namespace Tourze\AliyunVodBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;
use Tourze\AliyunVodBundle\Service\VideoUploadService;

/**
 * 视频上传控制器
 */
#[Route('/admin/video-upload', name: 'admin_video_upload_')]
class VideoUploadController extends AbstractController
{
    public function __construct(
        private readonly VideoUploadService $uploadService,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    /**
     * 视频上传页面
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $configs = $this->configService->getActiveConfigs();
        
        return $this->render('@AliyunVod/admin/upload/index.html.twig', [
            'configs' => $configs,
        ]);
    }

    /**
     * 获取上传凭证
     */
    #[Route('/auth', name: 'auth', methods: ['POST'])]
    public function getUploadAuth(Request $request): JsonResponse
    {
        try {
            $title = $request->request->get('title');
            $fileName = $request->request->get('fileName');
            $description = $request->request->get('description');
            $tags = $request->request->get('tags');
            $configName = $request->request->get('config');

            if (!$title || !$fileName) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '标题和文件名不能为空',
                ], 400);
            }

            $config = null;
            if ($configName) {
                $config = $this->configService->getConfigByName($configName);
            }

            $result = $this->uploadService->createUploadAuth(
                $title,
                $fileName,
                $description,
                $tags,
                $config
            );

            return new JsonResponse([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 刷新上传凭证
     */
    #[Route('/refresh-auth', name: 'refresh_auth', methods: ['POST'])]
    public function refreshUploadAuth(Request $request): JsonResponse
    {
        try {
            $videoId = $request->request->get('videoId');
            $configName = $request->request->get('config');

            if (!$videoId) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '视频ID不能为空',
                ], 400);
            }

            $config = null;
            if ($configName) {
                $config = $this->configService->getConfigByName($configName);
            }

            $result = $this->uploadService->refreshUploadAuth($videoId, $config);

            return new JsonResponse([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 上传进度查询
     */
    #[Route('/progress/{videoId}', name: 'progress', methods: ['GET'])]
    public function getUploadProgress(string $videoId): JsonResponse
    {
        try {
            $result = $this->uploadService->getUploadProgress($videoId);

            return new JsonResponse([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
} 