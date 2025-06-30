<?php

namespace Tourze\AliyunVodBundle\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 统计报表首页控制器
 */
class IndexController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {
    }

    #[Route(path: '/admin/statistics', name: 'admin_statistics_index')]
    public function __invoke(): Response
    {
        $realTimeStats = $this->statisticsService->getRealTimeStats();
        $popularVideos = $this->statisticsService->getPopularVideos(10);

        return $this->render('@AliyunVod/admin/statistics/index.html.twig', [
            'realTimeStats' => $realTimeStats,
            'popularVideos' => $popularVideos,
        ]);
    }
}