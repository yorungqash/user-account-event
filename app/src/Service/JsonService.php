<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;

final readonly class JsonService implements JsonServiceInterface
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function isJson(mixed $data): bool
    {
        if (!is_string($data) || !json_validate($data)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function buildJson(mixed $data): string
    {
        return $this->serializer->serialize($data, 'json');
    }

    /**
     * {@inheritDoc}
     */
    public function buildObject(string $json, string $objectName): object
    {
        return $this->serializer->deserialize($json, $objectName, 'json');
    }
}
