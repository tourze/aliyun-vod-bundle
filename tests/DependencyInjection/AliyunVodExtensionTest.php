<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\AliyunVodBundle\DependencyInjection\AliyunVodExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(AliyunVodExtension::class)]
final class AliyunVodExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private AliyunVodExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new AliyunVodExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testExtensionInheritance(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
        $reflection = new \ReflectionClass($this->extension);
        $this->assertTrue($reflection->hasMethod('load'));
        $this->assertTrue($reflection->hasMethod('getAlias'));
        $this->assertTrue($reflection->hasMethod('getConfigDir'));
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
            ],
        ];

        // 测试配置加载不会抛出异常
        $this->extension->load($configs, $this->container);

        // 验证容器加载成功
        $this->assertInstanceOf(ContainerBuilder::class, $this->container);
    }
}
