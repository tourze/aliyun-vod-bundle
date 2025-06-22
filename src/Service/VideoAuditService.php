<?php

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\GetAIMediaAuditJobRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\GetMediaAuditResultRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\SubmitAIMediaAuditJobRequest;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 视频审核服务
 */
class VideoAuditService
{
    public function __construct(
        private readonly VodClientFactory $clientFactory,
        private readonly AliyunVodConfigService $configService
    ) {
    }

    /**
     * 提交AI媒体审核任务
     */
    public function submitAIMediaAuditJob(
        string $mediaId,
        ?string $templateId = null,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?: $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $requestData = ['mediaId' => $mediaId];
        if ($templateId !== null) {
            $requestData['templateId'] = $templateId;
        }

        $request = new SubmitAIMediaAuditJobRequest($requestData);
        $response = $client->submitAIMediaAuditJob($request);

        return [
            'requestId' => $response->body->requestId,
            'jobId' => $response->body->jobId,
            'mediaId' => $response->body->mediaId,
        ];
    }

    /**
     * 获取AI媒体审核任务详情
     */
    public function getAIMediaAuditJob(
        string $jobId,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?: $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetAIMediaAuditJobRequest([
            'jobId' => $jobId,
        ]);

        $response = $client->getAIMediaAuditJob($request);

        return [
            'requestId' => $response->body->requestId,
            'mediaAuditJob' => [
                'jobId' => $response->body->mediaAuditJob->jobId,
                'mediaId' => $response->body->mediaAuditJob->mediaId,
                'type' => $response->body->mediaAuditJob->type,
                'status' => $response->body->mediaAuditJob->status,
                'code' => property_exists($response->body->mediaAuditJob, 'code') ? $response->body->mediaAuditJob->code : null,
                'message' => property_exists($response->body->mediaAuditJob, 'message') ? $response->body->mediaAuditJob->message : null,
                'creationTime' => $response->body->mediaAuditJob->creationTime,
                'completeTime' => property_exists($response->body->mediaAuditJob, 'completeTime') ? $response->body->mediaAuditJob->completeTime : null,
                'data' => property_exists($response->body->mediaAuditJob, 'data') ? $response->body->mediaAuditJob->data : null,
            ],
        ];
    }

    /**
     * 获取媒体审核结果
     */
    public function getMediaAuditResult(
        string $mediaId,
        ?AliyunVodConfig $config = null
    ): array {
        $config = $config ?: $this->configService->getDefaultConfig();
        if ($config === null) {
            throw new \RuntimeException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetMediaAuditResultRequest([
            'mediaId' => $mediaId,
        ]);

        $response = $client->getMediaAuditResult($request);

        return [
            'requestId' => $response->body->requestId,
            'mediaAuditResult' => [
                'abnormalModules' => property_exists($response->body->mediaAuditResult, 'abnormalModules') ? $response->body->mediaAuditResult->abnormalModules : '',
                'label' => property_exists($response->body->mediaAuditResult, 'label') ? $response->body->mediaAuditResult->label : '',
                'suggestion' => property_exists($response->body->mediaAuditResult, 'suggestion') ? $response->body->mediaAuditResult->suggestion : '',
                'imageResult' => $this->formatImageResult(property_exists($response->body->mediaAuditResult, 'imageResult') ? $response->body->mediaAuditResult->imageResult : []),
                'textResult' => $this->formatTextResult(property_exists($response->body->mediaAuditResult, 'textResult') ? $response->body->mediaAuditResult->textResult : []),
                'videoResult' => $this->formatVideoResult(property_exists($response->body->mediaAuditResult, 'videoResult') ? $response->body->mediaAuditResult->videoResult : null),
            ],
        ];
    }

    /**
     * 批量提交审核任务
     */
    public function batchSubmitAuditJobs(
        array $mediaIds,
        ?string $templateId = null,
        ?AliyunVodConfig $config = null
    ): array {
        $results = [];
        foreach ($mediaIds as $mediaId) {
            try {
                $results[$mediaId] = $this->submitAIMediaAuditJob($mediaId, $templateId, $config);
            } catch (\Throwable $e) {
                $results[$mediaId] = [
                    'error' => $e->getMessage(),
                    'mediaId' => $mediaId,
                ];
            }
        }
        return $results;
    }

    /**
     * 检查审核状态
     */
    public function checkAuditStatus(string $jobId, ?AliyunVodConfig $config = null): string
    {
        $jobInfo = $this->getAIMediaAuditJob($jobId, $config);
        return $jobInfo['mediaAuditJob']['status'];
    }

    /**
     * 格式化图片审核结果
     */
    private function formatImageResult(array $imageResult): array
    {
        $formatted = [];
        foreach ($imageResult as $result) {
            $formatted[] = [
                'suggestion' => $result->suggestion ?? '',
                'label' => $result->label ?? '',
                'type' => $result->type ?? '',
                'url' => $result->url ?? '',
                'result' => $result->result ?? [],
            ];
        }
        return $formatted;
    }

    /**
     * 格式化文本审核结果
     */
    private function formatTextResult(array $textResult): array
    {
        $formatted = [];
        foreach ($textResult as $result) {
            $formatted[] = [
                'suggestion' => $result->suggestion ?? '',
                'label' => $result->label ?? '',
                'score' => $result->score ?? '',
                'scene' => $result->scene ?? '',
                'type' => $result->type ?? '',
                'content' => $result->content ?? '',
            ];
        }
        return $formatted;
    }

    /**
     * 格式化视频审核结果
     */
    private function formatVideoResult($videoResult): ?array
    {
        if (!$videoResult) {
            return null;
        }

        return [
            'suggestion' => $videoResult->suggestion ?? '',
            'label' => $videoResult->label ?? '',
            'terrorismResult' => $videoResult->terrorismResult ?? null,
            'pornResult' => $videoResult->pornResult ?? null,
            'adResult' => $videoResult->adResult ?? null,
            'liveResult' => $videoResult->liveResult ?? null,
            'logoResult' => $videoResult->logoResult ?? null,
        ];
    }

    /**
     * 判断审核是否通过
     */
    public function isAuditPassed(array $auditResult): bool
    {
        $suggestion = $auditResult['mediaAuditResult']['suggestion'] ?? '';
        return $suggestion === 'pass';
    }

    /**
     * 判断审核是否需要人工复审
     */
    public function needsManualReview(array $auditResult): bool
    {
        $suggestion = $auditResult['mediaAuditResult']['suggestion'] ?? '';
        return $suggestion === 'review';
    }

    /**
     * 判断审核是否被拒绝
     */
    public function isAuditRejected(array $auditResult): bool
    {
        $suggestion = $auditResult['mediaAuditResult']['suggestion'] ?? '';
        return $suggestion === 'block';
    }
} 