<?php

namespace App\Infrastructure\Services\Table;

use App\Infrastructure\Contracts\BaseServiceInterface;
use App\Infrastructure\Contracts\Tables\ServiceInterface;
use App\Infrastructure\Enums\Table\ServicesEnums;
use App\Infrastructure\Services\Table\VueTable\VueTableService;
use App\Shared\DI\Container;

class TableService implements BaseServiceInterface
{
    public static function create(int $typeId): ServiceInterface
    {
        return match ($typeId) {
            ServicesEnums::VUE->value => (new Container())->get(VueTableService::class),
        };
    }
}
