<?php

namespace Tourze\AliyunVodBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\AliyunVodBundle\Repository\VideoRepository;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

/**
 * 视频实体
 */
#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Table(name: 'aliyun_vod_video', options: ['comment' => '阿里云VOD视频表'])]
class Video implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: AliyunVodConfig::class)]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => '关联的阿里云配置'])]
    private AliyunVodConfig $config;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '阿里云视频ID'])]
    private string $videoId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '视频标题'])]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '视频描述'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '视频时长（秒）'])]
    private ?int $duration = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '文件大小（字节）'])]
    private ?int $size = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '视频状态'])]
    private string $status = 'Uploading';

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '封面URL'])]
    private ?string $coverUrl = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '播放URL'])]
    private ?string $playUrl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '视频标签'])]
    private ?string $tags = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否有效'])]
    private bool $valid = true;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    private \DateTimeImmutable $createdTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    private \DateTimeImmutable $updatedTime;

    public function __construct()
    {
        $this->createdTime = new \DateTimeImmutable();
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getConfig(): AliyunVodConfig
    {
        return $this->config;
    }

    public function setConfig(AliyunVodConfig $config): self
    {
        $this->config = $config;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getVideoId(): string
    {
        return $this->videoId;
    }

    public function setVideoId(string $videoId): self
    {
        $this->videoId = $videoId;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
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

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(?string $coverUrl): self
    {
        $this->coverUrl = $coverUrl;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getPlayUrl(): ?string
    {
        return $this->playUrl;
    }

    public function setPlayUrl(?string $playUrl): self
    {
        $this->playUrl = $playUrl;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;
        $this->updatedTime = new \DateTimeImmutable();
        return $this;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;
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
}
