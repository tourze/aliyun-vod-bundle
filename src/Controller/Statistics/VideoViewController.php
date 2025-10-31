<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 视频查看统计控制器
 */
final class VideoViewController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly VideoRepository $videoRepository,
    ) {
    }

    #[Route(path: '/admin/statistics/video-view', name: 'admin_statistics_video_view', methods: ['GET', 'POST', 'PATCH'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $videoId = $request->get('videoId');

            if (null === $videoId) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '缺少videoId参数',
                ], 400);
            }

            $video = $this->videoRepository->find((int) $videoId);
            if (null === $video) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '视频不存在',
                ], 404);
            }

            $completionRate = $this->statisticsService->getVideoCompletionRate($video);

            return new JsonResponse([
                'success' => true,
                'data' => $completionRate,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
