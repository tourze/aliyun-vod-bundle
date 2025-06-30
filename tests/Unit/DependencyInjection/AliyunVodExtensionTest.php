<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\AliyunVodBundle\DependencyInjection\AliyunVodExtension;

/**
 * @covers \Tourze\AliyunVodBundle\DependencyInjection\AliyunVodExtension
 */
class AliyunVodExtensionTest extends TestCase
{
    private AliyunVodExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new AliyunVodExtension();
        $this->container = new ContainerBuilder();
    }

    public function testExtensionInheritance(): void
    {
        $this->assertInstanceOf(\Symfony\Component\DependencyInjection\Extension\Extension::class, $this->extension);
    }

    public function testLoad(): void
    {
        $configs = [];
        
        // 测试加载过程不会抛出异常
        $this->extension->load($configs, $this->container);
        
        $this->assertTrue(true); // 如果没有异常抛出则测试通过
    }

    public function testGetAlias(): void
    {
        $alias = $this->extension->getAlias();
        
        $this->assertEquals('aliyun_vod', $alias);
    }

    public function testLoadWithConfiguration(): void
    {
        $configs = [
            'aliyun_vod' => [
                'default_config' => 'test',
            ]
        ];
        
        // 测试配置加载不会抛出异常
        $this->extension->load($configs, $this->container);
        
        $this->assertTrue(true);
    }
}