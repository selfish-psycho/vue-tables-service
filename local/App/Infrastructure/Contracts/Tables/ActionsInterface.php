<?php

namespace App\Infrastructure\Contracts\Tables;

use App\Infrastructure\Enums\Table\DataClassesEnum;

interface ActionsInterface
{
    public function init(string $appId, DataClassesEnum $dataClass, array $params = []);
    public function includeStyles(): self;
    public function setEditable(bool $editable = true): self;
}
