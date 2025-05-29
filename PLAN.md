# 阿里云VOD服务对接开发计划

## 项目概述

本项目旨在创建一个Symfony Bundle，用于对接阿里云视频点播（VOD）服务，提供完整的视频上传、转码、播放、管理等功能。

## 开发阶段

### 第一阶段：基础架构搭建

- [x] 创建Bundle基础结构
- [x] 修复Bundle类名错误
- [x] 修复Extension类名错误
- [x] 完善composer.json依赖
- [x] 创建配置文件结构

### 第二阶段：核心服务实现

- [x] 阿里云VOD SDK集成
- [x] 认证配置服务
- [x] 视频上传服务
- [x] 视频信息查询服务
- [x] 视频转码服务
- [x] 视频删除服务

### 第三阶段：数据模型设计

- [x] 阿里云配置实体（AliyunVodConfig Entity）
- [x] 视频实体（Video Entity）
- [x] 转码任务实体（TranscodeTask Entity）
- [x] 播放记录实体（PlayRecord Entity）
- [ ] 数据库迁移文件

### 第四阶段：高级功能

- [x] 视频截图服务
- [x] 视频水印服务
- [x] 视频审核服务
- [x] 播放统计服务
- [x] 播放凭证服务

### 第五阶段：管理界面

- [x] EasyAdmin集成
- [x] 阿里云配置管理界面
- [x] 视频列表管理
- [x] 视频上传界面
- [x] 转码任务监控
- [x] 播放统计报表

### 第六阶段：测试与文档

- [x] DataFixtures数据填充
- [x] 数据同步Command命令
- [x] 定时任务配置
- [x] 数据同步使用文档
- [ ] 单元测试
- [ ] 集成测试
- [ ] API文档
- [ ] 示例代码

## 技术栈

- **PHP**: ^8.1
- **Symfony**: ^6.4
- **Doctrine ORM**: ^3.0
- **阿里云VOD SDK**: 待确定版本
- **PHPUnit**: ^10.0 (测试)
- **PHPStan**: ^2.1 (静态分析)

## 配置结构设计

采用实体存储配置，支持多账号管理：

### AliyunVodConfig实体

- id (主键)
- name (配置名称，如：默认配置、测试环境等)
- accessKeyId (访问密钥ID)
- accessKeySecret (访问密钥Secret，加密存储)
- regionId (地域ID，默认：cn-shanghai)
- templateGroupId (转码模板组ID)
- storageLocation (存储地址)
- callbackUrl (回调URL)
- isDefault (是否默认配置)
- isActive (是否启用)
- createdTime (创建时间)
- updatedTime (更新时间)

## 主要服务类设计

1. **VodClientFactory** - 阿里云VOD客户端工厂（根据配置创建客户端）
2. **AliyunVodConfigService** - 配置管理服务
3. **VideoUploadService** - 视频上传服务
4. **VideoManageService** - 视频管理服务
5. **TranscodeService** - 转码服务
6. **PlayAuthService** - 播放凭证服务
7. **StatisticsService** - 统计服务

## 实体设计

### Video实体

- id (主键)
- config (关联AliyunVodConfig)
- videoId (阿里云视频ID)
- title (标题)
- description (描述)
- duration (时长)
- size (文件大小)
- status (状态)
- coverUrl (封面URL)
- playUrl (播放URL)
- createdTime (创建时间)
- updatedTime (更新时间)

### TranscodeTask实体

- id (主键)
- video (关联Video)
- taskId (阿里云任务ID)
- templateId (转码模板ID)
- status (任务状态)
- progress (进度)
- createdTime (创建时间)
- completedTime (完成时间)

## API接口设计

### 控制器

- AliyunVodConfigController - 配置管理
- VideoController - 视频CRUD操作
- UploadController - 视频上传
- PlayController - 播放相关
- StatisticsController - 统计数据

### 事件系统

- VideoUploadedEvent - 视频上传完成事件
- TranscodeCompletedEvent - 转码完成事件
- VideoDeletedEvent - 视频删除事件

## 安全考虑

- 上传文件类型验证
- 文件大小限制
- 播放权限控制
- API访问频率限制
- 敏感信息加密存储（AccessKeySecret等）
- 配置访问权限控制
- 多租户配置隔离

## 性能优化

- 异步上传处理
- 缓存播放URL
- 批量操作支持
- 数据库查询优化

## 部署要求

- PHP 8.1+
- MySQL/PostgreSQL
- Redis (缓存)
- 阿里云VOD服务账号

## 里程碑

- **M1**: 基础架构完成 (预计1周)
- **M2**: 核心功能实现 (预计2周)
- **M3**: 数据模型完善 (预计1周)
- **M4**: 高级功能开发 (预计2周)
- **M5**: 管理界面完成 (预计1周)
- **M6**: 测试与文档 (预计1周)

## 风险评估

- 阿里云SDK版本兼容性
- 大文件上传稳定性
- 转码任务监控复杂性
- 播放权限控制实现难度

## 参考文档

- [阿里云VOD官方文档](https://help.aliyun.com/product/29932.html)
- [阿里云VOD PHP SDK](https://help.aliyun.com/document_detail/61063.html)
- [Symfony Bundle开发指南](https://symfony.com/doc/current/bundles.html)
