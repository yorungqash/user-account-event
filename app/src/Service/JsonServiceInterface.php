<?php

declare(strict_types=1);

namespace App\Service;

interface JsonServiceInterface
{
    /**
     * @param mixed $data
     * @return bool
     */
    public function isJson(mixed $data): bool;

    /**
     * @param mixed $data
     * @return string
     */
    public function buildJson(mixed $data): string;

    /**
     * @param string $json
     * @param string $objectName
     * @return object
     */
    public function buildObject(string $json, string $objectName): object;
}
