<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AliyunVodBundle\Exception\InvalidConfigurationException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(InvalidConfigurationException::class)]
final class InvalidConfigurationExceptionTest extends AbstractExceptionTestCase
{
}
