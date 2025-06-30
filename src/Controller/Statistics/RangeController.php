<?php

namespace Tourze\AliyunVodBundle\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 获取指定时间范围的统计数据控制器
 */
class RangeController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {
    }

    #[Route(path: '/admin/statistics/range', name: 'admin_statistics_range', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $startDate = new \DateTime($request->request->get('startDate'));
            $endDate = new \DateTime($request->request->get('endDate'));

            $stats = $this->statisticsService->getPlayStatsByDateRange($startDate, $endDate);

            return new JsonResponse([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}