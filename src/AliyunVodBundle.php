<?php

namespace Tourze\AliyunVodBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class AliyunVodBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle::class => ['all' => true],
        ];
    }
}
