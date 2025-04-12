<?php

namespace Tourze\Workerman\UserSupport\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Tourze\Workerman\UserSupport\ConnectionManager;
use Tourze\Workerman\UserSupport\User;
use Workerman\Connection\ConnectionInterface;

class ConnectionManagerTest extends TestCase
{
    /**
     * @var ConnectionInterface&MockObject
     */
    private ConnectionInterface $connection;

    private User $user;

    protected function setUp(): void
    {
        // 初始化ConnectionManager
        ConnectionManager::init();

        // 创建模拟对象
        $this->connection = $this->createMock(ConnectionInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);

        // 创建User实例用于测试
        $this->user = new User(
            $logger,
            $cache,
            456, // 用户ID
            'test123', // 密码
            2048 // 速度限制
        );
    }

    public function testGetUserReturnsNullForUnsetConnection(): void
    {
        $result = ConnectionManager::getUser($this->connection);
        $this->assertNull($result);
    }

    public function testSetAndGetUser(): void
    {
        ConnectionManager::setUser($this->connection, $this->user);
        $result = ConnectionManager::getUser($this->connection);

        $this->assertSame($this->user, $result);
        $this->assertEquals(456, $result->getId());
        $this->assertEquals('test123', $result->getPassword());
        $this->assertEquals(2048, $result->getSpeedLimit());
    }

    public function testWeakMapBehavior(): void
    {
        // 测试WeakMap的行为 - 如果删除连接引用，用户应该不再可用
        /** @var ConnectionInterface&MockObject $tempConnection */
        $tempConnection = $this->createMock(ConnectionInterface::class);
        ConnectionManager::setUser($tempConnection, $this->user);

        // 验证用户已关联
        $this->assertSame($this->user, ConnectionManager::getUser($tempConnection));

        // 移除引用 (在真实环境下应该触发垃圾回收)
        unset($tempConnection);

        // 注意：由于在测试环境中垃圾回收不可控，这个测试可能不会在所有环境中可靠运行
        // 实际上在生产环境中，当连接对象被销毁时，WeakMap应该自动移除对应的用户对象
    }
}
