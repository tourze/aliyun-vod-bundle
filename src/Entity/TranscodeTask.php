<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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

    #[ORM\ManyToOne(targetEntity: Video::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => '关联的视频'])]
    #[Assert\NotNull]
    private Video $video;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '阿里云任务ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $taskId;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '转码模板ID'])]
    #[Assert\Length(max: 100)]
    private ?string $templateId = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '任务状态'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $status = 'Processing';

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '转码进度（0-100）'])]
    #[Assert\Range(min: 0, max: 100)]
    private int $progress = 0;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '错误代码'])]
    #[Assert\Length(max: 100)]
    private ?string $errorCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '错误信息'])]
    #[Assert\Length(max: 65535)]
    private ?string $errorMessage = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $createdTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $updatedTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    #[Assert\Type(type: \DateTimeImmutable::class)]
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

    public function setVideo(Video $video): void
    {
        $this->video = $video;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    public function setTemplateId(?string $templateId): void
    {
        $this->templateId = $templateId;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): void
    {
        $this->progress = $progress;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): void
    {
        $this->errorCode = $errorCode;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
        $this->updatedTime = new \DateTimeImmutable();
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

    public function setCompletedTime(?\DateTimeImmutable $completedTime): void
    {
        $this->completedTime = $completedTime;
        $this->updatedTime = new \DateTimeImmutable();
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
        return null !== $this->completedTime;
    }
}
