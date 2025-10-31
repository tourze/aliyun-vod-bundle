<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * 转码任务实体测试
 *
 * @internal
 */
#[CoversClass(TranscodeTask::class)]
final class TranscodeTaskTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $task = new TranscodeTask();
        $task->setVideo($video);
        $task->setTaskId('transcode_task_001');

        return $task;
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
        yield 'taskId' => ['taskId', 'transcode_task_001'];
        yield 'templateId' => ['templateId', 'VOD_TEMPLATE_HD_001'];
        yield 'status' => ['status', 'TranscodeSuccess'];
        yield 'progress' => ['progress', 50];
        yield 'errorCode' => ['errorCode', 'InvalidVideo.Format'];
        yield 'errorMessage' => ['errorMessage', '视频格式不支持，请检查输入文件格式'];
        yield 'completedTime' => ['completedTime', new \DateTimeImmutable()];
    }

    public function testConstructSetsDefaultValues(): void
    {
        $task = new TranscodeTask();

        $this->assertEquals('Processing', $task->getStatus());
        $this->assertEquals(0, $task->getProgress());
        $this->assertNull($task->getCompletedTime());
        $this->assertFalse($task->isCompleted());
        $this->assertNotNull($task->getCreatedTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedTime());
        $this->assertNotNull($task->getUpdatedTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getUpdatedTime());
    }

    public function testMarkAsCompletedSetsCompletedTime(): void
    {
        $task = new TranscodeTask();
        $this->assertNull($task->getCompletedTime());
        $this->assertFalse($task->isCompleted());

        $result = $task->markAsCompleted();

        $this->assertSame($task, $result);
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCompletedTime());
        $this->assertTrue($task->isCompleted());
    }

    public function testMarkAsCompletedUpdatesTimestamp(): void
    {
        $task = new TranscodeTask();
        $originalUpdatedTime = $task->getUpdatedTime();

        usleep(1000);

        $task->markAsCompleted();
        $newUpdatedTime = $task->getUpdatedTime();

        $this->assertGreaterThan($originalUpdatedTime, $newUpdatedTime);
    }

    public function testIsCompletedWithCompletedTime(): void
    {
        $task = new TranscodeTask();
        $task->setCompletedTime(new \DateTimeImmutable());

        $this->assertTrue($task->isCompleted());
    }

    public function testIsCompletedWithoutCompletedTime(): void
    {
        $task = new TranscodeTask();
        $task->setCompletedTime(null);

        $this->assertFalse($task->isCompleted());
    }

    public function testToStringReturnsFormattedString(): void
    {
        $task = new TranscodeTask();
        $task->setTaskId('test_task_001');
        $task->setStatus('Processing');

        $expected = '转码任务 test_task_001 (Processing)';
        $this->assertEquals($expected, (string) $task);
    }

    public function testToStringWithDifferentStatus(): void
    {
        $task = new TranscodeTask();
        $task->setTaskId('test_task_002');
        $task->setStatus('TranscodeSuccess');

        $expected = '转码任务 test_task_002 (TranscodeSuccess)';
        $this->assertEquals($expected, (string) $task);
    }

    public function testMultipleUpdatesUpdateTimestamp(): void
    {
        $task = new TranscodeTask();
        $times = [];
        $times[] = $task->getUpdatedTime();

        usleep(1000);
        $task->setProgress(25);
        $times[] = $task->getUpdatedTime();

        usleep(1000);
        $task->setProgress(50);
        $times[] = $task->getUpdatedTime();

        usleep(1000);
        $task->setStatus('TranscodeSuccess');
        $times[] = $task->getUpdatedTime();

        $this->assertGreaterThan($times[0], $times[1]);
        $this->assertGreaterThan($times[1], $times[2]);
        $this->assertGreaterThan($times[2], $times[3]);
    }

    public function testProgressBoundaries(): void
    {
        $task = new TranscodeTask();

        // 测试边界值
        $task->setProgress(0);
        $this->assertEquals(0, $task->getProgress());

        $task->setProgress(100);
        $this->assertEquals(100, $task->getProgress());

        // 测试超出边界的值（虽然在实际使用中应该验证）
        $task->setProgress(-10);
        $this->assertEquals(-10, $task->getProgress());

        $task->setProgress(150);
        $this->assertEquals(150, $task->getProgress());
    }

    public function testErrorHandlingScenario(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $task = new TranscodeTask();
        // 模拟转码失败场景
        $task->setVideo($video);
        $task->setTaskId('failed_task_001');
        $task->setStatus('TranscodeFail');
        $task->setProgress(0);
        $task->setErrorCode('InvalidVideo.Format');
        $task->setErrorMessage('视频格式不支持');
        $task->markAsCompleted();

        $this->assertEquals('TranscodeFail', $task->getStatus());
        $this->assertEquals(0, $task->getProgress());
        $this->assertEquals('InvalidVideo.Format', $task->getErrorCode());
        $this->assertEquals('视频格式不支持', $task->getErrorMessage());
        $this->assertTrue($task->isCompleted());
    }

    public function testSuccessfulTranscodeScenario(): void
    {
        $config = new AliyunVodConfig();
        $config->setName('测试配置');

        $video = new Video();
        $video->setConfig($config);
        $video->setVideoId('test_video_001');
        $video->setTitle('测试视频');

        $task = new TranscodeTask();
        // 模拟转码成功场景
        $task->setVideo($video);
        $task->setTaskId('success_task_001');
        $task->setTemplateId('HD_TEMPLATE');
        $task->setStatus('TranscodeSuccess');
        $task->setProgress(100);
        $task->markAsCompleted();

        $this->assertEquals('TranscodeSuccess', $task->getStatus());
        $this->assertEquals(100, $task->getProgress());
        $this->assertNull($task->getErrorCode());
        $this->assertNull($task->getErrorMessage());
        $this->assertTrue($task->isCompleted());
    }

    public function testLongErrorMessageHandling(): void
    {
        $task = new TranscodeTask();
        $longMessage = str_repeat('这是一个很长的错误消息。', 100);
        $task->setErrorMessage($longMessage);

        $this->assertEquals($longMessage, $task->getErrorMessage());
    }

    public function testSpecialCharactersInTaskId(): void
    {
        $task = new TranscodeTask();
        $taskId = 'task_001_特殊字符_!@#$%';
        $task->setTaskId($taskId);

        $this->assertEquals($taskId, $task->getTaskId());
    }
}
