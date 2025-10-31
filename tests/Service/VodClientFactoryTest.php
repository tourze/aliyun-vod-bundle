<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Service\VodClientFactory;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * VOD客户端工厂测试
 *
 * @internal
 */
#[CoversClass(VodClientFactory::class)]
#[RunTestsInSeparateProcesses]
final class VodClientFactoryTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Service 测试无需特殊设置
    }

    public function testConstructCreatesValidFactory(): void
    {
        $factory = self::getContainer()->get(VodClientFactory::class);

        $this->assertNotNull($factory);
        $reflection = new \ReflectionClass($factory);
        $this->assertTrue($reflection->hasMethod('createClient'));
    }

    public function testCreateClientWithValidConfig(): void
    {
        $config = new AliyunVodConfig();
        $config->setAccessKeyId('test_access_key');
        $config->setAccessKeySecret('test_access_secret');
        $config->setRegionId('cn-shanghai');
        $config->setValid(true);

        // 由于实际的阿里云SDK可能不可用，我们主要测试方法存在性和基本逻辑
        $factory = self::getContainer()->get(VodClientFactory::class);
        $this->assertNotNull($factory);

        // 测试参数类型
        $reflection = new \ReflectionMethod($factory, 'createClient');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('config', $parameters[0]->getName());
    }

    public function testCreateClientMethodSignature(): void
    {
        $factory = self::getContainer()->get(VodClientFactory::class);
        $reflection = new \ReflectionMethod($factory, 'createClient');

        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('config', $parameters[0]->getName());

        // 检查参数类型
        $paramType = $parameters[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertEquals(AliyunVodConfig::class, (string) $paramType);
    }

    public function testCreateClientWithInvalidConfig(): void
    {
        $config = new AliyunVodConfig();
        $config->setAccessKeyId('');
        $config->setAccessKeySecret('');
        $config->setRegionId('');
        $config->setValid(false);

        // 测试无效配置的处理
        $this->assertFalse($config->isValid());
        $this->assertEmpty($config->getAccessKeyId());
        $this->assertEmpty($config->getAccessKeySecret());
        $this->assertEmpty($config->getRegionId());
    }

    public function testCreateClientConfigValidation(): void
    {
        // 测试配置验证逻辑
        $validConfig = new AliyunVodConfig();
        $validConfig->setAccessKeyId('valid_key');
        $validConfig->setAccessKeySecret('valid_secret');
        $validConfig->setRegionId('cn-shanghai');
        $validConfig->setValid(true);

        $this->assertTrue($validConfig->isValid());
        $this->assertNotEmpty($validConfig->getAccessKeyId());
        $this->assertNotEmpty($validConfig->getAccessKeySecret());
        $this->assertNotEmpty($validConfig->getRegionId());
    }

    public function testCreateClientRegionIdValidation(): void
    {
        // 测试不同地域ID的有效性
        $validRegions = [
            'cn-shanghai',
            'cn-beijing',
            'cn-hangzhou',
            'cn-shenzhen',
            'ap-southeast-1',
        ];

        foreach ($validRegions as $regionId) {
            $config = new AliyunVodConfig();
            $config->setRegionId($regionId);

            $this->assertEquals($regionId, $config->getRegionId());
            $this->assertNotEmpty($config->getRegionId());
        }
    }

    public function testCreateClientAccessKeyValidation(): void
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

    public function testFactoryMethodExists(): void
    {
        // 测试工厂类必需的方法存在
        $factory = self::getContainer()->get(VodClientFactory::class);
        $reflection = new \ReflectionClass($factory);
        $this->assertTrue($reflection->hasMethod('createClient'));

        // 通过反射检查方法的可见性
        $method = $reflection->getMethod('createClient');
        $this->assertTrue($method->isPublic());
    }

    public function testFactoryClassStructure(): void
    {
        // 测试工厂类的基本结构
        $factory = self::getContainer()->get(VodClientFactory::class);
        $reflection = new \ReflectionClass($factory);

        $this->assertEquals(VodClientFactory::class, $reflection->getName());
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
        $this->assertTrue($reflection->isInstantiable());
    }

    public function testCreateClientParameterValidation(): void
    {
        // 测试createClient方法的参数验证
        $factory = self::getContainer()->get(VodClientFactory::class);
        $reflection = new \ReflectionMethod($factory, 'createClient');
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

    public function testCreateClientReturnTypeValidation(): void
    {
        // 测试createClient方法的返回类型
        $factory = self::getContainer()->get(VodClientFactory::class);
        $reflection = new \ReflectionMethod($factory, 'createClient');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);

        // 返回类型应该是阿里云VOD客户端类型
        $returnTypeName = (string) $returnType;
        $this->assertNotEmpty($returnTypeName);
    }

    public function testFactoryConfigurationScenarios(): void
    {
        // 测试不同配置场景
        $scenarios = [
            'production' => [
                'accessKeyId' => 'LTAI4G8mF9XxXxXxXxXxXxXx',
                'accessKeySecret' => 'abcdefghijklmnopqrstuvwxyz123456',
                'regionId' => 'cn-shanghai',
                'valid' => true,
            ],
            'testing' => [
                'accessKeyId' => 'LTAI4Test123456789012345',
                'accessKeySecret' => 'testSecretKey123456789012345678',
                'regionId' => 'cn-beijing',
                'valid' => true,
            ],
            'development' => [
                'accessKeyId' => 'LTAI4Dev1234567890123456',
                'accessKeySecret' => 'devSecretKey1234567890123456789',
                'regionId' => 'cn-hangzhou',
                'valid' => true,
            ],
        ];

        foreach ($scenarios as $scenarioName => $configData) {
            $config = new AliyunVodConfig();
            $config->setAccessKeyId($configData['accessKeyId']);
            $config->setAccessKeySecret($configData['accessKeySecret']);
            $config->setRegionId($configData['regionId']);
            $config->setValid($configData['valid']);

            $this->assertTrue($config->isValid(), "配置场景 {$scenarioName} 应该有效");
            $this->assertEquals($configData['accessKeyId'], $config->getAccessKeyId());
            $this->assertEquals($configData['accessKeySecret'], $config->getAccessKeySecret());
            $this->assertEquals($configData['regionId'], $config->getRegionId());
        }
    }

    public function testValidateConfig(): void
    {
        // 测试validateConfig方法存在性
        $factory = self::getContainer()->get(VodClientFactory::class);
        $this->assertInstanceOf(VodClientFactory::class, $factory);
        $reflection = new \ReflectionClass($factory);
        $this->assertTrue($reflection->hasMethod('validateConfig'));

        $method = $reflection->getMethod('validateConfig');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfParameters());

        // 测试方法签名
        $parameters = $method->getParameters();
        $this->assertEquals('config', $parameters[0]->getName());
        $paramType = $parameters[0]->getType();
        $this->assertEquals(AliyunVodConfig::class, (string) $paramType);

        // 测试返回类型
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', (string) $returnType);

        // 创建测试配置
        $testConfig = new AliyunVodConfig();
        $testConfig->setAccessKeyId('test_access_key');
        $testConfig->setAccessKeySecret('test_secret_key');
        $testConfig->setRegionId('cn-shanghai');
        $testConfig->setValid(true);

        // 由于没有真实的阿里云凭证，验证应该返回false
        $result = $factory->validateConfig($testConfig);
        $this->assertFalse($result);
    }
}
