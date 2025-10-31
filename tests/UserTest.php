<?php

declare(strict_types=1);

namespace Tourze\Workerman\UserSupport\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Tourze\Workerman\UserSupport\User;

/**
 * @internal
 */
#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var CacheInterface&MockObject
     */
    private CacheInterface $cache;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建模拟对象
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        // 创建User实例用于测试
        $this->user = new User(
            $this->logger,
            $this->cache,
            123, // 用户ID
            'password123', // 密码
            1024 // 速度限制
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals(123, $this->user->getId());
    }

    public function testGetPassword(): void
    {
        $this->assertEquals('password123', $this->user->getPassword());
    }

    public function testGetSpeedLimit(): void
    {
        $this->assertEquals(1024, $this->user->getSpeedLimit());
    }

    public function testIncrUploadSize(): void
    {
        // 设置缓存模拟行为
        $this->cache->method('get')
            ->with('user-stat-upload-123')
            ->willReturn(100)
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                'user-stat-upload-123',
                150
            )
        ;

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                '记录用户上传流量',
                [
                    'userId' => 123,
                    'value' => 50,
                ]
            )
        ;

        // 测试方法
        $result = $this->user->incrUploadSize(50);
        $this->assertEquals(150, $result);
    }

    public function testPopUploadStat(): void
    {
        // 设置缓存模拟行为
        $this->cache->method('get')
            ->with('user-stat-upload-123')
            ->willReturn(200)
        ;

        $this->cache->expects($this->once())
            ->method('delete')
            ->with('user-stat-upload-123')
        ;

        // 测试方法
        $result = $this->user->popUploadStat();
        $this->assertEquals(200, $result);
    }

    public function testIncrDownloadSize(): void
    {
        // 设置缓存模拟行为
        $this->cache->method('get')
            ->with('user-stat-download-123')
            ->willReturn(300)
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                'user-stat-download-123',
                400
            )
        ;

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                '记录用户下载流量',
                [
                    'userId' => 123,
                    'value' => 100,
                ]
            )
        ;

        // 测试方法
        $result = $this->user->incrDownloadSize(100);
        $this->assertEquals(400, $result);
    }

    public function testPopDownloadStat(): void
    {
        // 设置缓存模拟行为
        $this->cache->method('get')
            ->with('user-stat-download-123')
            ->willReturn(500)
        ;

        $this->cache->expects($this->once())
            ->method('delete')
            ->with('user-stat-download-123')
        ;

        // 测试方法
        $result = $this->user->popDownloadStat();
        $this->assertEquals(500, $result);
    }

    public function testIncrUploadSizeWithNullCache(): void
    {
        // 测试缓存返回null的情况
        $this->cache->method('get')
            ->with('user-stat-upload-123')
            ->willReturn(null)
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                'user-stat-upload-123',
                50
            )
        ;

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                '记录用户上传流量',
                [
                    'userId' => 123,
                    'value' => 50,
                ]
            )
        ;

        // 测试方法
        $result = $this->user->incrUploadSize(50);
        $this->assertEquals(50, $result);
    }

    public function testGetUploadSizeWithNullCache(): void
    {
        // 测试缓存返回null的情况
        $this->cache->method('get')
            ->with('user-stat-upload-123')
            ->willReturn(null)
        ;

        // 测试方法
        $result = $this->user->getUploadSize();
        $this->assertEquals(0, $result);
    }

    public function testIncrDownloadSizeWithNullCache(): void
    {
        // 测试缓存返回null的情况
        $this->cache->method('get')
            ->with('user-stat-download-123')
            ->willReturn(null)
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                'user-stat-download-123',
                100
            )
        ;

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                '记录用户下载流量',
                [
                    'userId' => 123,
                    'value' => 100,
                ]
            )
        ;

        // 测试方法
        $result = $this->user->incrDownloadSize(100);
        $this->assertEquals(100, $result);
    }

    public function testGetDownloadSizeWithNullCache(): void
    {
        // 测试缓存返回null的情况
        $this->cache->method('get')
            ->with('user-stat-download-123')
            ->willReturn(null)
        ;

        // 测试方法
        $result = $this->user->getDownloadSize();
        $this->assertEquals(0, $result);
    }
}
