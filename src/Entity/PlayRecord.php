<?php

namespace Tourze\AliyunVodBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\AliyunVodBundle\Repository\PlayRecordRepository;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;

/**
 * 播放记录实体
 */
#[ORM\Entity(repositoryClass: PlayRecordRepository::class)]
#[ORM\Table(name: 'aliyun_vod_play_record', options: ['comment' => '阿里云VOD播放记录表'])]
class PlayRecord implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private readonly int $id;

    #[ORM\ManyToOne(targetEntity: Video::class)]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => '关联的视频'])]
    private Video $video;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '播放者IP地址'])]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '用户代理'])]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '来源页面'])]
    private ?string $referer = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '播放时长（秒）'])]
    private ?int $playDuration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '播放进度（秒）'])]
    private ?int $playPosition = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '播放质量'])]
    private ?string $playQuality = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '播放设备类型'])]
    private ?string $deviceType = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '播放器版本'])]
    private ?string $playerVersion = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '播放时间'])]
    private \DateTime $playTime;

    public function __construct()
    {
        $this->playTime = new \DateTime();
    }

    public function __toString(): string
    {
        $videoTitle = isset($this->video) ? $this->video->getTitle() : '未知视频';
        return sprintf('播放记录 - %s', $videoTitle);
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
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): self
    {
        $this->referer = $referer;
        return $this;
    }

    public function getPlayDuration(): ?int
    {
        return $this->playDuration;
    }

    public function setPlayDuration(?int $playDuration): self
    {
        $this->playDuration = $playDuration;
        return $this;
    }

    public function getPlayPosition(): ?int
    {
        return $this->playPosition;
    }

    public function setPlayPosition(?int $playPosition): self
    {
        $this->playPosition = $playPosition;
        return $this;
    }

    public function getPlayQuality(): ?string
    {
        return $this->playQuality;
    }

    public function setPlayQuality(?string $playQuality): self
    {
        $this->playQuality = $playQuality;
        return $this;
    }

    public function getDeviceType(): ?string
    {
        return $this->deviceType;
    }

    public function setDeviceType(?string $deviceType): self
    {
        $this->deviceType = $deviceType;
        return $this;
    }

    public function getPlayerVersion(): ?string
    {
        return $this->playerVersion;
    }

    public function setPlayerVersion(?string $playerVersion): self
    {
        $this->playerVersion = $playerVersion;
        return $this;
    }

    public function getPlayTime(): \DateTime
    {
        return $this->playTime;
    }

    public function setPlayTime(\DateTime $playTime): self
    {
        $this->playTime = $playTime;
        return $this;
    }
}
