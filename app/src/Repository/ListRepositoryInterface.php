<?php

namespace App\Repository;

use RedisException;

interface ListRepositoryInterface
{
    /**
     * @param string $queueName
     * @return string
     * @throws RedisException
     */
    public function dequeue(string $queueName): string;

    /**
     * @param string $queueName
     * @return int
     * @throws RedisException
     */
    public function length(string $queueName): int;

    /**
     * @param string $queueName
     * @param object[] $objectValues
     * @return int
     * @throws RedisException
     */
    public function enqueueBatch(string $queueName, array $objectValues): int;
}
