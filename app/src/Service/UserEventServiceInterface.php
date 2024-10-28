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
     * @return int|UserEventError
     */
    public function getListLength(): int|UserEventError;

    /**
     * @return UserEvent|UserEventError
     */
    public function remove(): UserEvent|UserEventError;
}
