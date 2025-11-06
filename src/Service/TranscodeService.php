<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\GetTranscodeTaskRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\SubmitTranscodeJobsRequest;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;

/**
 * 视频转码服务
 */
#[Autoconfigure(public: true)]
readonly class TranscodeService
{
    public function __construct(
        private VodClientFactory $clientFactory,
        private AliyunVodConfigService $configService,
    ) {
    }

    /**
     * 提交转码任务
     *
     * @return array<string, mixed>
     */
    public function submitTranscodeJobs(
        string $videoId,
        ?string $templateGroupId = null,
        ?AliyunVodConfig $config = null,
    ): array {
        $config ??= $this->configService->getDefaultConfig();
        if (null === $config) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        // 如果没有指定模板组ID，使用配置中的默认模板组ID
        $templateGroupId ??= $config->getTemplateGroupId();

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
     *
     * @return array{
     *     transcodeTaskId: string,
     *     videoId: string,
     *     taskStatus: string,
     *     creationTime: string,
     *     completeTime: string,
     *     transcodeJobInfoList: list<array{
     *         transcodeJobId: string,
     *         transcodeJobStatus: string,
     *         transcodeProgress: int,
     *         priority: string,
     *         creationTime: string,
     *         completeTime: string,
     *         errorCode: string,
     *         errorMessage: string,
     *         inputFileUrl: string,
     *         outputFile: array<string, mixed>|null
     *     }>
     * }
     */
    public function getTranscodeTask(
        string $transcodeTaskId,
        ?AliyunVodConfig $config = null,
    ): array {
        $config ??= $this->configService->getDefaultConfig();
        if (null === $config) {
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
     *
     * @param array<mixed> $jobInfoList
     *
     * @return list<array{
     *     transcodeJobId: string,
     *     transcodeJobStatus: string,
     *     transcodeProgress: int,
     *     priority: string,
     *     creationTime: string,
     *     completeTime: string,
     *     errorCode: string,
     *     errorMessage: string,
     *     inputFileUrl: string,
     *     outputFile: array<string, mixed>|null
     * }>
     */
    private function formatTranscodeJobInfoList(array $jobInfoList): array
    {
        $formattedList = [];
        foreach ($jobInfoList as $jobInfo) {
            // 防御性检查：确保 $jobInfo 是对象
            if (!is_object($jobInfo)) {
                continue;
            }

            /** @var object{transcodeJobId: string, transcodeJobStatus: string, transcodeProgress: int, priority: string, creationTime: string, completeTime: string, errorCode: string, errorMessage: string, inputFileUrl: string, outputFile?: mixed} $jobInfo */
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
     *
     * @return array{
     *     outputFileUrl: string,
     *     width: string,
     *     height: string,
     *     bitrate: string,
     *     fps: string,
     *     duration: string,
     *     filesize: int,
     *     format: string
     * }|null
     */
    private function formatOutputFile(mixed $outputFile): ?array
    {
        if (null === $outputFile || false === $outputFile || !is_object($outputFile)) {
            return null;
        }

        /** @var object{outputFileUrl: string, width: string, height: string, bitrate: string, fps: string, duration: string, filesize: int, format: string} $outputFile */
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
        ?AliyunVodConfig $config = null,
    ): string {
        $taskInfo = $this->getTranscodeTask($transcodeTaskId, $config);

        return $taskInfo['taskStatus'];
    }

    /**
     * 获取转码进度
     *
     * @return array{
     *     transcodeTaskId: string,
     *     taskStatus: string,
     *     overallProgress: float|int,
     *     completedJobs: int,
     *     totalJobs: int,
     *     jobDetails: list<array{
     *         transcodeJobId: string,
     *         transcodeJobStatus: string,
     *         transcodeProgress: int,
     *         priority: string,
     *         creationTime: string,
     *         completeTime: string,
     *         errorCode: string,
     *         errorMessage: string,
     *         inputFileUrl: string,
     *         outputFile: array<string, mixed>|null
     *     }>
     * }
     */
    public function getTranscodeProgress(
        string $transcodeTaskId,
        ?AliyunVodConfig $config = null,
    ): array {
        $taskInfo = $this->getTranscodeTask($transcodeTaskId, $config);

        /** @var list<array{transcodeJobId: string, transcodeJobStatus: string, transcodeProgress: int, priority: string, creationTime: string, completeTime: string, errorCode: string, errorMessage: string, inputFileUrl: string, outputFile: array<string, mixed>|null}> $jobInfoList */
        $jobInfoList = $taskInfo['transcodeJobInfoList'];

        $totalJobs = count($jobInfoList);
        $completedJobs = 0;
        $totalProgress = 0;

        foreach ($jobInfoList as $job) {
            if ('TranscodeSuccess' === $job['transcodeJobStatus']) {
                ++$completedJobs;
                $totalProgress += 100;
            } else {
                $totalProgress += $job['transcodeProgress'];
            }
        }

        $overallProgress = $totalJobs > 0 ? round($totalProgress / $totalJobs, 2) : 0;

        return [
            'transcodeTaskId' => $transcodeTaskId,
            'taskStatus' => $taskInfo['taskStatus'],
            'overallProgress' => $overallProgress,
            'completedJobs' => $completedJobs,
            'totalJobs' => $totalJobs,
            'jobDetails' => $jobInfoList,
        ];
    }
}
