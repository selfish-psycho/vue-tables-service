<?php

namespace App\Infrastructure\Contracts;

interface BaseServiceInterface
{
    /**
     * Method create service by key.
     * @param int $typeId  ID сервиса.
     * @return mixed
     */
    public static function create(int $typeId): mixed;
}
