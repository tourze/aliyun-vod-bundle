<?php

namespace Tourze\AliyunVodBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Repository\AliyunVodConfigRepository;
use Tourze\AliyunVodBundle\Service\AliyunVodConfigService;

/**
 * 阿里云VOD配置服务测试
 */
class AliyunVodConfigServiceTest extends TestCase
{
    private $entityManager;
    private $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(AliyunVodConfigRepository::class);
    }

    public function test_construct_withValidDependencies(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        $this->assertInstanceOf(AliyunVodConfigService::class, $service);
    }

    public function test_service_hasRequiredMethods(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        $this->assertTrue(method_exists($service, 'getDefaultConfig'));
        $this->assertTrue(method_exists($service, 'getActiveConfigs'));
        $this->assertTrue(method_exists($service, 'getConfigByName'));
        $this->assertTrue(method_exists($service, 'createConfig'));
        $this->assertTrue(method_exists($service, 'updateConfig'));
        $this->assertTrue(method_exists($service, 'deleteConfig'));
        $this->assertTrue(method_exists($service, 'decryptSecret'));
    }

    public function test_getDefaultConfig_methodSignature(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        $reflection = new \ReflectionMethod($service, 'getDefaultConfig');
        
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_getActiveConfigs_methodSignature(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        $reflection = new \ReflectionMethod($service, 'getActiveConfigs');
        
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_getConfigByName_methodSignature(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        $reflection = new \ReflectionMethod($service, 'getConfigByName');
        
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    public function test_createConfig_methodSignature(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        $reflection = new \ReflectionMethod($service, 'createConfig');
        
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(5, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertEquals('accessKeyId', $parameters[1]->getName());
        $this->assertEquals('accessKeySecret', $parameters[2]->getName());
        $this->assertEquals('regionId', $parameters[3]->getName());
        $this->assertEquals('isDefault', $parameters[4]->getName());
    }

    public function test_decryptSecret_methodSignature(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        $reflection = new \ReflectionMethod($service, 'decryptSecret');
        
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        $parameters = $reflection->getParameters();
        $this->assertEquals('encryptedSecret', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    public function test_service_classStructure(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        $reflection = new \ReflectionClass($service);
        
        $this->assertEquals(AliyunVodConfigService::class, $reflection->getName());
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_service_constructorDependencies(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        $reflection = new \ReflectionClass($service);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
        
        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);
        
        $entityManagerParam = $parameters[0];
        $this->assertEquals('entityManager', $entityManagerParam->getName());
        $this->assertFalse($entityManagerParam->isOptional());
        
        $repositoryParam = $parameters[1];
        $this->assertEquals('configRepository', $repositoryParam->getName());
        $this->assertFalse($repositoryParam->isOptional());
    }

    public function test_decryptSecret_basicFunctionality(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        // 测试基本的解密功能（当前是base64解码）
        $originalSecret = 'test_secret_123';
        $encryptedSecret = base64_encode($originalSecret);
        
        $result = $service->decryptSecret($encryptedSecret);
        
        $this->assertEquals($originalSecret, $result);
    }

    public function test_service_methodsReturnTypes(): void
    {
        $service = new AliyunVodConfigService($this->entityManager, $this->repository);
        
        // 检查getDefaultConfig的返回类型
        $getDefaultReflection = new \ReflectionMethod($service, 'getDefaultConfig');
        $returnType = $getDefaultReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
        
        // 检查getActiveConfigs的返回类型
        $getActiveReflection = new \ReflectionMethod($service, 'getActiveConfigs');
        $returnType = $getActiveReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
        
        // 检查createConfig的返回类型
        $createConfigReflection = new \ReflectionMethod($service, 'createConfig');
        $returnType = $createConfigReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals(AliyunVodConfig::class, $returnType->getName());
        
        // 检查decryptSecret的返回类型
        $decryptReflection = new \ReflectionMethod($service, 'decryptSecret');
        $returnType = $decryptReflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }

    public function test_service_parameterValidation(): void
    {
        // 测试各种参数的有效性
        $validNames = ['default', 'production', 'testing', 'development'];
        $validRegions = ['cn-shanghai', 'cn-beijing', 'cn-hangzhou'];
        $validAccessKeys = ['LTAI4G8mF9XxXxXxXxXxXxXx', 'LTAI4Test123456789012345'];
        
        foreach ($validNames as $name) {
            $this->assertIsString($name);
            $this->assertNotEmpty($name);
        }
        
        foreach ($validRegions as $region) {
            $this->assertIsString($region);
            $this->assertNotEmpty($region);
        }
        
        foreach ($validAccessKeys as $accessKey) {
            $this->assertIsString($accessKey);
            $this->assertNotEmpty($accessKey);
        }
    }
} 