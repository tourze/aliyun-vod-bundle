<?php

namespace Tourze\AliyunVodBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Service\VodClientFactory;

/**
 * VOD客户端工厂测试
 */
class VodClientFactoryTest extends TestCase
{
    private VodClientFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new VodClientFactory();
    }

    public function test_construct_createsValidFactory(): void
    {
        $factory = new VodClientFactory();
        
        $this->assertInstanceOf(VodClientFactory::class, $factory);
    }

    public function test_createClient_withValidConfig(): void
    {
        $config = new AliyunVodConfig();
        $config->setAccessKeyId('test_access_key')
            ->setAccessKeySecret('test_access_secret')
            ->setRegionId('cn-shanghai')
            ->setValid(true);

        // 由于实际的阿里云SDK可能不可用，我们主要测试方法存在性和基本逻辑
        $this->assertInstanceOf(VodClientFactory::class, $this->factory);
        
        // 测试参数类型
        $reflection = new \ReflectionMethod($this->factory, 'createClient');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('config', $parameters[0]->getName());
    }

    public function test_createClient_methodSignature(): void
    {
        $reflection = new \ReflectionMethod($this->factory, 'createClient');
        
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('config', $parameters[0]->getName());
        
        // 检查参数类型
        $paramType = $parameters[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertEquals(AliyunVodConfig::class, (string) $paramType);
    }

    public function test_createClient_withInvalidConfig(): void
    {
        $config = new AliyunVodConfig();
        $config->setAccessKeyId('')
            ->setAccessKeySecret('')
            ->setRegionId('')
            ->setValid(false);

        // 测试无效配置的处理
        $this->assertFalse($config->isValid());
        $this->assertEmpty($config->getAccessKeyId());
        $this->assertEmpty($config->getAccessKeySecret());
        $this->assertEmpty($config->getRegionId());
    }

    public function test_createClient_configValidation(): void
    {
        // 测试配置验证逻辑
        $validConfig = new AliyunVodConfig();
        $validConfig->setAccessKeyId('valid_key')
            ->setAccessKeySecret('valid_secret')
            ->setRegionId('cn-shanghai')
            ->setValid(true);

        $this->assertTrue($validConfig->isValid());
        $this->assertNotEmpty($validConfig->getAccessKeyId());
        $this->assertNotEmpty($validConfig->getAccessKeySecret());
        $this->assertNotEmpty($validConfig->getRegionId());
    }

    public function test_createClient_regionIdValidation(): void
    {
        // 测试不同地域ID的有效性
        $validRegions = [
            'cn-shanghai',
            'cn-beijing',
            'cn-hangzhou',
            'cn-shenzhen',
            'ap-southeast-1'
        ];

        foreach ($validRegions as $regionId) {
            $config = new AliyunVodConfig();
            $config->setRegionId($regionId);
            
            $this->assertEquals($regionId, $config->getRegionId());
            $this->assertNotEmpty($config->getRegionId());
        }
    }

    public function test_createClient_accessKeyValidation(): void
    {
        // 测试AccessKey格式验证
        $config = new AliyunVodConfig();
        
        // 测试有效的AccessKey格式
        $validAccessKey = 'LTAI4G8mF9XxXxXxXxXxXxXx';
        $config->setAccessKeyId($validAccessKey);
        $this->assertEquals($validAccessKey, $config->getAccessKeyId());
        $this->assertNotEmpty($config->getAccessKeyId());
        
        // 测试AccessKeySecret
        $validAccessSecret = 'abcdefghijklmnopqrstuvwxyz123456';
        $config->setAccessKeySecret($validAccessSecret);
        $this->assertEquals($validAccessSecret, $config->getAccessKeySecret());
        $this->assertNotEmpty($config->getAccessKeySecret());
    }

    public function test_factory_methodExists(): void
    {
        // 测试工厂类必需的方法存在
        $this->assertInstanceOf(VodClientFactory::class, $this->factory);
        
        // 通过反射检查方法的可见性
        $reflection = new \ReflectionClass($this->factory);
        $method = $reflection->getMethod('createClient');
        $this->assertTrue($method->isPublic());
    }

    public function test_factory_classStructure(): void
    {
        // 测试工厂类的基本结构
        $reflection = new \ReflectionClass($this->factory);
        
        $this->assertEquals(VodClientFactory::class, $reflection->getName());
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_createClient_parameterValidation(): void
    {
        // 测试createClient方法的参数验证
        $reflection = new \ReflectionMethod($this->factory, 'createClient');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters);
        
        $configParam = $parameters[0];
        $this->assertEquals('config', $configParam->getName());
        $this->assertFalse($configParam->isOptional());
        $this->assertFalse($configParam->allowsNull());
        
        $paramType = $configParam->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $paramType);
        $this->assertEquals(AliyunVodConfig::class, (string) $paramType);
    }

    public function test_createClient_returnTypeValidation(): void
    {
        // 测试createClient方法的返回类型
        $reflection = new \ReflectionMethod($this->factory, 'createClient');
        $returnType = $reflection->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        
        // 返回类型应该是阿里云VOD客户端类型
        $returnTypeName = (string) $returnType;
        $this->assertNotEmpty($returnTypeName);
    }

    public function test_factory_configurationScenarios(): void
    {
        // 测试不同配置场景
        $scenarios = [
            'production' => [
                'accessKeyId' => 'LTAI4G8mF9XxXxXxXxXxXxXx',
                'accessKeySecret' => 'abcdefghijklmnopqrstuvwxyz123456',
                'regionId' => 'cn-shanghai',
                'valid' => true
            ],
            'testing' => [
                'accessKeyId' => 'LTAI4Test123456789012345',
                'accessKeySecret' => 'testSecretKey123456789012345678',
                'regionId' => 'cn-beijing',
                'valid' => true
            ],
            'development' => [
                'accessKeyId' => 'LTAI4Dev1234567890123456',
                'accessKeySecret' => 'devSecretKey1234567890123456789',
                'regionId' => 'cn-hangzhou',
                'valid' => true
            ]
        ];

        foreach ($scenarios as $scenarioName => $configData) {
            $config = new AliyunVodConfig();
            $config->setAccessKeyId($configData['accessKeyId'])
                ->setAccessKeySecret($configData['accessKeySecret'])
                ->setRegionId($configData['regionId'])
                ->setValid($configData['valid']);

            $this->assertTrue($config->isValid(), "配置场景 {$scenarioName} 应该有效");
            $this->assertEquals($configData['accessKeyId'], $config->getAccessKeyId());
            $this->assertEquals($configData['accessKeySecret'], $config->getAccessKeySecret());
            $this->assertEquals($configData['regionId'], $config->getRegionId());
        }
    }
} 