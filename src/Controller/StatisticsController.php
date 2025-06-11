<?php

namespace Tourze\AliyunVodBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 播放统计控制器
 */
#[Route('/admin/statistics', name: 'admin_statistics_')]
class StatisticsController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly VideoRepository $videoRepository
    ) {
    }

    /**
     * 统计报表首页
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $realTimeStats = $this->statisticsService->getRealTimeStats();
        $popularVideos = $this->statisticsService->getPopularVideos(10);

        return $this->render('@AliyunVod/admin/statistics/index.html.twig', [
            'realTimeStats' => $realTimeStats,
            'popularVideos' => $popularVideos,
        ]);
    }

    /**
     * 视频播放统计详情
     */
    #[Route('/video/{id}', name: 'video_detail')]
    public function videoDetail(int $id): Response
    {
        $video = $this->videoRepository->find($id);
        if (!$video) {
            throw $this->createNotFoundException('视频不存在');
        }

        $playStats = $this->statisticsService->getVideoPlayStats($video);
        $completionRate = $this->statisticsService->getVideoCompletionRate($video);

        return $this->render('@AliyunVod/admin/statistics/video_detail.html.twig', [
            'video' => $video,
            'playStats' => $playStats,
            'completionRate' => $completionRate,
        ]);
    }

    /**
     * 获取指定时间范围的统计数据
     */
    #[Route('/range', name: 'range', methods: ['POST'])]
    public function getStatsByRange(Request $request): JsonResponse
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

    /**
     * 获取用户播放行为分析
     */
    #[Route('/user-behavior', name: 'user_behavior', methods: ['POST'])]
    public function getUserBehavior(Request $request): JsonResponse
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

    /**
     * 获取热门视频排行
     */
    #[Route('/popular', name: 'popular', methods: ['GET'])]
    public function getPopularVideos(Request $request): JsonResponse
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

    /**
     * 清理过期播放记录
     */
    #[Route('/cleanup', name: 'cleanup', methods: ['POST'])]
    public function cleanupExpiredRecords(Request $request): JsonResponse
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