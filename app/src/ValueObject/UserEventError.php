<?php

namespace App\ValueObject;

final readonly class UserEventError
{
    public function __construct(
        public string $message,
    ) {
    }
}
