# Workerman 用户支持

[English](README.md) | [中文](README.zh-CN.md)

[![最新版本](https://img.shields.io/packagist/v/tourze/workerman-user-support.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-user-support)
[![总下载量](https://img.shields.io/packagist/dt/tourze/workerman-user-support.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-user-support)

一个为 Workerman 连接添加用户支持的库，使关联用户数据与连接变得简单。

## 功能特性

- 将用户数据与 Workerman 连接关联
- 跟踪每个用户的上传和下载统计信息
- 支持用户速度限制
- 当连接关闭时自动清理用户数据（使用 WeakMap）
- 兼容 PSR 的日志和缓存

## 安装方法

```bash
composer require tourze/workerman-user-support
```

## 快速开始

```php
<?php

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Tourze\Workerman\UserSupport\ConnectionManager;
use Tourze\Workerman\UserSupport\User;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

// 创建 worker 实例
$worker = new Worker('websocket://0.0.0.0:12345');

// 配置你自己的日志和缓存实现
$logger = new YourPsrLogger();
$cache = new YourPsrCache();

$worker->onConnect = function (ConnectionInterface $connection) use ($logger, $cache) {
    // 创建用户并与连接关联
    $user = new User(
        $logger,
        $cache,
        123, // 用户ID
        'password123', // 密码（如需要）
        1024 // 速度限制（字节/秒，如需要）
    );

    ConnectionManager::setUser($connection, $user);
};

$worker->onMessage = function (ConnectionInterface $connection, $data) {
    // 获取与此连接关联的用户
    $user = ConnectionManager::getUser($connection);

    if ($user) {
        // 跟踪数据使用情况
        $bytes = strlen($data);
        $user->incrUploadSize($bytes);

        // 检查用户是否超过速度限制
        $speedLimit = $user->getSpeedLimit();
        if ($speedLimit > 0 && $user->getUploadSize() > $speedLimit) {
            // 处理超过速度限制的情况
        }
    }
};

$worker->onClose = function (ConnectionInterface $connection) {
    // 无需清理，WeakMap 会自动处理
};

Worker::runAll();
```

## API 文档

### User 类

`User` 类代表一个远程用户及其关联数据和统计信息。

```php
// 构造函数
public function __construct(
    private readonly LoggerInterface $logger,
    private readonly CacheInterface $cache,
    private readonly int $id,
    private readonly string $password = '',
    private readonly int $speedLimit = 0,
)

// 方法
public function getId(): int 
public function getPassword(): string
public function getSpeedLimit(): int

// 上传统计
public function incrUploadSize(int $flowSize): int
public function getUploadSize(): int
public function popUploadStat(): int

// 下载统计
public function incrDownloadSize(int $flowSize): int
public function getDownloadSize(): int
public function popDownloadStat(): int
```

### ConnectionManager 类

`ConnectionManager` 类管理连接和用户之间的关联。

```php
// 静态方法
public static function init(): void
public static function getUser(ConnectionInterface $connection): ?User
public static function setUser(ConnectionInterface $connection, User $user): void
```

## 贡献

欢迎贡献！请随时提交 Pull Request。

## 许可证

MIT 许可证。更多信息请查看 [许可证文件](LICENSE)。
