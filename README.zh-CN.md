# AliyunVodBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/aliyun-vod-bundle/phpunit.yml?branch=master&style=flat-square)](https://github.com/tourze/aliyun-vod-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/aliyun-vod-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/aliyun-vod-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/aliyun-vod-bundle)
[![License](https://img.shields.io/packagist/l/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/aliyun-vod-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/aliyun-vod-bundle?style=flat-square)](https://codecov.io/gh/tourze/aliyun-vod-bundle)

## 目录

- [安装](#安装)
- [配置](#配置)
- [控制台命令](#控制台命令)
- [使用示例](#使用示例)
- [高级用法](#高级用法)
- [管理界面](#管理界面)
- [API 端点](#api-端点)
- [安全性](#安全性)
- [系统要求](#系统要求)
- [许可证](#许可证)
- [贡献](#贡献)
- [支持](#支持)

这是一个 Symfony 包，用于集成阿里云视频点播（VOD）服务。此包提供了全面的视频管理功能，包括上传、转码、播放和统计。

## 功能特性

- **视频管理**：在阿里云 VOD 中上传、更新和管理视频
- **转码处理**：处理视频转码任务并监控进度
- **播放统计**：跟踪视频播放数据并生成报表
- **管理界面**：集成 EasyAdmin 的视频管理界面
- **控制台命令**：强大的 CLI 工具用于视频操作

## 安装

```bash
composer require tourze/aliyun-vod-bundle
```

## 配置

将包添加到您的 `config/bundles.php`：

```php
return [
    // ...
    Tourze\AliyunVodBundle\AliyunVodBundle::class => ['all' => true],
];
```

在环境变量或配置文件中配置您的阿里云凭证。

## 控制台命令

### 视频数据同步

#### `aliyun-vod:sync:from-remote`
从阿里云 VOD 同步视频数据到本地数据库。

```bash
# 从所有配置同步所有视频
bin/console aliyun-vod:sync:from-remote

# 从指定配置同步
bin/console aliyun-vod:sync:from-remote --config=default

# 限制同步数量
bin/console aliyun-vod:sync:from-remote --limit=50

# 强制更新已存在的视频
bin/console aliyun-vod:sync:from-remote --force

# 试运行（仅预览）
bin/console aliyun-vod:sync:from-remote --dry-run
```

#### `aliyun-vod:sync:to-remote`
将本地视频数据同步到阿里云 VOD。

```bash
# 同步所有视频
bin/console aliyun-vod:sync:to-remote

# 同步指定视频
bin/console aliyun-vod:sync:to-remote --video-id=VIDEO_ID

# 同步指定状态的视频
bin/console aliyun-vod:sync:to-remote --status=Normal

# 限制同步数量
bin/console aliyun-vod:sync:to-remote --limit=50

# 试运行（仅预览）
bin/console aliyun-vod:sync:to-remote --dry-run
```

### 转码管理

#### `aliyun-vod:sync:transcode-task`
同步转码任务状态和进度。

```bash
# 同步所有处理中的任务
bin/console aliyun-vod:sync:transcode-task

# 同步指定任务
bin/console aliyun-vod:sync:transcode-task --task-id=TASK_ID

# 同步指定状态的任务
bin/console aliyun-vod:sync:transcode-task --status=Processing

# 限制同步数量
bin/console aliyun-vod:sync:transcode-task --limit=50

# 试运行（仅预览）
bin/console aliyun-vod:sync:transcode-task --dry-run
```

### 统计和报表

#### `aliyun-vod:statistics:generate`
生成播放统计报表。

```bash
# 生成昨天的日报
bin/console aliyun-vod:statistics:generate

# 生成指定日期的报表
bin/console aliyun-vod:statistics:generate --date=2024-01-01

# 生成周报
bin/console aliyun-vod:statistics:generate --type=weekly

# 生成月报
bin/console aliyun-vod:statistics:generate --type=monthly

# 输出为 JSON 格式
bin/console aliyun-vod:statistics:generate --output=json

# 保存到文件
bin/console aliyun-vod:statistics:generate --output=json --file=/path/to/report.json

# 输出为 CSV 格式
bin/console aliyun-vod:statistics:generate --output=csv --file=/path/to/report.csv
```

### 数据清理

#### `aliyun-vod:cleanup:play-records`
清理过期的播放记录。

```bash
# 清理 90 天前的记录（默认）
bin/console aliyun-vod:cleanup:play-records

# 清理 30 天前的记录
bin/console aliyun-vod:cleanup:play-records --days=30

# 强制清理，不询问确认
bin/console aliyun-vod:cleanup:play-records --force

# 试运行（仅预览）
bin/console aliyun-vod:cleanup:play-records --dry-run
```

## 使用示例

### 基本视频上传

```php
use Tourze\AliyunVodBundle\Service\VideoUploadService;

class VideoController
{
    public function __construct(
        private VideoUploadService $videoUploadService
    ) {}

    public function upload(Request $request)
    {
        $uploadedFile = $request->files->get('video');
        $config = $this->getVodConfig(); // 获取您的 VOD 配置
        
        $result = $this->videoUploadService->uploadVideo(
            $uploadedFile,
            $config,
            '我的视频标题',
            '视频描述'
        );
        
        return $this->json($result);
    }
}
```

### 获取视频播放凭证

```php
use Tourze\AliyunVodBundle\Service\PlayAuthService;

class VideoController
{
    public function __construct(
        private PlayAuthService $playAuthService
    ) {}

    public function getPlayAuth(string $videoId)
    {
        $config = $this->getVodConfig();
        $playAuth = $this->playAuthService->getPlayAuth($videoId, $config);
        
        return $this->json(['playAuth' => $playAuth]);
    }
}
```

## 高级用法

### 自定义视频处理

```php
use Tourze\AliyunVodBundle\Service\VideoManageService;
use Tourze\AliyunVodBundle\Service\TranscodeService;

class AdvancedVideoController
{
    public function __construct(
        private VideoManageService $videoManageService,
        private TranscodeService $transcodeService
    ) {}

    public function processVideo(string $videoId)
    {
        $config = $this->getVodConfig();
        
        // 更新视频元数据
        $this->videoManageService->updateVideoInfo(
            $videoId,
            $config,
            '更新的标题',
            '更新的描述',
            ['标签1', '标签2']
        );
        
        // 使用自定义模板开始转码
        $transcodeTask = $this->transcodeService->submitTranscodeJobs(
            $videoId,
            $config,
            'template_group_id'
        );
        
        return $this->json($transcodeTask);
    }
}
```

### 视频统计与分析

```php
use Tourze\AliyunVodBundle\Service\StatisticsService;

class AnalyticsController
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    public function getVideoAnalytics(string $videoId)
    {
        $video = $this->getVideo($videoId);
        
        // 获取综合视频统计
        $stats = $this->statisticsService->getVideoPlayStats($video);
        
        // 获取完播率
        $completionRate = $this->statisticsService->getVideoCompletionRate($video);
        
        // 获取用户行为分析
        $userBehavior = $this->statisticsService->getUserPlayBehavior($ipAddress);
        
        return $this->json([
            'stats' => $stats,
            'completion_rate' => $completionRate,
            'user_behavior' => $userBehavior
        ]);
    }
}
```

### 批量操作

```php
use Tourze\AliyunVodBundle\Repository\VideoRepository;

class BatchVideoController
{
    public function __construct(
        private VideoRepository $videoRepository,
        private VideoManageService $videoManageService
    ) {}

    public function batchUpdateStatus(array $videoIds, string $status)
    {
        $config = $this->getVodConfig();
        $results = [];
        
        foreach ($videoIds as $videoId) {
            try {
                $result = $this->videoManageService->updateVideoStatus(
                    $videoId,
                    $config,
                    $status
                );
                $results[] = ['video_id' => $videoId, 'success' => true];
            } catch (\Exception $e) {
                $results[] = [
                    'video_id' => $videoId,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $this->json($results);
    }
}
```

## 管理界面

该包包含用于管理的 EasyAdmin CRUD 控制器：

- **视频**：视频库管理
- **转码任务**：监控转码进度
- **播放记录**：查看播放统计
- **VOD 配置**：管理阿里云凭证

## API 端点

### 视频上传
- `POST /admin/video-upload` - 视频上传界面
- `POST /admin/video-upload/auth` - 获取上传授权
- `GET /admin/video-upload/progress` - 检查上传进度

### 统计
- `GET /admin/statistics` - 统计仪表板
- `GET /admin/statistics/popular` - 热门视频
- `GET /admin/statistics/range` - 日期范围统计
- `GET /admin/statistics/video-detail/{id}` - 视频详细统计

## 安全性

### 凭证管理

**切勿将凭证提交到代码仓库。** 使用环境变量或安全的配置管理：

```yaml
# config/packages/aliyun_vod.yaml
parameters:
    env(ALIYUN_ACCESS_KEY_ID): ~
    env(ALIYUN_ACCESS_KEY_SECRET): ~
    env(ALIYUN_REGION_ID): 'cn-shanghai'
```

### 访问控制

为视频操作实施适当的访问控制：

```php
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VideoController
{
    #[IsGranted('ROLE_VIDEO_ADMIN')]
    public function manageVideos() { /* ... */ }
    
    #[IsGranted('ROLE_VIDEO_UPLOAD')]
    public function uploadVideo() { /* ... */ }
    
    #[IsGranted('ROLE_STATISTICS_VIEW')]
    public function viewStatistics() { /* ... */ }
}
```

### 数据保护

- **视频隐私**：确保视频内容的适当访问控制
- **用户数据**：存储播放数据时遵循 GDPR/隐私法规
- **API 安全**：所有 API 通信使用 HTTPS
- **输入验证**：验证所有用户输入，特别是文件上传

### 最佳实践

1. **轮换访问密钥**：定期轮换您的阿里云访问密钥
2. **限制权限**：使用 IAM 策略限制 VOD 服务权限
3. **监控访问**：为视频操作启用日志记录和监控
4. **安全存储**：静态加密敏感配置数据
5. **速率限制**：为 API 端点实施速率限制

## 系统要求

- PHP 8.1 或更高版本
- Symfony 7.3 或更高版本
- Doctrine ORM
- 阿里云 VOD SDK

## 许可证

此包基于 MIT 许可证发布。

## 贡献

欢迎贡献！请提交拉取请求或创建问题以进行任何改进。

## 支持

如需支持和问题咨询，请在仓库中创建问题。
