<?php

namespace Tourze\AliyunVodBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 转码任务实体测试
 */
class TranscodeTaskTest extends TestCase
{
    private TranscodeTask $task;
    private Video $video;
    private AliyunVodConfig $config;

    protected function setUp(): void
    {
        $this->task = new TranscodeTask();
        $this->config = new AliyunVodConfig();
        $this->config->setName('测试配置');
        $this->video = new Video();
        $this->video->setConfig($this->config)
            ->setVideoId('test_video_001')
            ->setTitle('测试视频');
    }

    public function test_construct_setsDefaultValues(): void
    {
        $task = new TranscodeTask();
        
        $this->assertEquals('Processing', $task->getStatus());
        $this->assertEquals(0, $task->getProgress());
        $this->assertNull($task->getCompletedTime());
        $this->assertFalse($task->isCompleted());
        $this->assertInstanceOf(\DateTime::class, $task->getCreatedTime());
        $this->assertInstanceOf(\DateTime::class, $task->getUpdatedTime());
    }

    public function test_setVideo_withValidVideo(): void
    {
        $result = $this->task->setVideo($this->video);
        
        $this->assertSame($this->task, $result);
        $this->assertSame($this->video, $this->task->getVideo());
    }

    public function test_setTaskId_withValidId(): void
    {
        $taskId = 'transcode_task_001';
        $result = $this->task->setTaskId($taskId);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals($taskId, $this->task->getTaskId());
    }

    public function test_setTemplateId_withValidId(): void
    {
        $templateId = 'VOD_TEMPLATE_HD_001';
        $result = $this->task->setTemplateId($templateId);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals($templateId, $this->task->getTemplateId());
    }

    public function test_setTemplateId_withNull(): void
    {
        $this->task->setTemplateId('test');
        $result = $this->task->setTemplateId(null);
        
        $this->assertSame($this->task, $result);
        $this->assertNull($this->task->getTemplateId());
    }

    public function test_setStatus_withValidStatus(): void
    {
        $status = 'TranscodeSuccess';
        $result = $this->task->setStatus($status);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals($status, $this->task->getStatus());
    }

    public function test_setStatus_withDifferentStatuses(): void
    {
        $statuses = ['Processing', 'TranscodeSuccess', 'TranscodeFail', 'TranscodeCancel'];
        
        foreach ($statuses as $status) {
            $this->task->setStatus($status);
            $this->assertEquals($status, $this->task->getStatus());
        }
    }

    public function test_setProgress_withValidProgress(): void
    {
        $progress = 50;
        $result = $this->task->setProgress($progress);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals($progress, $this->task->getProgress());
    }

    public function test_setProgress_withZero(): void
    {
        $this->task->setProgress(50);
        $result = $this->task->setProgress(0);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals(0, $this->task->getProgress());
    }

    public function test_setProgress_withHundred(): void
    {
        $result = $this->task->setProgress(100);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals(100, $this->task->getProgress());
    }

    public function test_setErrorCode_withValidCode(): void
    {
        $errorCode = 'InvalidVideo.Format';
        $result = $this->task->setErrorCode($errorCode);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals($errorCode, $this->task->getErrorCode());
    }

    public function test_setErrorCode_withNull(): void
    {
        $this->task->setErrorCode('test');
        $result = $this->task->setErrorCode(null);
        
        $this->assertSame($this->task, $result);
        $this->assertNull($this->task->getErrorCode());
    }

    public function test_setErrorMessage_withValidMessage(): void
    {
        $errorMessage = '视频格式不支持，请检查输入文件格式';
        $result = $this->task->setErrorMessage($errorMessage);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals($errorMessage, $this->task->getErrorMessage());
    }

    public function test_setErrorMessage_withNull(): void
    {
        $this->task->setErrorMessage('test');
        $result = $this->task->setErrorMessage(null);
        
        $this->assertSame($this->task, $result);
        $this->assertNull($this->task->getErrorMessage());
    }

    public function test_setCompletedTime_withValidTime(): void
    {
        $completedTime = new \DateTime();
        $result = $this->task->setCompletedTime($completedTime);
        
        $this->assertSame($this->task, $result);
        $this->assertEquals($completedTime, $this->task->getCompletedTime());
    }

    public function test_setCompletedTime_withNull(): void
    {
        $this->task->setCompletedTime(new \DateTime());
        $result = $this->task->setCompletedTime(null);
        
        $this->assertSame($this->task, $result);
        $this->assertNull($this->task->getCompletedTime());
    }

    public function test_markAsCompleted_setsCompletedTime(): void
    {
        $this->assertNull($this->task->getCompletedTime());
        $this->assertFalse($this->task->isCompleted());
        
        $result = $this->task->markAsCompleted();
        
        $this->assertSame($this->task, $result);
        $this->assertInstanceOf(\DateTime::class, $this->task->getCompletedTime());
        $this->assertTrue($this->task->isCompleted());
    }

    public function test_markAsCompleted_updatesTimestamp(): void
    {
        $originalUpdatedTime = $this->task->getUpdatedTime();
        
        usleep(1000);
        
        $this->task->markAsCompleted();
        $newUpdatedTime = $this->task->getUpdatedTime();
        
        $this->assertGreaterThan($originalUpdatedTime, $newUpdatedTime);
    }

    public function test_isCompleted_withCompletedTime(): void
    {
        $this->task->setCompletedTime(new \DateTime());
        
        $this->assertTrue($this->task->isCompleted());
    }

    public function test_isCompleted_withoutCompletedTime(): void
    {
        $this->task->setCompletedTime(null);
        
        $this->assertFalse($this->task->isCompleted());
    }

    public function test_updatedTime_isUpdatedOnPropertyChange(): void
    {
        $originalTime = $this->task->getUpdatedTime();
        
        usleep(1000);
        
        $this->task->setProgress(75);
        $newTime = $this->task->getUpdatedTime();
        
        $this->assertGreaterThan($originalTime, $newTime);
    }

    public function test_toString_returnsFormattedString(): void
    {
        $this->task->setTaskId('test_task_001')
            ->setStatus('Processing');
        
        $expected = '转码任务 test_task_001 (Processing)';
        $this->assertEquals($expected, (string) $this->task);
    }

    public function test_toString_withDifferentStatus(): void
    {
        $this->task->setTaskId('test_task_002')
            ->setStatus('TranscodeSuccess');
        
        $expected = '转码任务 test_task_002 (TranscodeSuccess)';
        $this->assertEquals($expected, (string) $this->task);
    }

    public function test_allPropertiesChaining(): void
    {
        $completedTime = new \DateTime();
        
        $result = $this->task
            ->setVideo($this->video)
            ->setTaskId('chain_task_001')
            ->setTemplateId('CHAIN_TEMPLATE')
            ->setStatus('TranscodeSuccess')
            ->setProgress(100)
            ->setErrorCode(null)
            ->setErrorMessage(null)
            ->setCompletedTime($completedTime);
        
        $this->assertSame($this->task, $result);
        $this->assertSame($this->video, $this->task->getVideo());
        $this->assertEquals('chain_task_001', $this->task->getTaskId());
        $this->assertEquals('CHAIN_TEMPLATE', $this->task->getTemplateId());
        $this->assertEquals('TranscodeSuccess', $this->task->getStatus());
        $this->assertEquals(100, $this->task->getProgress());
        $this->assertNull($this->task->getErrorCode());
        $this->assertNull($this->task->getErrorMessage());
        $this->assertEquals($completedTime, $this->task->getCompletedTime());
    }

    public function test_createdTime_isImmutable(): void
    {
        $originalTime = $this->task->getCreatedTime();
        
        $this->task->setProgress(50);
        
        $this->assertEquals($originalTime, $this->task->getCreatedTime());
    }

    public function test_multipleUpdates_updateTimestamp(): void
    {
        $times = [];
        $times[] = $this->task->getUpdatedTime();
        
        usleep(1000);
        $this->task->setProgress(25);
        $times[] = $this->task->getUpdatedTime();
        
        usleep(1000);
        $this->task->setProgress(50);
        $times[] = $this->task->getUpdatedTime();
        
        usleep(1000);
        $this->task->setStatus('TranscodeSuccess');
        $times[] = $this->task->getUpdatedTime();
        
        $this->assertGreaterThan($times[0], $times[1]);
        $this->assertGreaterThan($times[1], $times[2]);
        $this->assertGreaterThan($times[2], $times[3]);
    }

    public function test_progressBoundaries(): void
    {
        // 测试边界值
        $this->task->setProgress(0);
        $this->assertEquals(0, $this->task->getProgress());
        
        $this->task->setProgress(100);
        $this->assertEquals(100, $this->task->getProgress());
        
        // 测试超出边界的值（虽然在实际使用中应该验证）
        $this->task->setProgress(-10);
        $this->assertEquals(-10, $this->task->getProgress());
        
        $this->task->setProgress(150);
        $this->assertEquals(150, $this->task->getProgress());
    }

    public function test_errorHandling_scenario(): void
    {
        // 模拟转码失败场景
        $this->task->setVideo($this->video)
            ->setTaskId('failed_task_001')
            ->setStatus('TranscodeFail')
            ->setProgress(0)
            ->setErrorCode('InvalidVideo.Format')
            ->setErrorMessage('视频格式不支持')
            ->markAsCompleted();
        
        $this->assertEquals('TranscodeFail', $this->task->getStatus());
        $this->assertEquals(0, $this->task->getProgress());
        $this->assertEquals('InvalidVideo.Format', $this->task->getErrorCode());
        $this->assertEquals('视频格式不支持', $this->task->getErrorMessage());
        $this->assertTrue($this->task->isCompleted());
    }

    public function test_successfulTranscode_scenario(): void
    {
        // 模拟转码成功场景
        $this->task->setVideo($this->video)
            ->setTaskId('success_task_001')
            ->setTemplateId('HD_TEMPLATE')
            ->setStatus('TranscodeSuccess')
            ->setProgress(100)
            ->markAsCompleted();
        
        $this->assertEquals('TranscodeSuccess', $this->task->getStatus());
        $this->assertEquals(100, $this->task->getProgress());
        $this->assertNull($this->task->getErrorCode());
        $this->assertNull($this->task->getErrorMessage());
        $this->assertTrue($this->task->isCompleted());
    }

    public function test_longErrorMessage_handling(): void
    {
        $longMessage = str_repeat('这是一个很长的错误消息。', 100);
        $this->task->setErrorMessage($longMessage);
        
        $this->assertEquals($longMessage, $this->task->getErrorMessage());
    }

    public function test_specialCharacters_inTaskId(): void
    {
        $taskId = 'task_001_特殊字符_!@#$%';
        $this->task->setTaskId($taskId);
        
        $this->assertEquals($taskId, $this->task->getTaskId());
    }
} 