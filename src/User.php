<?php

declare(strict_types=1);

namespace Tourze\Workerman\UserSupport;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * 代表远程用户信息
 */
class User
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
        private readonly int $id,
        private readonly string $password = '',
        private readonly int $speedLimit = 0,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    // /////////////////

    public function getSpeedLimit(): int
    {
        return $this->speedLimit;
    }

    // ////////////////

    private function getUploadStatKey(): string
    {
        return 'user-stat-upload-' . $this->getId();
    }

    public function incrUploadSize(int $flowSize): int
    {
        $this->logger->info('记录用户上传流量', [
            'userId' => $this->getId(),
            'value' => $flowSize,
        ]);
        $currentValue = (int) ($this->cache->get($this->getUploadStatKey()) ?? 0);
        $newVal = $currentValue + $flowSize;
        $this->cache->set($this->getUploadStatKey(), $newVal);

        return $newVal;
    }

    public function getUploadSize(): int
    {
        return (int) ($this->cache->get($this->getUploadStatKey()) ?? 0);
    }

    public function popUploadStat(): int
    {
        try {
            return $this->getUploadSize();
        } finally {
            $this->cache->delete($this->getUploadStatKey());
        }
    }

    // ////////////////

    private function getDownloadStatKey(): string
    {
        return 'user-stat-download-' . $this->getId();
    }

    public function incrDownloadSize(int $flowSize): int
    {
        $this->logger->info('记录用户下载流量', [
            'userId' => $this->getId(),
            'value' => $flowSize,
        ]);
        $currentValue = (int) ($this->cache->get($this->getDownloadStatKey()) ?? 0);
        $newVal = $currentValue + $flowSize;
        $this->cache->set($this->getDownloadStatKey(), $newVal);

        return $newVal;
    }

    public function getDownloadSize(): int
    {
        return (int) ($this->cache->get($this->getDownloadStatKey()) ?? 0);
    }

    public function popDownloadStat(): int
    {
        try {
            return $this->getDownloadSize();
        } finally {
            $this->cache->delete($this->getDownloadStatKey());
        }
    }
}
