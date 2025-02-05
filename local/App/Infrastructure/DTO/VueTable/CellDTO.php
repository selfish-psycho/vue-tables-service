<?php

namespace App\Infrastructure\DTO\VueTable;

use App\Infrastructure\Enums\Table\CellTypeEnum;

/**
 * Класс объектов ячеек для таблицы сервиса VueTable
 */
class CellDTO
{
    public function __construct(
        private readonly int    $entity_type_id,
        private readonly int    $entity_id,
        private readonly string $field_code,
        private readonly string $value,
        private readonly CellTypeEnum $type,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'entity_type_id' => $this->entity_type_id,
            'entity_id' => $this->entity_id,
            'field_code' => $this->field_code,
            'value' => $this->value,
            'type' => $this->type->value,
        ];
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
