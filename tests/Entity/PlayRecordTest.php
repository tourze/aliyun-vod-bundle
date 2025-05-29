<?php

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\PlayRecord;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 播放记录实体测试
 */
class PlayRecordTest extends TestCase
{
    private PlayRecord $record;
    private Video $video;
    private AliyunVodConfig $config;

    protected function setUp(): void
    {
        $this->record = new PlayRecord();
        $this->config = new AliyunVodConfig();
        $this->config->setName('测试配置');
        $this->video = new Video();
        $this->video->setConfig($this->config)
            ->setVideoId('test_video_001')
            ->setTitle('测试视频');
    }

    public function test_construct_setsDefaultValues(): void
    {
        $record = new PlayRecord();
        
        $this->assertInstanceOf(\DateTime::class, $record->getPlayTime());
    }

    public function test_setVideo_withValidVideo(): void
    {
        $result = $this->record->setVideo($this->video);
        
        $this->assertSame($this->record, $result);
        $this->assertSame($this->video, $this->record->getVideo());
    }

    public function test_setIpAddress_withValidIp(): void
    {
        $ip = '192.168.1.100';
        $result = $this->record->setIpAddress($ip);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($ip, $this->record->getIpAddress());
    }

    public function test_setIpAddress_withIpv6(): void
    {
        $ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $result = $this->record->setIpAddress($ip);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($ip, $this->record->getIpAddress());
    }

    public function test_setIpAddress_withNull(): void
    {
        $this->record->setIpAddress('test');
        $result = $this->record->setIpAddress(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getIpAddress());
    }

    public function test_setUserAgent_withValidUserAgent(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $result = $this->record->setUserAgent($userAgent);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($userAgent, $this->record->getUserAgent());
    }

    public function test_setUserAgent_withNull(): void
    {
        $this->record->setUserAgent('test');
        $result = $this->record->setUserAgent(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getUserAgent());
    }

    public function test_setReferer_withValidUrl(): void
    {
        $referer = 'https://example.com/videos';
        $result = $this->record->setReferer($referer);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($referer, $this->record->getReferer());
    }

    public function test_setReferer_withNull(): void
    {
        $this->record->setReferer('test');
        $result = $this->record->setReferer(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getReferer());
    }

    public function test_setPlayDuration_withValidDuration(): void
    {
        $duration = 1800; // 30分钟
        $result = $this->record->setPlayDuration($duration);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($duration, $this->record->getPlayDuration());
    }

    public function test_setPlayDuration_withZero(): void
    {
        $result = $this->record->setPlayDuration(0);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals(0, $this->record->getPlayDuration());
    }

    public function test_setPlayDuration_withNull(): void
    {
        $this->record->setPlayDuration(100);
        $result = $this->record->setPlayDuration(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getPlayDuration());
    }

    public function test_setPlayPosition_withValidPosition(): void
    {
        $position = 900; // 15分钟
        $result = $this->record->setPlayPosition($position);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($position, $this->record->getPlayPosition());
    }

    public function test_setPlayPosition_withZero(): void
    {
        $result = $this->record->setPlayPosition(0);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals(0, $this->record->getPlayPosition());
    }

    public function test_setPlayPosition_withNull(): void
    {
        $this->record->setPlayPosition(100);
        $result = $this->record->setPlayPosition(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getPlayPosition());
    }

    public function test_setPlayQuality_withValidQuality(): void
    {
        $quality = 'HD';
        $result = $this->record->setPlayQuality($quality);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($quality, $this->record->getPlayQuality());
    }

    public function test_setPlayQuality_withDifferentQualities(): void
    {
        $qualities = ['SD', 'HD', '4K', 'Auto'];
        
        foreach ($qualities as $quality) {
            $this->record->setPlayQuality($quality);
            $this->assertEquals($quality, $this->record->getPlayQuality());
        }
    }

    public function test_setPlayQuality_withNull(): void
    {
        $this->record->setPlayQuality('HD');
        $result = $this->record->setPlayQuality(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getPlayQuality());
    }

    public function test_setDeviceType_withValidType(): void
    {
        $deviceType = 'Desktop';
        $result = $this->record->setDeviceType($deviceType);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($deviceType, $this->record->getDeviceType());
    }

    public function test_setDeviceType_withDifferentTypes(): void
    {
        $types = ['Desktop', 'Mobile', 'Tablet', 'Smart TV'];
        
        foreach ($types as $type) {
            $this->record->setDeviceType($type);
            $this->assertEquals($type, $this->record->getDeviceType());
        }
    }

    public function test_setDeviceType_withNull(): void
    {
        $this->record->setDeviceType('Desktop');
        $result = $this->record->setDeviceType(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getDeviceType());
    }

    public function test_setPlayerVersion_withValidVersion(): void
    {
        $version = '2.1.0';
        $result = $this->record->setPlayerVersion($version);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($version, $this->record->getPlayerVersion());
    }

    public function test_setPlayerVersion_withNull(): void
    {
        $this->record->setPlayerVersion('1.0.0');
        $result = $this->record->setPlayerVersion(null);
        
        $this->assertSame($this->record, $result);
        $this->assertNull($this->record->getPlayerVersion());
    }

    public function test_setPlayTime_withValidTime(): void
    {
        $playTime = new \DateTime('2024-01-15 10:30:00');
        $result = $this->record->setPlayTime($playTime);
        
        $this->assertSame($this->record, $result);
        $this->assertEquals($playTime, $this->record->getPlayTime());
    }

    public function test_toString_returnsFormattedString(): void
    {
        $this->record->setVideo($this->video);
        
        $expected = sprintf('播放记录 - %s', $this->video->getTitle());
        $this->assertEquals($expected, (string) $this->record);
    }

    public function test_allPropertiesChaining(): void
    {
        $playTime = new \DateTime();
        
        $result = $this->record
            ->setVideo($this->video)
            ->setIpAddress('192.168.1.100')
            ->setUserAgent('Mozilla/5.0 Test Browser')
            ->setReferer('https://example.com/test')
            ->setPlayDuration(1800)
            ->setPlayPosition(900)
            ->setPlayQuality('HD')
            ->setDeviceType('Desktop')
            ->setPlayerVersion('2.1.0')
            ->setPlayTime($playTime);
        
        $this->assertSame($this->record, $result);
        $this->assertSame($this->video, $this->record->getVideo());
        $this->assertEquals('192.168.1.100', $this->record->getIpAddress());
        $this->assertEquals('Mozilla/5.0 Test Browser', $this->record->getUserAgent());
        $this->assertEquals('https://example.com/test', $this->record->getReferer());
        $this->assertEquals(1800, $this->record->getPlayDuration());
        $this->assertEquals(900, $this->record->getPlayPosition());
        $this->assertEquals('HD', $this->record->getPlayQuality());
        $this->assertEquals('Desktop', $this->record->getDeviceType());
        $this->assertEquals('2.1.0', $this->record->getPlayerVersion());
        $this->assertEquals($playTime, $this->record->getPlayTime());
    }

    public function test_longUserAgent_handling(): void
    {
        $longUserAgent = str_repeat('Mozilla/5.0 (Very Long User Agent String) ', 20);
        $this->record->setUserAgent($longUserAgent);
        
        $this->assertEquals($longUserAgent, $this->record->getUserAgent());
    }

    public function test_longReferer_handling(): void
    {
        $longReferer = 'https://very-long-domain-name.example.com/very/long/path/to/page/with/many/parameters?param1=value1&param2=value2&param3=value3';
        $this->record->setReferer($longReferer);
        
        $this->assertEquals($longReferer, $this->record->getReferer());
    }

    public function test_largeDuration_handling(): void
    {
        $largeDuration = 86400; // 24小时
        $this->record->setPlayDuration($largeDuration);
        
        $this->assertEquals($largeDuration, $this->record->getPlayDuration());
    }

    public function test_playPosition_greaterThanDuration(): void
    {
        // 在某些情况下，播放位置可能大于播放时长（如快进）
        $this->record->setPlayDuration(1800);
        $this->record->setPlayPosition(2000);
        
        $this->assertEquals(1800, $this->record->getPlayDuration());
        $this->assertEquals(2000, $this->record->getPlayPosition());
    }

    public function test_specialCharacters_inDeviceType(): void
    {
        $deviceType = 'iPhone 15 Pro Max (iOS 17.2)';
        $this->record->setDeviceType($deviceType);
        
        $this->assertEquals($deviceType, $this->record->getDeviceType());
    }

    public function test_versionFormats_inPlayerVersion(): void
    {
        $versions = ['1.0', '2.1.0', '3.0.0-beta', '4.0.0-rc.1', '5.0.0+build.123'];
        
        foreach ($versions as $version) {
            $this->record->setPlayerVersion($version);
            $this->assertEquals($version, $this->record->getPlayerVersion());
        }
    }

    public function test_playTime_withDifferentTimezones(): void
    {
        $utcTime = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
        $this->record->setPlayTime($utcTime);
        
        $this->assertEquals($utcTime, $this->record->getPlayTime());
        $this->assertEquals('UTC', $this->record->getPlayTime()->getTimezone()->getName());
    }

    public function test_mobileUserAgent_scenario(): void
    {
        // 模拟移动设备播放场景
        $this->record->setVideo($this->video)
            ->setIpAddress('10.0.0.1')
            ->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15')
            ->setReferer('https://m.example.com/videos')
            ->setPlayDuration(300)
            ->setPlayPosition(250)
            ->setPlayQuality('Auto')
            ->setDeviceType('Mobile')
            ->setPlayerVersion('2.0.5');
        
        $this->assertEquals('10.0.0.1', $this->record->getIpAddress());
        $this->assertStringContainsString('iPhone', $this->record->getUserAgent());
        $this->assertEquals('Mobile', $this->record->getDeviceType());
        $this->assertEquals('Auto', $this->record->getPlayQuality());
    }

    public function test_desktopUserAgent_scenario(): void
    {
        // 模拟桌面设备播放场景
        $this->record->setVideo($this->video)
            ->setIpAddress('192.168.1.100')
            ->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
            ->setReferer('https://example.com/videos')
            ->setPlayDuration(3600)
            ->setPlayPosition(3600)
            ->setPlayQuality('4K')
            ->setDeviceType('Desktop')
            ->setPlayerVersion('2.1.0');
        
        $this->assertEquals('192.168.1.100', $this->record->getIpAddress());
        $this->assertStringContainsString('Windows NT', $this->record->getUserAgent());
        $this->assertEquals('Desktop', $this->record->getDeviceType());
        $this->assertEquals('4K', $this->record->getPlayQuality());
        $this->assertEquals(3600, $this->record->getPlayPosition()); // 完整播放
    }

    public function test_shortPlaySession_scenario(): void
    {
        // 模拟短时间播放场景（可能是误点或预览）
        $this->record->setVideo($this->video)
            ->setPlayDuration(5)
            ->setPlayPosition(3)
            ->setPlayQuality('SD')
            ->setDeviceType('Mobile');
        
        $this->assertEquals(5, $this->record->getPlayDuration());
        $this->assertEquals(3, $this->record->getPlayPosition());
    }
} 