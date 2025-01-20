<?php

namespace App\Infrastructure\Contracts\Tables;

interface ActionsInterface
{
    /**
     * Метод определяет DOM-узел для инициализации Vue-приложения и сохраняет класс описания сбора данных для таблицы
     * @param string $appId
     * @param DataClassInterface $dataClass
     * @return void
     */
    public function init(string $appId, DataClassInterface $dataClass): void;
}