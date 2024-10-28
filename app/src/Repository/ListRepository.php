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
    public function dequeue(string $queueName): string
    {
        $value = $this->redis->lPop($queueName);

        if (!is_string($value)) {
            throw new RedisException('lPop failed');
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function length(string $queueName): int
    {
        $listLength = $this->redis->lLen($queueName);

        if (!is_int($listLength)) {
            throw new RedisException('lLen failed');
        }

        return $listLength;
    }

    /**
     * {@inheritDoc}
     */
    public function enqueueBatch(string $queueName, array $objectValues): int
    {
        $valueClosure = function (array $objectValues) {
            foreach ($objectValues as $objectValue) {
                yield $objectValue;
            }
        };

        $multi = $this->redis->multi();
        $countValues = 0;

        foreach ($valueClosure($objectValues) as $objectValue) {
            $multi->rPush($queueName, $this->jsonService->buildJson($objectValue));
            $countValues += 1;
        }

        if ($multi->exec() === false) {
            throw new RedisException('enqueueBatch failed');
        }

        return $countValues;
    }
}
