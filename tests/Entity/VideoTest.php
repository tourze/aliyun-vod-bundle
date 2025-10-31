<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * 视频实体测试
 *
 * @internal
 */
#[CoversClass(Video::class)]
final class VideoTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        return $video;
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        yield 'config' => ['config', $config];
        yield 'videoId' => ['videoId', 'test_video_001'];
        yield 'title' => ['title', '测试视频标题'];
        yield 'description' => ['description', '这是一个测试视频的详细描述'];
        yield 'duration' => ['duration', 3600];
        yield 'size' => ['size', 1073741824];
        yield 'status' => ['status', 'Normal'];
        yield 'coverUrl' => ['coverUrl', 'https://example.com/cover.jpg'];
        yield 'playUrl' => ['playUrl', 'https://example.com/play/video.mp4'];
        yield 'tags' => ['tags', '教程,技术,PHP,Symfony'];
        yield 'valid' => ['valid', true];
    }

    public function testConstructSetsDefaultValues(): void
    {
        $video = new Video();

        $this->assertEquals('Uploading', $video->getStatus());
        $this->assertTrue($video->isValid());
        $this->assertNotNull($video->getCreatedTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $video->getCreatedTime());
        $this->assertNotNull($video->getUpdatedTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $video->getUpdatedTime());
    }

    public function testToStringReturnsTitle(): void
    {
        $video = new Video();
        $title = '测试视频标题';
        $video->setTitle($title);

        $this->assertEquals($title, (string) $video);
    }

    public function testToStringWithEmptyTitle(): void
    {
        $video = new Video();
        $video->setTitle('');

        $this->assertEquals('', (string) $video);
    }

    public function testMultipleUpdatesUpdateTimestamp(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $times = [];
        $times[] = $video->getUpdatedTime();

        usleep(1000);
        $video->setTitle('第一次更新');
        $times[] = $video->getUpdatedTime();

        usleep(1000);
        $video->setStatus('Normal');
        $times[] = $video->getUpdatedTime();

        usleep(1000);
        $video->setValid(false);
        $times[] = $video->getUpdatedTime();

        $this->assertGreaterThan($times[0], $times[1]);
        $this->assertGreaterThan($times[1], $times[2]);
        $this->assertGreaterThan($times[2], $times[3]);
    }

    public function testLargeFileSizeHandling(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $largeSize = 5368709120; // 5GB
        $video->setSize($largeSize);

        $this->assertEquals($largeSize, $video->getSize());
    }

    public function testLongDurationHandling(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $longDuration = 86400; // 24小时
        $video->setDuration($longDuration);

        $this->assertEquals($longDuration, $video->getDuration());
    }

    public function testSpecialCharactersInTitle(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');

        $title = '特殊字符测试 !@#$%^&*()_+-=[]{}|;:,.<>?';
        $video->setTitle($title);

        $this->assertEquals($title, $video->getTitle());
    }

    public function testUnicodeCharactersInDescription(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $description = '这是包含Unicode字符的描述：🎬📹🎥💻';
        $video->setDescription($description);

        $this->assertEquals($description, $video->getDescription());
    }

    public function testLongUrlHandling(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $longUrl = 'https://very-long-domain-name-for-testing-purposes.example.com/very/long/path/to/video/file/with/many/subdirectories/video.mp4?param1=value1&param2=value2&param3=value3';
        $video->setPlayUrl($longUrl);

        $this->assertEquals($longUrl, $video->getPlayUrl());
    }
}
