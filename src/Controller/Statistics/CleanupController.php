<?php

namespace Tourze\AliyunVodBundle\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 清理过期播放记录控制器
 */
class CleanupController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {
    }

    #[Route('/admin/statistics/cleanup', name: 'admin_statistics_cleanup', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $daysToKeep = (int) $request->request->get('daysToKeep', 90);
            $deletedCount = $this->statisticsService->cleanExpiredPlayRecords($daysToKeep);

            return new JsonResponse([
                'success' => true,
                'message' => "已清理 {$deletedCount} 条过期记录",
                'deletedCount' => $deletedCount,
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}