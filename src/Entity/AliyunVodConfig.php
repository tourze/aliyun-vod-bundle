<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;

/**
 * 阿里云VOD配置实体
 */
#[ORM\Entity(repositoryClass: AliyunVodConfigRepository::class)]
#[ORM\Table(name: 'aliyun_vod_config', options: ['comment' => '阿里云VOD配置表'])]
class AliyunVodConfig implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '配置名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '访问密钥ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $accessKeyId;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '访问密钥Secret，加密存储'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    private string $accessKeySecret;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => 'cn-shanghai', 'comment' => '地域ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $regionId = 'cn-shanghai';

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '转码模板组ID'])]
    #[Assert\Length(max: 100)]
    private ?string $templateGroupId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '存储地址'])]
    #[Assert\Length(max: 255)]
    private ?string $storageLocation = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '回调URL'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $callbackUrl = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否默认配置'])]
    #[Assert\NotNull]
    private bool $isDefault = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    #[Assert\NotNull]
    private bool $valid = true;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $createdTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $updatedTime;

    public function __construct()
    {
        $this->createdTime = new \DateTimeImmutable();
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getAccessKeyId(): string
    {
        return $this->accessKeyId;
    }

    public function setAccessKeyId(string $accessKeyId): void
    {
        $this->accessKeyId = $accessKeyId;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getAccessKeySecret(): string
    {
        return $this->accessKeySecret;
    }

    public function setAccessKeySecret(string $accessKeySecret): void
    {
        $this->accessKeySecret = $accessKeySecret;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getRegionId(): string
    {
        return $this->regionId;
    }

    public function setRegionId(string $regionId): void
    {
        $this->regionId = $regionId;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getTemplateGroupId(): ?string
    {
        return $this->templateGroupId;
    }

    public function setTemplateGroupId(?string $templateGroupId): void
    {
        $this->templateGroupId = $templateGroupId;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getStorageLocation(): ?string
    {
        return $this->storageLocation;
    }

    public function setStorageLocation(?string $storageLocation): void
    {
        $this->storageLocation = $storageLocation;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    public function setCallbackUrl(?string $callbackUrl): void
    {
        $this->callbackUrl = $callbackUrl;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
        $this->updatedTime = new \DateTimeImmutable();
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
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

    public function getCreateTime(): \DateTimeImmutable
    {
        return $this->createdTime;
    }

    public function getUpdateTime(): \DateTimeImmutable
    {
        return $this->updatedTime;
    }
}
