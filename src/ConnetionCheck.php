<?php
/**
 * Copyright (c) 2020
 * Alexandru Negrilã (alex-codes@arntech.ro) - ARN TECHNOLOGY
 */

namespace ARNTech\DoctrineTimeout;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class ConnetionCheck
{
    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception(sprintf("%s can not be unserialized.", self::class));
    }

    /**
     * Check if connection is truly connected. If not, reconnect.
     * @param $check
     * @param LoggerInterface|null $logger
     * @throws \Exception|\Throwable
     */
    public static function reconnectIfNeeded($check, LoggerInterface $logger = null)
    {
        $connection = null;
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        if ($check instanceof EntityManagerInterface) {
            $connection = $check->getConnection();
        } elseif ($check instanceof Connection) {
            $connection = $check;
        } else {
            throw new \InvalidArgumentException("Provided argument must be a Connection or an EntityManager.");
        }
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        if (!self::isTrulyConnected($connection, $logger)) {
            self::reconnect($connection, $logger);
        }
    }

    /**
     * Close the current connection and connect back
     * @param Connection $connection
     * @param LoggerInterface|null $logger
     * @throws \Exception|\Throwable
     */
    public function reconnect(Connection $connection, LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        self::softErrorExecuter(
            function () use ($connection) {
                $connection->close();
                $connection->connect();
            },
            function ($exception) use ($logger) {
                $logger->error(
                    sprintf("An error has ocurred while reconnecting. %s", $exception->getMessage())
                );
                throw $exception;
            }
        );
    }

    /**
     * Check if a connection is truly connected - is connected and is pingable
     * @param Connection $connection
     * @param LoggerInterface|null $logger
     * @return bool
     * @throws \Exception
     */
    public function isTrulyConnected(Connection $connection, LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        return self::isConnected($connection, $logger) && self::isPingable($connection, $logger);
    }

    /**
     * Check if connection is still connected
     * @param Connection $connection
     * @param LoggerInterface $logger
     * @return bool
     * @throws \Exception
     */
    public function isConnected(Connection $connection, LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        return self::softErrorExecuter(
            function () use ($connection, $logger) {
                if ($connection->isConnected()) {
                    $logger->info("Connection is still connected.");
                    return true;
                } else {
                    $logger->info("Connection is not connected anymore.");
                    return false;
                }
            },
            function ($exception) use ($logger) {
                $logger->warning(
                    sprintf("Connection check failed. %s", $exception->getMessage())
                );
                return false;
            }
        );
    }

    /**
     * Check if connection can be pinged
     * @param Connection $connection
     * @param LoggerInterface $logger
     * @return bool
     * @throws \Exception
     */
    public function isPingable(Connection $connection, LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        return self::softErrorExecuter(
            function () use ($connection, $logger) {
                if ($connection->ping()) {
                    $logger->info("Connection is still pingable.");
                    return true;
                } else {
                    $logger->warning("Connection is not pingable anymore.");
                    return false;
                }
            },
            function ($exception) use ($logger) {
                $logger->warning(
                    sprintf("Connection could not be pinged. %s", $exception->getMessage())
                );
                return false;
            }
        );
    }

    /**
     * Catch all errors (Exception and Throwable) for both PHP 5 and 7
     * @param callable $execute
     * @param callable $onError
     * @return mixed
     * @throws \Exception
     */
    private static function softErrorExecuter(callable $execute, callable $onError)
    {
        try {
            return call_user_func_array($execute);
        } catch (\Throwable $e) {
            // Executed only in PHP 7, will not match in PHP 5
            return call_user_func_array($onError, $e);
        } catch (\Exception $e) {
            // Executed only in PHP 5, will not be reached in PHP 7
            return call_user_func_array($onError, $e);
        }
        throw new \Exception("Something unusual happened. This statement should not have been reached.");
    }
}
