<?php

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
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    ///////////////////

    public function getSpeedLimit(): int
    {
        return $this->speedLimit;
    }

    //////////////////

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
        $newVal = intval($this->cache->get($this->getUploadStatKey()) + $flowSize);
        $this->cache->set($this->getUploadStatKey(), $newVal);
        return $newVal;
    }

    public function getUploadSize(): int
    {
        return intval($this->cache->get($this->getUploadStatKey()));
    }

    public function popUploadStat(): int
    {
        try {
            return $this->getUploadSize();
        } finally {
            $this->cache->delete($this->getUploadStatKey());
        }
    }

    //////////////////

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
        $newVal = intval($this->cache->get($this->getDownloadStatKey()) + $flowSize);
        $this->cache->set($this->getDownloadStatKey(), $newVal);
        return $newVal;
    }

    public function getDownloadSize(): int
    {
        return intval($this->cache->get($this->getDownloadStatKey()));
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
