<?php

namespace Tourze\AliyunVodBundle\Controller\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\AliyunVodBundle\Service\StatisticsService;

/**
 * 视频播放统计详情控制器
 */
class VideoDetailController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly VideoRepository $videoRepository
    ) {
    }

    #[Route(path: '/admin/statistics/video/{id}', name: 'admin_statistics_video_detail')]
    public function __invoke(int $id): Response
    {
        $video = $this->videoRepository->find($id);
        if ($video === null) {
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
}