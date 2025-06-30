<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;

/**
 * @covers \Tourze\AliyunVodBundle\Exception\AliyunVodException
 */
class AliyunVodExceptionTest extends TestCase
{
    public function testExceptionInheritance(): void
    {
        $exception = new AliyunVodException('Test message');
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new AliyunVodException('Test message', 500);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new AliyunVodException('Test message', 0, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}