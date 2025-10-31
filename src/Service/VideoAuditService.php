<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Service;

use AlibabaCloud\SDK\Vod\V20170321\Models\GetAIMediaAuditJobRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\GetMediaAuditResultRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\SubmitAIMediaAuditJobRequest;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;

/**
 * 视频审核服务
 */
#[Autoconfigure(public: true)]
readonly class VideoAuditService
{
    public function __construct(
        private VodClientFactory $clientFactory,
        private AliyunVodConfigService $configService,
    ) {
    }

    /**
     * 提交AI媒体审核任务
     *
     * @return array{requestId: string, jobId: string, mediaId: string}
     */
    public function submitAIMediaAuditJob(
        string $mediaId,
        ?string $templateId = null,
        ?AliyunVodConfig $config = null,
    ): array {
        $config ??= $this->configService->getDefaultConfig();
        if (null === $config) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $requestData = ['mediaId' => $mediaId];
        if (null !== $templateId) {
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
     *
     * @return array<string, mixed>
     */
    public function getAIMediaAuditJob(
        string $jobId,
        ?AliyunVodConfig $config = null,
    ): array {
        $config ??= $this->configService->getDefaultConfig();
        if (null === $config) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
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
                /* @phpstan-ignore-next-line */
                'code' => $response->body->mediaAuditJob->code ?? null,
                /* @phpstan-ignore-next-line */
                'message' => $response->body->mediaAuditJob->message ?? null,
                'creationTime' => $response->body->mediaAuditJob->creationTime,
                /* @phpstan-ignore-next-line */
                'completeTime' => $response->body->mediaAuditJob->completeTime ?? null,
                /* @phpstan-ignore-next-line */
                'data' => $response->body->mediaAuditJob->data ?? null,
            ],
        ];
    }

    /**
     * 获取媒体审核结果
     *
     * @return array<string, mixed>
     */
    public function getMediaAuditResult(
        string $mediaId,
        ?AliyunVodConfig $config = null,
    ): array {
        $config ??= $this->configService->getDefaultConfig();
        if (null === $config) {
            throw new AliyunVodException('未找到可用的阿里云VOD配置');
        }

        $client = $this->clientFactory->createClient($config);

        $request = new GetMediaAuditResultRequest([
            'mediaId' => $mediaId,
        ]);

        $response = $client->getMediaAuditResult($request);

        return [
            'requestId' => $response->body->requestId,
            'mediaAuditResult' => [
                /* @phpstan-ignore-next-line */
                'abnormalModules' => $response->body->mediaAuditResult->abnormalModules ?? '',
                /* @phpstan-ignore-next-line */
                'label' => $response->body->mediaAuditResult->label ?? '',
                /* @phpstan-ignore-next-line */
                'suggestion' => $response->body->mediaAuditResult->suggestion ?? '',
                /* @phpstan-ignore-next-line */
                'imageResult' => $this->formatImageResult($response->body->mediaAuditResult->imageResult ?? []),
                /* @phpstan-ignore-next-line */
                'textResult' => $this->formatTextResult($response->body->mediaAuditResult->textResult ?? []),
                /* @phpstan-ignore-next-line */
                'videoResult' => $this->formatVideoResult($response->body->mediaAuditResult->videoResult ?? null),
            ],
        ];
    }

    /**
     * 批量提交审核任务
     *
     * @param array<int, string> $mediaIds
     *
     * @return array<string, array<string, mixed>>
     */
    public function batchSubmitAuditJobs(
        array $mediaIds,
        ?string $templateId = null,
        ?AliyunVodConfig $config = null,
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
     *
     * @param array<int, mixed> $imageResult
     *
     * @return array<int, array{suggestion: string, label: string, type: string, url: string, result: mixed}>
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
     *
     * @param array<int, mixed> $textResult
     *
     * @return array<int, array{suggestion: string, label: string, score: string, content: string}>
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
     *
     * @return array{suggestion: string, label: string, terrorismResult: mixed, pornResult: mixed, adResult: mixed, liveResult: mixed, logoResult: mixed}|null
     */
    private function formatVideoResult(mixed $videoResult): ?array
    {
        if (null === $videoResult || false === $videoResult) {
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
     *
     * @param array<string, mixed> $auditResult
     */
    public function isAuditPassed(array $auditResult): bool
    {
        $suggestion = $auditResult['mediaAuditResult']['suggestion'] ?? '';

        return 'pass' === $suggestion;
    }

    /**
     * 判断审核是否需要人工复审
     *
     * @param array<string, mixed> $auditResult
     */
    public function needsManualReview(array $auditResult): bool
    {
        $suggestion = $auditResult['mediaAuditResult']['suggestion'] ?? '';

        return 'review' === $suggestion;
    }

    /**
     * 判断审核是否被拒绝
     *
     * @param array<string, mixed> $auditResult
     */
    public function isAuditRejected(array $auditResult): bool
    {
        $suggestion = $auditResult['mediaAuditResult']['suggestion'] ?? '';

        return 'block' === $suggestion;
    }
}
