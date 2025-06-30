<?php

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\GetTranscodeTaskRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\SubmitTranscodeJobsRequest;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;

/**
 * 视频转码服务
 */
class TranscodeService
{
    public function __construct(
        private readonly VodClientFactory $clientFactory,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    /**
     * 提交转码任务
     */
    public function submitTranscodeJobs(
        string $videoId,
        ?string $templateGroupId = null,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        // 如果没有指定模板组ID，使用配置中的默认模板组ID
        $templateGroupId = $templateGroupId ?? $config->getTemplateGroupId();

        $request = new SubmitTranscodeJobsRequest([
            'videoId' => $videoId,
            'templateGroupId' => $templateGroupId,
        ]);

        $response = $client->submitTranscodeJobs($request);

        return [
            'requestId' => $response->body->requestId,
            'transcodeJobs' => $response->body->transcodeJobs,
        ];
    }

    /**
     * 获取转码任务详情
     */
    public function getTranscodeTask(
        string $transcodeTaskId,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?? $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetTranscodeTaskRequest([
            'transcodeTaskId' => $transcodeTaskId,
        ]);

        $response = $client->getTranscodeTask($request);
        $task = $response->body->transcodeTask;

        return [
            'transcodeTaskId' => $task->transcodeTaskId,
            'videoId' => $task->videoId,
            'taskStatus' => $task->taskStatus,
            'creationTime' => $task->creationTime,
            'completeTime' => $task->completeTime,
            'transcodeJobInfoList' => $this->formatTranscodeJobInfoList($task->transcodeJobInfoList),
        ];
    }

    /**
     * 格式化转码任务信息列表
     */
    private function formatTranscodeJobInfoList(array $jobInfoList): array
    {
        $formattedList = [];
        foreach ($jobInfoList as $jobInfo) {
            $formattedList[] = [
                'transcodeJobId' => $jobInfo->transcodeJobId,
                'transcodeJobStatus' => $jobInfo->transcodeJobStatus,
                'transcodeProgress' => $jobInfo->transcodeProgress,
                'priority' => $jobInfo->priority,
                'creationTime' => $jobInfo->creationTime,
                'completeTime' => $jobInfo->completeTime,
                'errorCode' => $jobInfo->errorCode,
                'errorMessage' => $jobInfo->errorMessage,
                'inputFileUrl' => $jobInfo->inputFileUrl,
                'outputFile' => $this->formatOutputFile($jobInfo->outputFile ?? null),
            ];
        }
        return $formattedList;
    }

    /**
     * 格式化输出文件信息
     */
    private function formatOutputFile($outputFile): ?array
    {
        if (!$outputFile) {
            return null;
        }

        return [
            'outputFileUrl' => $outputFile->outputFileUrl,
            'width' => $outputFile->width,
            'height' => $outputFile->height,
            'bitrate' => $outputFile->bitrate,
            'fps' => $outputFile->fps,
            'duration' => $outputFile->duration,
            'filesize' => $outputFile->filesize,
            'format' => $outputFile->format,
        ];
    }

    /**
     * 检查转码任务状态
     */
    public function checkTranscodeStatus(
        string $transcodeTaskId,
        ?AliyunVodConfig $config = null
    ): string {
        $taskInfo = $this->getTranscodeTask($transcodeTaskId, $config);
        return $taskInfo['taskStatus'];
    }

    /**
     * 获取转码进度
     */
    public function getTranscodeProgress(
        string $transcodeTaskId,
        ?AliyunVodConfig $config = null
    ): array {
        $taskInfo = $this->getTranscodeTask($transcodeTaskId, $config);
        
        $totalJobs = count($taskInfo['transcodeJobInfoList']);
        $completedJobs = 0;
        $totalProgress = 0;

        foreach ($taskInfo['transcodeJobInfoList'] as $job) {
            if ($job['transcodeJobStatus'] === 'TranscodeSuccess') {
                $completedJobs++;
                $totalProgress += 100;
            } else {
                $totalProgress += $job['transcodeProgress'] ?? 0;
            }
        }

        $overallProgress = $totalJobs > 0 ? round($totalProgress / $totalJobs, 2) : 0;

        return [
            'transcodeTaskId' => $transcodeTaskId,
            'taskStatus' => $taskInfo['taskStatus'],
            'overallProgress' => $overallProgress,
            'completedJobs' => $completedJobs,
            'totalJobs' => $totalJobs,
            'jobDetails' => $taskInfo['transcodeJobInfoList'],
        ];
    }
}
