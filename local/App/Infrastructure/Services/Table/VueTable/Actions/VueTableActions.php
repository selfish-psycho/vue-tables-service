<?php

namespace App\Infrastructure\Services\Table\VueTable\Actions;

use App\Infrastructure\Contracts\Tables\ActionsInterface;
use App\Infrastructure\Contracts\Tables\DataClassInterface;
use App\Infrastructure\Enums\Table\ServicesEnums;
use App\Infrastructure\Services\Table\TableService;

class VueTableActions implements ActionsInterface
{
    /**
     * Метод определяет DOM-узел для инициализации Vue-приложения и сохраняет класс описания сбора данных для таблицы
     * @param string $appId
     * @param DataClassInterface $dataClass
     * @return void
     */
    public function init(string $appId, DataClassInterface $dataClass): void
    {
        //Определяем Vue-приложение
        $service = TableService::create(ServicesEnums::VUE->value);
        $service->repository()->getVueScript($appId);

        //Определяем источник данных
        foreach ($dataClass->getTableRows() as $row) {
            $service->repository()->addRow($row);
        }
    }
}
