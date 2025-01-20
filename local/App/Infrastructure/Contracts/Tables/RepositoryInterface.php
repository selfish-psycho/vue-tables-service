<?php

namespace App\Infrastructure\Contracts\Tables;

interface RepositoryInterface
{
    /**
     * Template Vue приложения (он передаётся в создании экземпляра Vue как строка в качестве параметра, все вопросы к битриксоидам)
     * @return string
     */
    public function getTemplate(): string;

    /**
     * JS скрипт Vue приложения
     * @param string $appId
     * @return void
     */
    public function getVueScript(string $appId): void;
}
