<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AliyunVodBundle\AliyunVodBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(AliyunVodBundle::class)]
#[RunTestsInSeparateProcesses]
final class AliyunVodBundleTest extends AbstractBundleTestCase
{
}
