<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Video::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => '关联的视频'])]
    #[Assert\NotNull]
    private Video $video;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '播放者IP地址'])]
    #[Assert\Length(max: 45)]
    #[Assert\Ip]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '用户代理'])]
    #[Assert\Length(max: 500)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '来源页面'])]
    #[Assert\Length(max: 255)]
    private ?string $referer = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '播放时长（秒）'])]
    #[Assert\Range(min: 0, max: 86400)]
    private ?int $playDuration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '播放进度（秒）'])]
    #[Assert\Range(min: 0)]
    private ?int $playPosition = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '播放质量'])]
    #[Assert\Length(max: 50)]
    private ?string $playQuality = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '播放设备类型'])]
    #[Assert\Length(max: 100)]
    private ?string $deviceType = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '播放器版本'])]
    #[Assert\Length(max: 100)]
    private ?string $playerVersion = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '播放时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $playTime;

    public function __construct()
    {
        $this->playTime = new \DateTimeImmutable();
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

    public function setVideo(Video $video): void
    {
        $this->video = $video;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): void
    {
        $this->referer = $referer;
    }

    public function getPlayDuration(): ?int
    {
        return $this->playDuration;
    }

    public function setPlayDuration(?int $playDuration): void
    {
        $this->playDuration = $playDuration;
    }

    public function getPlayPosition(): ?int
    {
        return $this->playPosition;
    }

    public function setPlayPosition(?int $playPosition): void
    {
        $this->playPosition = $playPosition;
    }

    public function getPlayQuality(): ?string
    {
        return $this->playQuality;
    }

    public function setPlayQuality(?string $playQuality): void
    {
        $this->playQuality = $playQuality;
    }

    public function getDeviceType(): ?string
    {
        return $this->deviceType;
    }

    public function setDeviceType(?string $deviceType): void
    {
        $this->deviceType = $deviceType;
    }

    public function getPlayerVersion(): ?string
    {
        return $this->playerVersion;
    }

    public function setPlayerVersion(?string $playerVersion): void
    {
        $this->playerVersion = $playerVersion;
    }

    public function getPlayTime(): \DateTimeImmutable
    {
        return $this->playTime;
    }

    public function setPlayTime(\DateTimeImmutable $playTime): void
    {
        $this->playTime = $playTime;
    }
}
