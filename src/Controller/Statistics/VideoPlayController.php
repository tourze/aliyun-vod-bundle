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
 * 视频播放统计控制器
 */
final class VideoPlayController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly VideoRepository $videoRepository,
    ) {
    }

    #[Route(path: '/admin/statistics/video-play/{id}', name: 'admin_statistics_video_play', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        try {
            $video = $this->videoRepository->find($id);
            if (null === $video) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '视频不存在',
                ], 404);
            }

            $playStats = $this->statisticsService->getVideoPlayStats($video);

            return new JsonResponse([
                'success' => true,
                'data' => $playStats,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
