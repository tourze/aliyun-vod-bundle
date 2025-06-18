<?php

namespace Tourze\AliyunVodBundle\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 获取热门视频排行控制器
 */
class PopularController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {
    }

    #[Route('/admin/statistics/popular', name: 'admin_statistics_popular', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->query->get('limit', 10);
            $popularVideos = $this->statisticsService->getPopularVideos($limit);

            return new JsonResponse([
                'success' => true,
                'data' => $popularVideos,
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}