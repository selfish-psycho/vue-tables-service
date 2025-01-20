<?php

namespace App\Infrastructure\Services\Table\VueTable;

use App\Infrastructure\Contracts\Tables\ActionsInterface;
use App\Infrastructure\Contracts\Tables\RepositoryInterface;
use App\Infrastructure\Contracts\Tables\ServiceInterface;
use App\Infrastructure\Services\Table\VueTable\Actions\VueTableActions;
use App\Infrastructure\Services\Table\VueTable\Repository\VueTableRepository;

class VueTableService implements ServiceInterface
{
    public function __construct(
        private VueTableActions $actions,
        private VueTableRepository $repository
    )
    {
    }

    public function actions(): ActionsInterface
    {
        return $this->actions;
    }

    public function repository(): RepositoryInterface
    {
        return $this->repository;
    }
}
