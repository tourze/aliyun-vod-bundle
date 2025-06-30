<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\AliyunVodBundle\AliyunVodBundle;
use Tourze\AliyunVodBundle\DependencyInjection\AliyunVodExtension;

/**
 * @covers \Tourze\AliyunVodBundle\AliyunVodBundle
 */
class AliyunVodBundleTest extends TestCase
{
    public function testGetContainerExtension(): void
    {
        $bundle = new AliyunVodBundle();
        $extension = $bundle->getContainerExtension();
        
        $this->assertInstanceOf(AliyunVodExtension::class, $extension);
    }

    public function testBuild(): void
    {
        $bundle = new AliyunVodBundle();
        $container = new ContainerBuilder();
        
        // 测试构建过程不会抛出异常
        $bundle->build($container);
        
        $this->assertTrue(true); // 如果没有异常抛出则测试通过
    }
}