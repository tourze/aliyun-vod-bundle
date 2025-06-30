<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Exception\InvalidConfigurationException;
use Tourze\AliyunVodBundle\Exception\StatisticsGenerationException;

/**
 * @covers \Tourze\AliyunVodBundle\Exception\StatisticsGenerationException
 */
class StatisticsGenerationExceptionTest extends TestCase
{
    public function testExceptionInheritance(): void
    {
        $exception = new StatisticsGenerationException('Test message');
        
        $this->assertInstanceOf(InvalidConfigurationException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new StatisticsGenerationException('Test message', 500);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new StatisticsGenerationException('Test message', 0, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}