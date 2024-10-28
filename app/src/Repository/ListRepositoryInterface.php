<?php

namespace App\Repository;

use RedisException;

interface ListRepositoryInterface
{
    /**
     * @param string $queueName
     * @param object $objectValue
     * @return int
     * @throws RedisException
     */
    public function queue(string $queueName, object $objectValue): int;

    /**
     * @param string $queueName
     * @return string
     * @throws RedisException
     */
    public function dequeue(string $queueName): string;

    /**
     * @param string $prefix
     * @return string
     * @throws RedisException
     */
    public function getQueueByPrefix(string $prefix): string;

    /**
     * @param string $queueName
     * @return bool
     * @throws RedisException
     */
    public function delete(string $queueName): bool;
}
