<?php

namespace App\Repository;

use App\Service\JsonServiceInterface;
use Redis;
use RedisException;

final readonly class ListRepository implements ListRepositoryInterface
{
    public function __construct(
        private Redis $redis,
        private JsonServiceInterface $jsonService
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function queue(string $queueName, object $objectValue): int
    {
        $listLength = $this->redis->rPush($queueName, $this->jsonService->buildJson($objectValue));

        if (!is_int($listLength)) {
            throw new RedisException('rPush failed');
        }

        return $listLength;
    }

    /**
     * {@inheritDoc}
     */
    public function dequeue(string $queueName): string
    {
        $value = $this->redis->lPop($queueName);

        if (!is_string($value)) {
            throw new RedisException('lPop failed, empty list');
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueueByPrefix(string $prefix): string
    {
        $keys = $this->redis->keys($prefix . '*');

        if (!is_array($keys)) {
            throw new RedisException('scan failed');
        }

        if (empty($keys)) {
            throw new RedisException('scan failed, no queues');
        }

        $key = (string) array_shift($keys);

        if ($this->redis->exists($key . 'blocked')) {
            throw new RedisException('list is blocked');
        }

        if (str_contains($key, 'blocked')) {
            throw new RedisException('blocked list');
        }

        $this->redis->set($key . 'blocked', '1');

        return $key;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $queueName): bool
    {
        $deleted = $this->redis->del($queueName);

        if (!is_bool($deleted)) {
            throw new RedisException('deleted failed');
        }

        return $deleted;
    }
}
