<?php

namespace App\Infrastructure\Contracts\Tables;

interface ActionsInterface
{
    public function init(string $appId, DataClassInterface $dataClass);
    public function includeStyles(): self;
    public function setEditable(bool $editable = true): self;
}