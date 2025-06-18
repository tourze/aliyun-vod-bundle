<?php

namespace Tourze\AliyunVodBundle\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 获取用户播放行为分析控制器
 */
class UserBehaviorController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {
    }

    #[Route('/admin/statistics/user-behavior', name: 'admin_statistics_user_behavior', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $ipAddress = $request->request->get('ipAddress');

            if (!$ipAddress) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'IP地址不能为空',
                ], 400);
            }

            $behavior = $this->statisticsService->getUserPlayBehavior($ipAddress);

            return new JsonResponse([
                'success' => true,
                'data' => $behavior,
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}