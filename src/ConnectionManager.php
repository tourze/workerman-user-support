<?php

declare(strict_types=1);

namespace Tourze\Workerman\UserSupport;

use Workerman\Connection\ConnectionInterface;

class ConnectionManager
{
    /**
     * @var \WeakMap<ConnectionInterface, User>
     */
    private static \WeakMap $userMap;

    public static function init(): void
    {
        self::$userMap = new \WeakMap();
    }

    public static function getUser(ConnectionInterface $connection): ?User
    {
        return self::$userMap->offsetExists($connection) ? self::$userMap->offsetGet($connection) : null;
    }

    public static function setUser(ConnectionInterface $connection, User $user): void
    {
        self::$userMap->offsetSet($connection, $user);
    }
}

ConnectionManager::init();
