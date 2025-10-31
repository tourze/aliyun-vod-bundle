<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * 播放记录实体测试
 *
 * @internal
 */
#[CoversClass(PlayRecord::class)]
final class PlayRecordTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $record = new PlayRecord();
        $record->setVideo($video);

        return $record;
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        yield 'video' => ['video', $video];
        yield 'ipAddress' => ['ipAddress', '192.168.1.100'];
        yield 'userAgent' => ['userAgent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'];
        yield 'referer' => ['referer', 'https://example.com/referrer'];
        yield 'playDuration' => ['playDuration', 3600];
        yield 'playPosition' => ['playPosition', 1800];
        yield 'playQuality' => ['playQuality', 'HD'];
        yield 'deviceType' => ['deviceType', 'desktop'];
        yield 'playerVersion' => ['playerVersion', 'v2.1.0'];
    }

    public function testConstructSetsDefaultValues(): void
    {
        $record = new PlayRecord();

        $this->assertNotNull($record->getPlayTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $record->getPlayTime());
    }

    public function testSetPlayTimeWithValidTime(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $record = new PlayRecord();
        $playTime = new \DateTimeImmutable();
        $record->setPlayTime($playTime);

        $this->assertEquals($playTime, $record->getPlayTime());
    }

    public function testToStringReturnsFormattedString(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $record = new PlayRecord();
        $record->setVideo($video);
        $record->setPlayDuration(3600);

        $expected = '播放记录 - 测试视频';
        $this->assertEquals($expected, (string) $record);
    }

    public function testPlayPositionGreaterThanDuration(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $record = new PlayRecord();
        $record->setVideo($video);
        $record->setPlayDuration(1800);  // 30分钟
        $record->setPlayPosition(3600);  // 60分钟，超过时长

        // 虽然播放位置超过时长，但这是允许的（可能是拖动进度条）
        $this->assertEquals(1800, $record->getPlayDuration());
        $this->assertEquals(3600, $record->getPlayPosition());
    }

    public function testPlayTimeWithDifferentTimezones(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $record = new PlayRecord();

        // 测试UTC时间
        $utcTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $record->setPlayTime($utcTime);
        $this->assertEquals($utcTime, $record->getPlayTime());

        // 测试北京时间
        $beijingTime = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Shanghai'));
        $record->setPlayTime($beijingTime);
        $this->assertEquals($beijingTime, $record->getPlayTime());
    }
}
