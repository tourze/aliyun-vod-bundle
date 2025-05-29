<?php

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 阿里云VOD配置实体测试
 */
class AliyunVodConfigTest extends TestCase
{
    private AliyunVodConfig $config;

    protected function setUp(): void
    {
        $this->config = new AliyunVodConfig();
    }

    public function test_construct_setsDefaultValues(): void
    {
        $config = new AliyunVodConfig();

        $this->assertEquals('cn-shanghai', $config->getRegionId());
        $this->assertFalse($config->isDefault());
        $this->assertTrue($config->isValid());
        $this->assertInstanceOf(\DateTime::class, $config->getCreatedTime());
        $this->assertInstanceOf(\DateTime::class, $config->getUpdatedTime());
    }

    public function test_setName_withValidName(): void
    {
        $name = '测试配置';
        $result = $this->config->setName($name);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals($name, $this->config->getName());
    }

    public function test_setName_withEmptyString(): void
    {
        $result = $this->config->setName('');
        
        $this->assertSame($this->config, $result);
        $this->assertEquals('', $this->config->getName());
    }

    public function test_setAccessKeyId_withValidKey(): void
    {
        $keyId = 'LTAI5tTestAccessKeyId123';
        $result = $this->config->setAccessKeyId($keyId);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals($keyId, $this->config->getAccessKeyId());
    }

    public function test_setAccessKeySecret_withValidSecret(): void
    {
        $secret = 'TestAccessKeySecret123456789';
        $result = $this->config->setAccessKeySecret($secret);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals($secret, $this->config->getAccessKeySecret());
    }

    public function test_setRegionId_withValidRegion(): void
    {
        $regionId = 'cn-beijing';
        $result = $this->config->setRegionId($regionId);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals($regionId, $this->config->getRegionId());
    }

    public function test_setRegionId_withDefaultRegion(): void
    {
        // 测试默认值
        $this->assertEquals('cn-shanghai', $this->config->getRegionId());
        
        // 设置新值
        $this->config->setRegionId('cn-hangzhou');
        $this->assertEquals('cn-hangzhou', $this->config->getRegionId());
    }

    public function test_setTemplateGroupId_withValidId(): void
    {
        $templateId = 'VOD_TEMPLATE_GROUP_001';
        $result = $this->config->setTemplateGroupId($templateId);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals($templateId, $this->config->getTemplateGroupId());
    }

    public function test_setTemplateGroupId_withNull(): void
    {
        $this->config->setTemplateGroupId('test');
        $result = $this->config->setTemplateGroupId(null);
        
        $this->assertSame($this->config, $result);
        $this->assertNull($this->config->getTemplateGroupId());
    }

    public function test_setStorageLocation_withValidLocation(): void
    {
        $location = 'outin-test-bucket.oss-cn-shanghai.aliyuncs.com';
        $result = $this->config->setStorageLocation($location);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals($location, $this->config->getStorageLocation());
    }

    public function test_setStorageLocation_withNull(): void
    {
        $this->config->setStorageLocation('test');
        $result = $this->config->setStorageLocation(null);
        
        $this->assertSame($this->config, $result);
        $this->assertNull($this->config->getStorageLocation());
    }

    public function test_setCallbackUrl_withValidUrl(): void
    {
        $url = 'https://example.com/vod/callback';
        $result = $this->config->setCallbackUrl($url);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals($url, $this->config->getCallbackUrl());
    }

    public function test_setCallbackUrl_withNull(): void
    {
        $this->config->setCallbackUrl('test');
        $result = $this->config->setCallbackUrl(null);
        
        $this->assertSame($this->config, $result);
        $this->assertNull($this->config->getCallbackUrl());
    }

    public function test_setIsDefault_withTrue(): void
    {
        $result = $this->config->setIsDefault(true);
        
        $this->assertSame($this->config, $result);
        $this->assertTrue($this->config->isDefault());
    }

    public function test_setIsDefault_withFalse(): void
    {
        $this->config->setIsDefault(true);
        $result = $this->config->setIsDefault(false);
        
        $this->assertSame($this->config, $result);
        $this->assertFalse($this->config->isDefault());
    }

    public function test_setValid_withTrue(): void
    {
        $result = $this->config->setValid(true);
        
        $this->assertSame($this->config, $result);
        $this->assertTrue($this->config->isValid());
    }

    public function test_setValid_withFalse(): void
    {
        $result = $this->config->setValid(false);
        
        $this->assertSame($this->config, $result);
        $this->assertFalse($this->config->isValid());
    }

    public function test_updatedTime_isUpdatedOnPropertyChange(): void
    {
        $originalTime = $this->config->getUpdatedTime();
        
        // 等待一毫秒确保时间不同
        usleep(1000);
        
        $this->config->setName('新名称');
        $newTime = $this->config->getUpdatedTime();
        
        $this->assertGreaterThan($originalTime, $newTime);
    }

    public function test_toString_returnsName(): void
    {
        $name = '测试配置名称';
        $this->config->setName($name);
        
        $this->assertEquals($name, (string) $this->config);
    }

    public function test_toString_withEmptyName(): void
    {
        $this->config->setName('');
        
        $this->assertEquals('', (string) $this->config);
    }

    public function test_allPropertiesChaining(): void
    {
        $result = $this->config
            ->setName('链式测试')
            ->setAccessKeyId('LTAI5tChainTest')
            ->setAccessKeySecret('ChainTestSecret')
            ->setRegionId('cn-shenzhen')
            ->setTemplateGroupId('CHAIN_TEMPLATE')
            ->setStorageLocation('chain-bucket.oss-cn-shenzhen.aliyuncs.com')
            ->setCallbackUrl('https://chain.example.com/callback')
            ->setIsDefault(true)
            ->setValid(false);
        
        $this->assertSame($this->config, $result);
        $this->assertEquals('链式测试', $this->config->getName());
        $this->assertEquals('LTAI5tChainTest', $this->config->getAccessKeyId());
        $this->assertEquals('ChainTestSecret', $this->config->getAccessKeySecret());
        $this->assertEquals('cn-shenzhen', $this->config->getRegionId());
        $this->assertEquals('CHAIN_TEMPLATE', $this->config->getTemplateGroupId());
        $this->assertEquals('chain-bucket.oss-cn-shenzhen.aliyuncs.com', $this->config->getStorageLocation());
        $this->assertEquals('https://chain.example.com/callback', $this->config->getCallbackUrl());
        $this->assertTrue($this->config->isDefault());
        $this->assertFalse($this->config->isValid());
    }

    public function test_createdTime_isImmutable(): void
    {
        $originalTime = $this->config->getCreatedTime();
        
        // 尝试修改其他属性
        $this->config->setName('测试');
        
        // 创建时间应该保持不变
        $this->assertEquals($originalTime, $this->config->getCreatedTime());
    }

    public function test_multipleUpdates_updateTimestamp(): void
    {
        $times = [];
        $times[] = $this->config->getUpdatedTime();
        
        usleep(1000);
        $this->config->setName('第一次更新');
        $times[] = $this->config->getUpdatedTime();
        
        usleep(1000);
        $this->config->setRegionId('cn-beijing');
        $times[] = $this->config->getUpdatedTime();
        
        usleep(1000);
        $this->config->setValid(false);
        $times[] = $this->config->getUpdatedTime();
        
        // 每次更新时间都应该递增
        $this->assertGreaterThan($times[0], $times[1]);
        $this->assertGreaterThan($times[1], $times[2]);
        $this->assertGreaterThan($times[2], $times[3]);
    }
}
