<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;

/**
 * 阿里云VOD配置管理服务
 */
#[Autoconfigure(public: true)]
readonly class AliyunVodConfigService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AliyunVodConfigRepository $configRepository,
    ) {
    }

    /**
     * 获取默认配置
     */
    public function getDefaultConfig(): ?AliyunVodConfig
    {
        return $this->configRepository->findOneBy([
            'isDefault' => true,
            'valid' => true,
        ]);
    }

    /**
     * 根据名称获取配置
     */
    public function getConfigByName(string $name): ?AliyunVodConfig
    {
        return $this->configRepository->findOneBy([
            'name' => $name,
            'valid' => true,
        ]);
    }

    /**
     * 获取所有激活的配置
     *
     * @return array<int, AliyunVodConfig>
     */
    public function getActiveConfigs(): array
    {
        return $this->configRepository->findBy(['valid' => true]);
    }

    /**
     * 创建新配置
     */
    public function createConfig(
        string $name,
        string $accessKeyId,
        string $accessKeySecret,
        string $regionId = 'cn-shanghai',
        bool $isDefault = false,
    ): AliyunVodConfig {
        // 如果设置为默认配置，先取消其他默认配置
        if ($isDefault) {
            $this->clearDefaultConfigs();
        }

        $config = new AliyunVodConfig();
        $config->setName($name);
        $config->setAccessKeyId($accessKeyId);
        $config->setAccessKeySecret($this->encryptSecret($accessKeySecret));
        $config->setRegionId($regionId);
        $config->setIsDefault($isDefault);

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        return $config;
    }

    /**
     * 更新配置
     */
    public function updateConfig(AliyunVodConfig $config): void
    {
        // 如果设置为默认配置，先取消其他默认配置
        if ($config->isDefault()) {
            $this->clearDefaultConfigs($config->getId());
        }

        $this->entityManager->flush();
    }

    /**
     * 删除配置
     */
    public function deleteConfig(AliyunVodConfig $config): void
    {
        $this->entityManager->remove($config);
        $this->entityManager->flush();
    }

    /**
     * 设置默认配置
     */
    public function setDefaultConfig(AliyunVodConfig $config): void
    {
        $this->clearDefaultConfigs();
        $config->setIsDefault(true);
        $this->entityManager->flush();
    }

    /**
     * 清除所有默认配置标记
     */
    private function clearDefaultConfigs(?int $excludeId = null): void
    {
        $qb = $this->configRepository->createQueryBuilder('c')
            ->update()
            ->set('c.isDefault', ':false')
            ->where('c.isDefault = :true')
            ->setParameter('false', false)
            ->setParameter('true', true)
        ;

        if (null !== $excludeId) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId)
            ;
        }

        $qb->getQuery()->execute();
    }

    /**
     * 加密敏感信息
     * TODO: 实现真正的加密逻辑
     */
    private function encryptSecret(string $secret): string
    {
        // 这里应该使用真正的加密算法
        // 暂时使用base64编码作为占位符
        return base64_encode($secret);
    }

    /**
     * 解密敏感信息
     * TODO: 实现真正的解密逻辑
     */
    public function decryptSecret(string $encryptedSecret): string
    {
        // 这里应该使用真正的解密算法
        // 暂时使用base64解码作为占位符
        $decoded = base64_decode($encryptedSecret, true);

        return false !== $decoded ? $decoded : $encryptedSecret;
    }
}
