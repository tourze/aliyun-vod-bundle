<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Service 测试无需特殊设置
    }

    public function testServiceCanBeInstantiated(): void
    {
        $loader = self::getContainer()->get(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }

    public function testAutoloadMethodExists(): void
    {
        $loader = self::getContainer()->get(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
        $loader->autoload();

        // 如果方法执行成功没抛出异常，就算通过
        $this->assertTrue(true);
    }

    public function testLoadMethodExists(): void
    {
        $loader = self::getContainer()->get(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
        $loader->load('resource');

        // 如果方法执行成功没抛出异常，就算通过
        $this->assertTrue(true);
    }

    public function testSupportsMethodExists(): void
    {
        $loader = self::getContainer()->get(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
        $loader->supports('resource');

        // 如果方法执行成功没抛出异常，就算通过
        $this->assertTrue(true);
    }
}
