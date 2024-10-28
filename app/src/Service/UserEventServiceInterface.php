<?php

declare(strict_types=1);

namespace App\Service;

use App\Event\UserEvent;
use App\ValueObject\UserEventError;

interface UserEventServiceInterface
{
    /**
     * @param array $data
     * @return UserEvent[]
     */
    public function build(array $data): array;

    /**
     * @param UserEvent[] $userEvents
     * @return int|UserEventError
     */
    public function add(array $userEvents): int|UserEventError;

    /**
     * @param string $queueName
     * @return UserEvent|UserEventError
     */
    public function remove(string $queueName): UserEvent|UserEventError;

    /**
     * @return string|UserEventError
     */
    public function getRandomList(): string|UserEventError;
    /**
     * @param string $queueName
     * @return bool|UserEventError
     */
    public function deleteList(string $queueName): bool|UserEventError;
}
