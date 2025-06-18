<?php

namespace Tourze\AliyunVodBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\AliyunVodBundle\Repository\TranscodeTaskRepository;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

/**
 * 转码任务实体
 */
#[ORM\Entity(repositoryClass: TranscodeTaskRepository::class)]
#[ORM\Table(name: 'aliyun_vod_transcode_task', options: ['comment' => '阿里云VOD转码任务表'])]
class TranscodeTask implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Video::class)]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => '关联的视频'])]
    private Video $video;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '阿里云任务ID'])]
    private string $taskId;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '转码模板ID'])]
    private ?string $templateId = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '任务状态'])]
    private string $status = 'Processing';

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '转码进度（0-100）'])]
    private int $progress = 0;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '错误代码'])]
    private ?string $errorCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '错误信息'])]
    private ?string $errorMessage = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    private \DateTimeImmutable $createdTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    private \DateTimeImmutable $updatedTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeImmutable $completedTime = null;

    public function __construct()
    {
        $this->createdTime = new \DateTimeImmutable();
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('转码任务 %s (%s)', $this->taskId, $this->status);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function setVideo(Video $video): self
    {
        $this->video = $video;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): self
    {
        $this->taskId = $taskId;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    public function setTemplateId(?string $templateId): self
    {
        $this->templateId = $templateId;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): self
    {
        $this->errorCode = $errorCode;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedTime(): \DateTimeImmutable
    {
        return $this->createdTime;
    }

    public function getUpdatedTime(): \DateTimeImmutable
    {
        return $this->updatedTime;
    }

    public function getCompletedTime(): ?\DateTimeImmutable
    {
        return $this->completedTime;
    }

    public function setCompletedTime(?\DateTimeImmutable $completedTime): self
    {
        $this->completedTime = $completedTime;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    /**
     * 标记任务完成
     */
    public function markAsCompleted(): self
    {
        $this->completedTime = new \DateTimeImmutable();
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    /**
     * 检查任务是否完成
     */
    public function isCompleted(): bool
    {
        return $this->completedTime !== null;
    }
}
