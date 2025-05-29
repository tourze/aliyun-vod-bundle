<?php

namespace Tourze\AliyunVodBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 阿里云VOD配置数据填充
 * 用于创建测试和开发环境的基础配置数据
 */
class AliyunVodConfigFixtures extends Fixture
{
    // 配置引用常量
    public const DEFAULT_CONFIG_REFERENCE = 'aliyun-vod-config-default';
    public const TEST_CONFIG_REFERENCE = 'aliyun-vod-config-test';
    public const BACKUP_CONFIG_REFERENCE = 'aliyun-vod-config-backup';

    public function load(ObjectManager $manager): void
    {
        // 创建默认配置
        $defaultConfig = new AliyunVodConfig();
        $defaultConfig->setName('默认配置')
            ->setAccessKeyId('LTAI5tFakeAccessKeyId123')
            ->setAccessKeySecret(base64_encode('FakeAccessKeySecret123456789'))
            ->setRegionId('cn-shanghai')
            ->setTemplateGroupId('VOD_TEMPLATE_GROUP_001')
            ->setStorageLocation('outin-fake-bucket-123456.oss-cn-shanghai.aliyuncs.com')
            ->setCallbackUrl('https://example.com/vod/callback')
            ->setIsDefault(true)
            ->setValid(true);

        $manager->persist($defaultConfig);
        $this->addReference(self::DEFAULT_CONFIG_REFERENCE, $defaultConfig);

        // 创建测试环境配置
        $testConfig = new AliyunVodConfig();
        $testConfig->setName('测试环境')
            ->setAccessKeyId('LTAI5tTestAccessKeyId456')
            ->setAccessKeySecret(base64_encode('TestAccessKeySecret987654321'))
            ->setRegionId('cn-beijing')
            ->setTemplateGroupId('VOD_TEMPLATE_GROUP_TEST')
            ->setStorageLocation('outin-test-bucket-456789.oss-cn-beijing.aliyuncs.com')
            ->setCallbackUrl('https://test.example.com/vod/callback')
            ->setIsDefault(false)
            ->setValid(true);

        $manager->persist($testConfig);
        $this->addReference(self::TEST_CONFIG_REFERENCE, $testConfig);

        // 创建备用配置
        $backupConfig = new AliyunVodConfig();
        $backupConfig->setName('备用配置')
            ->setAccessKeyId('LTAI5tBackupAccessKeyId789')
            ->setAccessKeySecret(base64_encode('BackupAccessKeySecret111222333'))
            ->setRegionId('cn-hangzhou')
            ->setTemplateGroupId('VOD_TEMPLATE_GROUP_BACKUP')
            ->setStorageLocation('outin-backup-bucket-789012.oss-cn-hangzhou.aliyuncs.com')
            ->setCallbackUrl('https://backup.example.com/vod/callback')
            ->setIsDefault(false)
            ->setValid(false); // 备用配置暂时禁用

        $manager->persist($backupConfig);
        $this->addReference(self::BACKUP_CONFIG_REFERENCE, $backupConfig);

        $manager->flush();
    }
} 