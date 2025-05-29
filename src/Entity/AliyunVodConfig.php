<?php

namespace Tourze\AliyunVodBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
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
    #[ORM\Column(type: Types::INTEGER)]
    private readonly int $id;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '配置名称'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '访问密钥ID'])]
    private string $accessKeyId;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '访问密钥Secret，加密存储'])]
    private string $accessKeySecret;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => 'cn-shanghai', 'comment' => '地域ID'])]
    private string $regionId = 'cn-shanghai';

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '转码模板组ID'])]
    private ?string $templateGroupId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '存储地址'])]
    private ?string $storageLocation = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '回调URL'])]
    private ?string $callbackUrl = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否默认配置'])]
    private bool $isDefault = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    private bool $valid = true;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '创建时间'])]
    private \DateTime $createdTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '更新时间'])]
    private \DateTime $updatedTime;

    public function __construct()
    {
        $this->createdTime = new \DateTime();
        $this->updatedTime = new \DateTime();
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

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function getAccessKeyId(): string
    {
        return $this->accessKeyId;
    }

    public function setAccessKeyId(string $accessKeyId): self
    {
        $this->accessKeyId = $accessKeyId;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function getAccessKeySecret(): string
    {
        return $this->accessKeySecret;
    }

    public function setAccessKeySecret(string $accessKeySecret): self
    {
        $this->accessKeySecret = $accessKeySecret;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function getRegionId(): string
    {
        return $this->regionId;
    }

    public function setRegionId(string $regionId): self
    {
        $this->regionId = $regionId;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function getTemplateGroupId(): ?string
    {
        return $this->templateGroupId;
    }

    public function setTemplateGroupId(?string $templateGroupId): self
    {
        $this->templateGroupId = $templateGroupId;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function getStorageLocation(): ?string
    {
        return $this->storageLocation;
    }

    public function setStorageLocation(?string $storageLocation): self
    {
        $this->storageLocation = $storageLocation;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    public function setCallbackUrl(?string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;
        $this->updatedTime = new \DateTime();
        return $this;
    }

    public function getCreatedTime(): \DateTime
    {
        return $this->createdTime;
    }

    public function getUpdatedTime(): \DateTime
    {
        return $this->updatedTime;
    }
}
