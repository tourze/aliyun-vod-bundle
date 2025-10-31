<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AliyunVodBundle\Exception\AliyunVodException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(AliyunVodException::class)]
final class AliyunVodExceptionTest extends AbstractExceptionTestCase
{
}
