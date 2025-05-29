# 阿里云VOD数据同步指南

## 概述

本文档介绍如何使用阿里云VOD Bundle的数据同步功能，包括DataFixtures数据填充和Command命令行工具。

## DataFixtures 数据填充

### 安装依赖

```bash
composer require --dev doctrine/doctrine-fixtures-bundle
```

### 可用的Fixtures

1. **AliyunVodConfigFixtures** - 阿里云VOD配置数据
2. **VideoFixtures** - 示例视频数据
3. **TranscodeTaskFixtures** - 转码任务数据
4. **PlayRecordFixtures** - 播放记录数据

### 执行Fixtures

```bash
# 加载所有fixtures
php bin/console doctrine:fixtures:load

# 追加数据（不清空现有数据）
php bin/console doctrine:fixtures:load --append

# 只加载特定的fixtures组
php bin/console doctrine:fixtures:load --group=dev
```

### Fixtures依赖关系

```
AliyunVodConfigFixtures (基础配置)
    ↓
VideoFixtures (视频数据)
    ↓
TranscodeTaskFixtures (转码任务)
    ↓
PlayRecordFixtures (播放记录)
```

## Command 数据同步命令

### 1. 从阿里云同步视频到本地

```bash
# 基本同步
php bin/console aliyun-vod:sync:from-remote

# 指定配置
php bin/console aliyun-vod:sync:from-remote --config="默认配置"

# 限制同步数量
php bin/console aliyun-vod:sync:from-remote --limit=50

# 强制更新已存在的视频
php bin/console aliyun-vod:sync:from-remote --force

# 试运行模式
php bin/console aliyun-vod:sync:from-remote --dry-run
```

### 2. 从本地同步视频到阿里云

```bash
# 基本同步
php bin/console aliyun-vod:sync:to-remote

# 同步指定视频
php bin/console aliyun-vod:sync:to-remote --video-id="demo_video_001"

# 只同步指定状态的视频
php bin/console aliyun-vod:sync:to-remote --status="Normal"

# 限制同步数量
php bin/console aliyun-vod:sync:to-remote --limit=30

# 试运行模式
php bin/console aliyun-vod:sync:to-remote --dry-run
```

### 3. 同步转码任务状态

```bash
# 同步进行中的转码任务
php bin/console aliyun-vod:sync:transcode-task

# 同步指定任务
php bin/console aliyun-vod:sync:transcode-task --task-id="transcode_task_001"

# 同步指定状态的任务
php bin/console aliyun-vod:sync:transcode-task --status="Processing"

# 限制同步数量
php bin/console aliyun-vod:sync:transcode-task --limit=20

# 试运行模式
php bin/console aliyun-vod:sync:transcode-task --dry-run
```

### 4. 清理过期播放记录

```bash
# 清理90天前的记录（默认）
php bin/console aliyun-vod:cleanup:play-records

# 指定保留天数
php bin/console aliyun-vod:cleanup:play-records --days=30

# 强制执行（不询问确认）
php bin/console aliyun-vod:cleanup:play-records --force

# 试运行模式
php bin/console aliyun-vod:cleanup:play-records --dry-run
```

### 5. 生成统计报表

```bash
# 生成昨日统计报表
php bin/console aliyun-vod:statistics:generate

# 指定日期
php bin/console aliyun-vod:statistics:generate --date="2024-01-15"

# 生成周报
php bin/console aliyun-vod:statistics:generate --type=weekly

# 生成月报
php bin/console aliyun-vod:statistics:generate --type=monthly

# 输出为JSON格式
php bin/console aliyun-vod:statistics:generate --output=json

# 保存到文件
php bin/console aliyun-vod:statistics:generate --output=json --file="/tmp/stats.json"

# 输出为CSV格式
php bin/console aliyun-vod:statistics:generate --output=csv --file="/tmp/stats.csv"
```

## 定时任务配置

### 使用Cron

```bash
# 编辑crontab
crontab -e

# 添加以下任务
# 每6小时同步视频数据
0 */6 * * * /usr/bin/php /path/to/project/bin/console aliyun-vod:sync:from-remote --limit=100

# 每30分钟同步转码任务
*/30 * * * * /usr/bin/php /path/to/project/bin/console aliyun-vod:sync:transcode-task --status=Processing

# 每天凌晨2点清理过期记录
0 2 * * * /usr/bin/php /path/to/project/bin/console aliyun-vod:cleanup:play-records --days=90 --force

# 每天凌晨3点生成统计报表
0 3 * * * /usr/bin/php /path/to/project/bin/console aliyun-vod:statistics:generate --type=daily
```

### 使用Symfony Scheduler（推荐）

如果项目使用了Symfony Scheduler，可以在配置文件中定义：

```yaml
# config/packages/scheduler.yaml
framework:
    scheduler:
        transports:
            default: 'doctrine://default'

        tasks:
            sync_video_from_remote:
                task: 'aliyun-vod:sync:from-remote --limit=100'
                frequency: '0 */6 * * *'
                description: '从阿里云同步视频数据'

            sync_transcode_task:
                task: 'aliyun-vod:sync:transcode-task --status=Processing'
                frequency: '*/30 * * * *'
                description: '同步转码任务状态'

            cleanup_play_records:
                task: 'aliyun-vod:cleanup:play-records --days=90 --force'
                frequency: '0 2 * * *'
                description: '清理过期播放记录'

            generate_statistics:
                task: 'aliyun-vod:statistics:generate --type=daily'
                frequency: '0 3 * * *'
                description: '生成每日统计报表'
```

## 最佳实践

### 1. 数据同步策略

- **增量同步**: 优先使用增量同步，避免重复处理已存在的数据
- **错误处理**: 使用`--dry-run`参数先测试，确认无误后再执行
- **日志监控**: 关注命令执行日志，及时发现和处理异常

### 2. 性能优化

- **批量处理**: 使用`--limit`参数控制单次处理数量，避免内存溢出
- **分时执行**: 将大量数据的同步任务安排在业务低峰期执行
- **并发控制**: 避免同时运行多个相同的同步任务

### 3. 数据安全

- **备份策略**: 在执行清理操作前确保有数据备份
- **权限控制**: 确保只有授权用户可以执行数据同步命令
- **配置安全**: 妥善保管阿里云访问密钥，定期轮换

### 4. 监控告警

- **执行状态**: 监控命令执行的成功率和耗时
- **数据一致性**: 定期检查本地和远程数据的一致性
- **存储空间**: 监控播放记录等数据的增长，及时清理

## 故障排除

### 常见问题

1. **同步失败**
   - 检查网络连接
   - 验证阿里云配置是否正确
   - 查看详细错误日志

2. **内存不足**
   - 减少`--limit`参数值
   - 增加PHP内存限制
   - 分批处理大量数据

3. **权限错误**
   - 检查阿里云AccessKey权限
   - 验证数据库写入权限
   - 确认文件系统权限

### 调试技巧

```bash
# 启用详细输出
php bin/console aliyun-vod:sync:from-remote -vvv

# 查看帮助信息
php bin/console aliyun-vod:sync:from-remote --help

# 检查命令是否注册
php bin/console list aliyun-vod
```

## 扩展开发

### 自定义同步逻辑

可以继承现有的Command类，实现自定义的同步逻辑：

```php
<?php

namespace App\Command;

use Tourze\AliyunVodBundle\Command\SyncVideoFromRemoteCommand;

class CustomSyncCommand extends SyncVideoFromRemoteCommand
{
    public const NAME = 'app:custom-sync';
    
    // 重写同步逻辑
    protected function syncSingleVideo(...): string
    {
        // 自定义实现
    }
}
```

### 添加新的Fixtures

```php
<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Tourze\AliyunVodBundle\DataFixtures\VideoFixtures;

class CustomVideoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 自定义数据填充逻辑
    }
    
    public function getDependencies(): array
    {
        return [VideoFixtures::class];
    }
}
``` 