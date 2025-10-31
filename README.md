# AliyunVodBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/aliyun-vod-bundle/phpunit.yml?branch=master&style=flat-square)](https://github.com/tourze/aliyun-vod-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/aliyun-vod-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/aliyun-vod-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/aliyun-vod-bundle)
[![License](https://img.shields.io/packagist/l/tourze/aliyun-vod-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/aliyun-vod-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/aliyun-vod-bundle?style=flat-square)](https://codecov.io/gh/tourze/aliyun-vod-bundle)

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Console Commands](#console-commands)
- [Usage Examples](#usage-examples)
- [Advanced Usage](#advanced-usage)
- [Admin Interface](#admin-interface)
- [API Endpoints](#api-endpoints)
- [Security](#security)
- [Requirements](#requirements)
- [License](#license)
- [Contributing](#contributing)
- [Support](#support)

A Symfony bundle for integrating with Alibaba Cloud Video on Demand (VOD) services. This bundle provides comprehensive video management features including upload, transcoding, playback, and statistics.

## Features

- **Video Management**: Upload, update, and manage videos in Alibaba Cloud VOD
- **Transcoding**: Handle video transcoding tasks and monitor progress
- **Playback Statistics**: Track video playback data and generate reports
- **Admin Interface**: EasyAdmin integration for video management
- **Console Commands**: Powerful CLI tools for video operations

## Installation

```bash
composer require tourze/aliyun-vod-bundle
```

## Configuration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    Tourze\AliyunVodBundle\AliyunVodBundle::class => ['all' => true],
];
```

Configure your Alibaba Cloud credentials in your environment variables or configuration files.

## Console Commands

### Video Data Synchronization

#### `aliyun-vod:sync:from-remote`
Synchronize video data from Alibaba Cloud VOD to local database.

```bash
# Sync all videos from all configurations
bin/console aliyun-vod:sync:from-remote

# Sync from specific configuration
bin/console aliyun-vod:sync:from-remote --config=default

# Limit sync quantity
bin/console aliyun-vod:sync:from-remote --limit=50

# Force update existing videos
bin/console aliyun-vod:sync:from-remote --force

# Dry run (preview only)
bin/console aliyun-vod:sync:from-remote --dry-run
```

#### `aliyun-vod:sync:to-remote`
Synchronize local video data to Alibaba Cloud VOD.

```bash
# Sync all videos
bin/console aliyun-vod:sync:to-remote

# Sync specific video
bin/console aliyun-vod:sync:to-remote --video-id=VIDEO_ID

# Sync videos with specific status
bin/console aliyun-vod:sync:to-remote --status=Normal

# Limit sync quantity
bin/console aliyun-vod:sync:to-remote --limit=50

# Dry run (preview only)
bin/console aliyun-vod:sync:to-remote --dry-run
```

### Transcoding Management

#### `aliyun-vod:sync:transcode-task`
Synchronize transcoding task status and progress.

```bash
# Sync all processing tasks
bin/console aliyun-vod:sync:transcode-task

# Sync specific task
bin/console aliyun-vod:sync:transcode-task --task-id=TASK_ID

# Sync tasks with specific status
bin/console aliyun-vod:sync:transcode-task --status=Processing

# Limit sync quantity
bin/console aliyun-vod:sync:transcode-task --limit=50

# Dry run (preview only)
bin/console aliyun-vod:sync:transcode-task --dry-run
```

### Statistics and Reports

#### `aliyun-vod:statistics:generate`
Generate playback statistics reports.

```bash
# Generate daily report for yesterday
bin/console aliyun-vod:statistics:generate

# Generate report for specific date
bin/console aliyun-vod:statistics:generate --date=2024-01-01

# Generate weekly report
bin/console aliyun-vod:statistics:generate --type=weekly

# Generate monthly report
bin/console aliyun-vod:statistics:generate --type=monthly

# Output to JSON format
bin/console aliyun-vod:statistics:generate --output=json

# Save to file
bin/console aliyun-vod:statistics:generate --output=json --file=/path/to/report.json

# Output to CSV format
bin/console aliyun-vod:statistics:generate --output=csv --file=/path/to/report.csv
```

### Data Cleanup

#### `aliyun-vod:cleanup:play-records`
Clean up expired playback records.

```bash
# Clean records older than 90 days (default)
bin/console aliyun-vod:cleanup:play-records

# Clean records older than 30 days
bin/console aliyun-vod:cleanup:play-records --days=30

# Force cleanup without confirmation
bin/console aliyun-vod:cleanup:play-records --force

# Dry run (preview only)
bin/console aliyun-vod:cleanup:play-records --dry-run
```

## Usage Examples

### Basic Video Upload

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
        $config = $this->getVodConfig(); // Get your VOD configuration
        
        $result = $this->videoUploadService->uploadVideo(
            $uploadedFile,
            $config,
            'My Video Title',
            'Video description'
        );
        
        return $this->json($result);
    }
}
```

### Get Video Play Auth

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

## Advanced Usage

### Custom Video Processing

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
        
        // Update video metadata
        $this->videoManageService->updateVideoInfo(
            $videoId,
            $config,
            'Updated Title',
            'Updated Description',
            ['tag1', 'tag2']
        );
        
        // Start transcoding with custom template
        $transcodeTask = $this->transcodeService->submitTranscodeJobs(
            $videoId,
            $config,
            'template_group_id'
        );
        
        return $this->json($transcodeTask);
    }
}
```

### Video Statistics and Analytics

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
        
        // Get comprehensive video statistics
        $stats = $this->statisticsService->getVideoPlayStats($video);
        
        // Get completion rate
        $completionRate = $this->statisticsService->getVideoCompletionRate($video);
        
        // Get user behavior analysis
        $userBehavior = $this->statisticsService->getUserPlayBehavior($ipAddress);
        
        return $this->json([
            'stats' => $stats,
            'completion_rate' => $completionRate,
            'user_behavior' => $userBehavior
        ]);
    }
}
```

### Batch Operations

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

## Admin Interface

The bundle includes EasyAdmin CRUD controllers for managing:

- **Videos**: Video library management
- **Transcoding Tasks**: Monitor transcoding progress
- **Playback Records**: View playback statistics
- **VOD Configurations**: Manage Alibaba Cloud credentials

## API Endpoints

### Video Upload
- `POST /admin/video-upload` - Upload video interface
- `POST /admin/video-upload/auth` - Get upload authorization
- `GET /admin/video-upload/progress` - Check upload progress

### Statistics
- `GET /admin/statistics` - Statistics dashboard
- `GET /admin/statistics/popular` - Popular videos
- `GET /admin/statistics/range` - Date range statistics
- `GET /admin/statistics/video-detail/{id}` - Video detail statistics

## Security

### Credential Management

**Never commit credentials to your repository.** Use environment variables or secure configuration management:

```yaml
# config/packages/aliyun_vod.yaml
parameters:
    env(ALIYUN_ACCESS_KEY_ID): ~
    env(ALIYUN_ACCESS_KEY_SECRET): ~
    env(ALIYUN_REGION_ID): 'cn-shanghai'
```

### Access Control

Implement proper access control for video operations:

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

### Data Protection

- **Video Privacy**: Ensure appropriate access controls for video content
- **User Data**: Follow GDPR/privacy regulations when storing playback data
- **API Security**: Use HTTPS for all API communications
- **Input Validation**: Validate all user inputs, especially file uploads

### Best Practices

1. **Rotate Access Keys**: Regularly rotate your Alibaba Cloud access keys
2. **Limit Permissions**: Use IAM policies to limit VOD service permissions
3. **Monitor Access**: Enable logging and monitoring for video operations
4. **Secure Storage**: Encrypt sensitive configuration data at rest
5. **Rate Limiting**: Implement rate limiting for API endpoints

## Requirements

- PHP 8.1 or higher
- Symfony 7.3 or higher
- Doctrine ORM
- Alibaba Cloud VOD SDK

## License

This bundle is released under the MIT license.

## Contributing

Contributions are welcome! Please submit pull requests or create issues for any improvements.

## Support

For support and questions, please create an issue in the repository.