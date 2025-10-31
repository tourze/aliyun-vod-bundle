<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * 阿里云VOD配置实体测试
 *
 * @internal
 */
#[CoversClass(AliyunVodConfig::class)]
final class AliyunVodConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new AliyunVodConfig();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', '测试配置'];
        yield 'accessKeyId' => ['accessKeyId', 'LTAI5tTestAccessKeyId123'];
        yield 'accessKeySecret' => ['accessKeySecret', 'TestAccessKeySecret123456789'];
        yield 'regionId' => ['regionId', 'cn-beijing'];
        yield 'templateGroupId' => ['templateGroupId', 'VOD_TEMPLATE_GROUP_001'];
        yield 'storageLocation' => ['storageLocation', 'outin-test-bucket.oss-cn-shanghai.aliyuncs.com'];
        yield 'callbackUrl' => ['callbackUrl', 'https://example.com/vod/callback'];
        yield 'isDefault' => ['isDefault', true];
        yield 'valid' => ['valid', false];
    }

    public function testConstructSetsDefaultValues(): void
    {
        $config = new AliyunVodConfig();

        $this->assertEquals('cn-shanghai', $config->getRegionId());
        $this->assertFalse($config->isDefault());
        $this->assertTrue($config->isValid());
        $this->assertInstanceOf(\DateTimeImmutable::class, $config->getCreateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $config->getUpdateTime());
    }

    public function testUpdatedTimeIsUpdatedOnPropertyChange(): void
    {
        $config = new AliyunVodConfig();
        $originalTime = $config->getUpdateTime();

        // 等待一毫秒确保时间不同
        usleep(1000);

        $config->setName('新名称');
        $newTime = $config->getUpdateTime();

        $this->assertGreaterThan($originalTime, $newTime);
    }

    public function testToStringReturnsName(): void
    {
        $config = new AliyunVodConfig();
        $name = '测试配置名称';
        $config->setName($name);

        $this->assertEquals($name, (string) $config);
    }

    public function testToStringWithEmptyName(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('');

        $this->assertEquals('', (string) $config);
    }

    public function testAllPropertiesChaining(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('链式测试');
        $config->setAccessKeyId('LTAI5tChainTest');
        $config->setAccessKeySecret('ChainTestSecret');
        $config->setRegionId('cn-shenzhen');
        $config->setTemplateGroupId('CHAIN_TEMPLATE');
        $config->setStorageLocation('chain-bucket.oss-cn-shenzhen.aliyuncs.com');
        $config->setCallbackUrl('https://chain.example.com/callback');
        $config->setIsDefault(true);
        $config->setValid(false);
        $this->assertEquals('链式测试', $config->getName());
        $this->assertEquals('LTAI5tChainTest', $config->getAccessKeyId());
        $this->assertEquals('ChainTestSecret', $config->getAccessKeySecret());
        $this->assertEquals('cn-shenzhen', $config->getRegionId());
        $this->assertEquals('CHAIN_TEMPLATE', $config->getTemplateGroupId());
        $this->assertEquals('chain-bucket.oss-cn-shenzhen.aliyuncs.com', $config->getStorageLocation());
        $this->assertEquals('https://chain.example.com/callback', $config->getCallbackUrl());
        $this->assertTrue($config->isDefault());
        $this->assertFalse($config->isValid());
    }

    public function testCreatedTimeIsImmutable(): void
    {
        $config = new AliyunVodConfig();
        $originalTime = $config->getCreateTime();

        // 尝试修改其他属性
        $config->setName('测试');

        // 创建时间应该保持不变
        $this->assertEquals($originalTime, $config->getCreateTime());
    }

    public function testMultipleUpdatesUpdateTimestamp(): void
    {
        $config = new AliyunVodConfig();
        $times = [];
        $times[] = $config->getUpdateTime();

        usleep(1000);
        $config->setName('第一次更新');
        $times[] = $config->getUpdateTime();

        usleep(1000);
        $config->setRegionId('cn-beijing');
        $times[] = $config->getUpdateTime();

        usleep(1000);
        $config->setValid(false);
        $times[] = $config->getUpdateTime();

        // 每次更新时间都应该递增
        $this->assertGreaterThan($times[0], $times[1]);
        $this->assertGreaterThan($times[1], $times[2]);
        $this->assertGreaterThan($times[2], $times[3]);
    }
}
