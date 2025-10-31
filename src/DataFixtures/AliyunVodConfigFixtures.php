<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 阿里云VOD配置数据填充
 * 用于创建测试和开发环境的基础配置数据
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class AliyunVodConfigFixtures extends Fixture
{
    // 配置引用常量
    public const DEFAULT_CONFIG_REFERENCE = 'default-config';
    public const TEST_CONFIG_REFERENCE = 'test-config';
    public const BACKUP_CONFIG_REFERENCE = 'backup-config';

    public function load(ObjectManager $manager): void
    {
        // 创建默认配置
        $defaultConfig = new AliyunVodConfig();
        $defaultConfig->setName('默认配置');
        $defaultConfig->setAccessKeyId('LTAI5tFakeAccessKeyId123');
        $defaultConfig->setAccessKeySecret(base64_encode('FakeAccessKeySecret123456789'));
        $defaultConfig->setRegionId('cn-shanghai');
        $defaultConfig->setTemplateGroupId('VOD_TEMPLATE_GROUP_001');
        $defaultConfig->setStorageLocation('outin-fake-bucket-123456.oss-cn-shanghai.aliyuncs.com');
        $defaultConfig->setCallbackUrl('https://images.unsplash.com/photo-1611224923853-80b023f02d71/vod/callback');
        $defaultConfig->setIsDefault(true);
        $defaultConfig->setValid(true);

        $manager->persist($defaultConfig);
        $this->addReference(self::DEFAULT_CONFIG_REFERENCE, $defaultConfig);

        // 创建测试环境配置
        $testConfig = new AliyunVodConfig();
        $testConfig->setName('测试环境');
        $testConfig->setAccessKeyId('LTAI5tTestAccessKeyId456');
        $testConfig->setAccessKeySecret(base64_encode('TestAccessKeySecret987654321'));
        $testConfig->setRegionId('cn-beijing');
        $testConfig->setTemplateGroupId('VOD_TEMPLATE_GROUP_TEST');
        $testConfig->setStorageLocation('outin-test-bucket-456789.oss-cn-beijing.aliyuncs.com');
        $testConfig->setCallbackUrl('https://images.unsplash.com/photo-1516321318423-f06f85e504b3/vod/callback');
        $testConfig->setIsDefault(false);
        $testConfig->setValid(true);

        $manager->persist($testConfig);
        $this->addReference(self::TEST_CONFIG_REFERENCE, $testConfig);

        // 创建备用配置
        $backupConfig = new AliyunVodConfig();
        $backupConfig->setName('备用配置');
        $backupConfig->setAccessKeyId('LTAI5tBackupAccessKeyId789');
        $backupConfig->setAccessKeySecret(base64_encode('BackupAccessKeySecret111222333'));
        $backupConfig->setRegionId('cn-hangzhou');
        $backupConfig->setTemplateGroupId('VOD_TEMPLATE_GROUP_BACKUP');
        $backupConfig->setStorageLocation('outin-backup-bucket-789012.oss-cn-hangzhou.aliyuncs.com');
        $backupConfig->setCallbackUrl('https://images.unsplash.com/photo-1460925895917-afdab827c52f/vod/callback');
        $backupConfig->setIsDefault(false);
        $backupConfig->setValid(false); // 备用配置暂时禁用

        $manager->persist($backupConfig);
        $this->addReference(self::BACKUP_CONFIG_REFERENCE, $backupConfig);

        $manager->flush();
    }
}
