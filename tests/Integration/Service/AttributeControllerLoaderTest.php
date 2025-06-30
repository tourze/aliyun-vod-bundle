<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Service\AttributeControllerLoader;

/**
 * @covers \Tourze\AliyunVodBundle\Service\AttributeControllerLoader
 */
class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $attributeControllerLoader;

    protected function setUp(): void
    {
        $this->attributeControllerLoader = new AttributeControllerLoader();
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(AttributeControllerLoader::class, $this->attributeControllerLoader);
    }
}