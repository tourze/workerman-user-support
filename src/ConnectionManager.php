<?php

namespace Tourze\Workerman\UserSupport;

use WeakMap;
use Workerman\Connection\ConnectionInterface;

class ConnectionManager
{
    /**
     * @var WeakMap<ConnectionInterface, User>
     */
    private static WeakMap $userMap;

    public static function init(): void
    {
        self::$userMap = new WeakMap();
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
