<?php

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 视频实体测试
 */
class VideoTest extends TestCase
{
    private Video $video;
    private AliyunVodConfig $config;

    protected function setUp(): void
    {
        $this->video = new Video();
        $this->config = new AliyunVodConfig();
        $this->config->setName('测试配置');
    }

    public function test_construct_setsDefaultValues(): void
    {
        $video = new Video();
        
        $this->assertEquals('Uploading', $video->getStatus());
        $this->assertTrue($video->isValid());
        $this->assertInstanceOf(\DateTime::class, $video->getCreatedTime());
        $this->assertInstanceOf(\DateTime::class, $video->getUpdatedTime());
    }

    public function test_setConfig_withValidConfig(): void
    {
        $result = $this->video->setConfig($this->config);
        
        $this->assertSame($this->video, $result);
        $this->assertSame($this->config, $this->video->getConfig());
    }

    public function test_setVideoId_withValidId(): void
    {
        $videoId = 'test_video_001';
        $result = $this->video->setVideoId($videoId);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($videoId, $this->video->getVideoId());
    }

    public function test_setTitle_withValidTitle(): void
    {
        $title = '测试视频标题';
        $result = $this->video->setTitle($title);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($title, $this->video->getTitle());
    }

    public function test_setTitle_withEmptyString(): void
    {
        $result = $this->video->setTitle('');
        
        $this->assertSame($this->video, $result);
        $this->assertEquals('', $this->video->getTitle());
    }

    public function test_setDescription_withValidDescription(): void
    {
        $description = '这是一个测试视频的详细描述';
        $result = $this->video->setDescription($description);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($description, $this->video->getDescription());
    }

    public function test_setDescription_withNull(): void
    {
        $this->video->setDescription('test');
        $result = $this->video->setDescription(null);
        
        $this->assertSame($this->video, $result);
        $this->assertNull($this->video->getDescription());
    }

    public function test_setDuration_withValidDuration(): void
    {
        $duration = 3600; // 1小时
        $result = $this->video->setDuration($duration);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($duration, $this->video->getDuration());
    }

    public function test_setDuration_withZero(): void
    {
        $result = $this->video->setDuration(0);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals(0, $this->video->getDuration());
    }

    public function test_setDuration_withNull(): void
    {
        $this->video->setDuration(100);
        $result = $this->video->setDuration(null);
        
        $this->assertSame($this->video, $result);
        $this->assertNull($this->video->getDuration());
    }

    public function test_setSize_withValidSize(): void
    {
        $size = 1073741824; // 1GB
        $result = $this->video->setSize($size);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($size, $this->video->getSize());
    }

    public function test_setSize_withZero(): void
    {
        $result = $this->video->setSize(0);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals(0, $this->video->getSize());
    }

    public function test_setSize_withNull(): void
    {
        $this->video->setSize(1000);
        $result = $this->video->setSize(null);
        
        $this->assertSame($this->video, $result);
        $this->assertNull($this->video->getSize());
    }

    public function test_setStatus_withValidStatus(): void
    {
        $status = 'Normal';
        $result = $this->video->setStatus($status);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($status, $this->video->getStatus());
    }

    public function test_setStatus_withDifferentStatuses(): void
    {
        $statuses = ['Uploading', 'UploadSucc', 'Transcoding', 'TranscodeSucc', 'Checking', 'Normal', 'Blocked'];
        
        foreach ($statuses as $status) {
            $this->video->setStatus($status);
            $this->assertEquals($status, $this->video->getStatus());
        }
    }

    public function test_setCoverUrl_withValidUrl(): void
    {
        $url = 'https://example.com/cover.jpg';
        $result = $this->video->setCoverUrl($url);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($url, $this->video->getCoverUrl());
    }

    public function test_setCoverUrl_withNull(): void
    {
        $this->video->setCoverUrl('test');
        $result = $this->video->setCoverUrl(null);
        
        $this->assertSame($this->video, $result);
        $this->assertNull($this->video->getCoverUrl());
    }

    public function test_setPlayUrl_withValidUrl(): void
    {
        $url = 'https://example.com/play/video.mp4';
        $result = $this->video->setPlayUrl($url);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($url, $this->video->getPlayUrl());
    }

    public function test_setPlayUrl_withNull(): void
    {
        $this->video->setPlayUrl('test');
        $result = $this->video->setPlayUrl(null);
        
        $this->assertSame($this->video, $result);
        $this->assertNull($this->video->getPlayUrl());
    }

    public function test_setTags_withValidTags(): void
    {
        $tags = '教程,技术,PHP,Symfony';
        $result = $this->video->setTags($tags);
        
        $this->assertSame($this->video, $result);
        $this->assertEquals($tags, $this->video->getTags());
    }

    public function test_setTags_withNull(): void
    {
        $this->video->setTags('test');
        $result = $this->video->setTags(null);
        
        $this->assertSame($this->video, $result);
        $this->assertNull($this->video->getTags());
    }

    public function test_setValid_withTrue(): void
    {
        $result = $this->video->setValid(true);
        
        $this->assertSame($this->video, $result);
        $this->assertTrue($this->video->isValid());
    }

    public function test_setValid_withFalse(): void
    {
        $result = $this->video->setValid(false);
        
        $this->assertSame($this->video, $result);
        $this->assertFalse($this->video->isValid());
    }

    public function test_updatedTime_isUpdatedOnPropertyChange(): void
    {
        $originalTime = $this->video->getUpdatedTime();
        
        usleep(1000);
        
        $this->video->setTitle('新标题');
        $newTime = $this->video->getUpdatedTime();
        
        $this->assertGreaterThan($originalTime, $newTime);
    }

    public function test_toString_returnsTitle(): void
    {
        $title = '测试视频标题';
        $this->video->setTitle($title);
        
        $this->assertEquals($title, (string) $this->video);
    }

    public function test_toString_withEmptyTitle(): void
    {
        $this->video->setTitle('');
        
        $this->assertEquals('', (string) $this->video);
    }

    public function test_allPropertiesChaining(): void
    {
        $result = $this->video
            ->setConfig($this->config)
            ->setVideoId('chain_test_001')
            ->setTitle('链式测试视频')
            ->setDescription('这是链式调用测试')
            ->setDuration(1800)
            ->setSize(524288000)
            ->setStatus('Normal')
            ->setCoverUrl('https://example.com/chain-cover.jpg')
            ->setPlayUrl('https://example.com/chain-play.mp4')
            ->setTags('链式,测试,视频')
            ->setValid(true);
        
        $this->assertSame($this->video, $result);
        $this->assertSame($this->config, $this->video->getConfig());
        $this->assertEquals('chain_test_001', $this->video->getVideoId());
        $this->assertEquals('链式测试视频', $this->video->getTitle());
        $this->assertEquals('这是链式调用测试', $this->video->getDescription());
        $this->assertEquals(1800, $this->video->getDuration());
        $this->assertEquals(524288000, $this->video->getSize());
        $this->assertEquals('Normal', $this->video->getStatus());
        $this->assertEquals('https://example.com/chain-cover.jpg', $this->video->getCoverUrl());
        $this->assertEquals('https://example.com/chain-play.mp4', $this->video->getPlayUrl());
        $this->assertEquals('链式,测试,视频', $this->video->getTags());
        $this->assertTrue($this->video->isValid());
    }

    public function test_createdTime_isImmutable(): void
    {
        $originalTime = $this->video->getCreatedTime();
        
        $this->video->setTitle('测试');
        
        $this->assertEquals($originalTime, $this->video->getCreatedTime());
    }

    public function test_multipleUpdates_updateTimestamp(): void
    {
        $times = [];
        $times[] = $this->video->getUpdatedTime();
        
        usleep(1000);
        $this->video->setTitle('第一次更新');
        $times[] = $this->video->getUpdatedTime();
        
        usleep(1000);
        $this->video->setStatus('Normal');
        $times[] = $this->video->getUpdatedTime();
        
        usleep(1000);
        $this->video->setValid(false);
        $times[] = $this->video->getUpdatedTime();
        
        $this->assertGreaterThan($times[0], $times[1]);
        $this->assertGreaterThan($times[1], $times[2]);
        $this->assertGreaterThan($times[2], $times[3]);
    }

    public function test_largeFileSize_handling(): void
    {
        $largeSize = 5368709120; // 5GB
        $this->video->setSize($largeSize);
        
        $this->assertEquals($largeSize, $this->video->getSize());
    }

    public function test_longDuration_handling(): void
    {
        $longDuration = 86400; // 24小时
        $this->video->setDuration($longDuration);
        
        $this->assertEquals($longDuration, $this->video->getDuration());
    }

    public function test_specialCharacters_inTitle(): void
    {
        $title = '特殊字符测试 !@#$%^&*()_+-=[]{}|;:,.<>?';
        $this->video->setTitle($title);
        
        $this->assertEquals($title, $this->video->getTitle());
    }

    public function test_unicodeCharacters_inDescription(): void
    {
        $description = '这是包含Unicode字符的描述：🎬📹🎥💻';
        $this->video->setDescription($description);
        
        $this->assertEquals($description, $this->video->getDescription());
    }

    public function test_longUrl_handling(): void
    {
        $longUrl = 'https://very-long-domain-name-for-testing-purposes.example.com/very/long/path/to/video/file/with/many/subdirectories/video.mp4?param1=value1&param2=value2&param3=value3';
        $this->video->setPlayUrl($longUrl);
        
        $this->assertEquals($longUrl, $this->video->getPlayUrl());
    }
} 