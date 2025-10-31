<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AliyunVodBundle\Exception\InvalidConfigurationException;
use Tourze\AliyunVodBundle\Exception\StatisticsGenerationException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(StatisticsGenerationException::class)]
final class StatisticsGenerationExceptionTest extends AbstractExceptionTestCase
{
}
