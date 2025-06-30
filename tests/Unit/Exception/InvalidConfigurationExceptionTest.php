<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Exception\InvalidConfigurationException;

/**
 * @covers \Tourze\AliyunVodBundle\Exception\InvalidConfigurationException
 */
class InvalidConfigurationExceptionTest extends TestCase
{
    public function testExceptionInheritance(): void
    {
        $exception = new InvalidConfigurationException('Test message');
        
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new InvalidConfigurationException('Test message', 400);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidConfigurationException('Test message', 0, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}