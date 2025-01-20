<?php

namespace App\Infrastructure\Contracts\Tables;

interface ServiceInterface
{
    public function actions(): ActionsInterface;

    public function repository(): RepositoryInterface;
}
