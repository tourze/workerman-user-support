# Workerman User Support

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-user-support.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-user-support)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-user-support.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-user-support)

A library to add user support for Workerman connections, making it easy to associate user data with connections.

## Features

- Associate user data with Workerman connections
- Track upload and download statistics for each user
- Support for user speed limits
- Automatic cleanup of user data when connections are closed (using WeakMap)
- PSR-compatible logging and caching

## Installation

```bash
composer require tourze/workerman-user-support
```

## Quick Start

```php
<?php

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Tourze\Workerman\UserSupport\ConnectionManager;
use Tourze\Workerman\UserSupport\User;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

// Create a worker instance
$worker = new Worker('websocket://0.0.0.0:12345');

// Configure with your own logger and cache implementation
$logger = new YourPsrLogger();
$cache = new YourPsrCache();

$worker->onConnect = function (ConnectionInterface $connection) use ($logger, $cache) {
    // Create a user and associate it with the connection
    $user = new User(
        $logger,
        $cache,
        123, // User ID
        'password123', // Password (if needed)
        1024 // Speed limit in bytes/second (if needed)
    );

    ConnectionManager::setUser($connection, $user);
};

$worker->onMessage = function (ConnectionInterface $connection, $data) {
    // Get user associated with this connection
    $user = ConnectionManager::getUser($connection);

    if ($user) {
        // Track data usage
        $bytes = strlen($data);
        $user->incrUploadSize($bytes);

        // Check if user exceeds speed limit
        $speedLimit = $user->getSpeedLimit();
        if ($speedLimit > 0 && $user->getUploadSize() > $speedLimit) {
            // Handle speed limit exceeded
        }
    }
};

$worker->onClose = function (ConnectionInterface $connection) {
    // No need to clean up, WeakMap handles this automatically
};

Worker::runAll();
```

## API Documentation

### User Class

The `User` class represents a remote user with associated data and statistics.

```php
// Constructor
public function __construct(
    private readonly LoggerInterface $logger,
    private readonly CacheInterface $cache,
    private readonly int $id,
    private readonly string $password = '',
    private readonly int $speedLimit = 0,
)

// Methods
public function getId(): int
public function getPassword(): string
public function getSpeedLimit(): int

// Upload statistics
public function incrUploadSize(int $flowSize): int
public function getUploadSize(): int
public function popUploadStat(): int

// Download statistics
public function incrDownloadSize(int $flowSize): int
public function getDownloadSize(): int
public function popDownloadStat(): int
```

### ConnectionManager Class

The `ConnectionManager` class manages the association between connections and users.

```php
// Static methods
public static function init(): void
public static function getUser(ConnectionInterface $connection): ?User
public static function setUser(ConnectionInterface $connection, User $user): void
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
