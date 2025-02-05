<?php

namespace App\Domains\Tables\DemoReport;

use App\Infrastructure\Contracts\Tables\DataClassInterface;
use App\Infrastructure\DTO\VueTable\CellDTO;
use App\Infrastructure\Enums\Table\CellTypeEnum;
use Generator;

/**
 * Класс - пример работы с сервисом
 */
class Data implements DataClassInterface
{
    /**
     * Массив параметров, необходимых для сбора данных в таблице, может быть предан в actions()->init()
     * @var array
     */
    private array $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }
    
    public function getTableRows(): Generator
    {
        $ar = [
            [
                'fruits' => (new CellDTO(
                    entity_type_id: 158, //ID типа сущности (например, ID инфоблока)
                    entity_id: 1, //ID элемента
                    field_code: 'TITLE', //Код для запроса на редактирование
                    value: 'apple', //Значение для отображения в таблице
                    type: CellTypeEnum::STRING, //Тип поля для полей редактирования
                ))->toArray(),
                'vegetables' => (new CellDTO(
                    entity_type_id: 159,
                    entity_id: 1,
                    field_code: 'TITLE',
                    value: 'tomato',
                    type: CellTypeEnum::STRING,
                ))->toArray(),
                'art' => [
                    'artist' => (new CellDTO(
                        entity_type_id: 160,
                        entity_id: 1,
                        field_code: 'UF_ARTIST',
                        value: 'Aivazovsky',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                    'name' => (new CellDTO(
                        entity_type_id: 160,
                        entity_id: 1,
                        field_code: 'UF_ART_NAME',
                        value: '9 wave',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                ],
            ],
            [
                'vegetables' => (new CellDTO(
                    entity_type_id: 159,
                    entity_id: 2,
                    field_code: 'TITLE',
                    value: 'potato',
                    type: CellTypeEnum::STRING,
                ))->toArray(),
                'fruits' => (new CellDTO(
                    entity_type_id: 158,
                    entity_id: 2,
                    field_code: 'TITLE',
                    value: 'orange',
                    type: CellTypeEnum::STRING,
                ))->toArray(),
                'pet' => [
                    'name' => (new CellDTO(
                        entity_type_id: 161,
                        entity_id: 1,
                        field_code: 'UF_PET_NAME',
                        value: 'Snowball',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                    'type' => (new CellDTO(
                        entity_type_id: 161,
                        entity_id: 1,
                        field_code: 'UF_PET_TYPE',
                        value: 'cat',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                ],
                'art' => [
                    'artist' => (new CellDTO(
                        entity_type_id: 160,
                        entity_id: 2,
                        field_code: 'UF_ARTIST',
                        value: 'Malevich',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                    'name' => (new CellDTO(
                        entity_type_id: 160,
                        entity_id: 2,
                        field_code: 'UF_ART_NAME',
                        value: 'Black square',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                ],
            ],
            [
                'color' => (new CellDTO(
                    entity_type_id: 163,
                    entity_id: 3,
                    field_code: 'UF_COLOR',
                    value: 'red',
                    type: CellTypeEnum::STRING,
                ))->toArray(),
                'fruits' => (new CellDTO(
                    entity_type_id: 158,
                    entity_id: 3,
                    field_code: 'TITLE',
                    value: 'banana',
                    type: CellTypeEnum::STRING,
                ))->toArray(),
                'music' => [
                    'composer' => (new CellDTO(
                        entity_type_id: 162,
                        entity_id: 1,
                        field_code: 'UF_AUTHOR',
                        value: 'Tchaikovsky',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                    'name' => (new CellDTO(
                        entity_type_id: 162,
                        entity_id: 1,
                        field_code: 'TITLE',
                        value: 'Swan Lake',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                    'year' => (new CellDTO(
                        entity_type_id: 162,
                        entity_id: 1,
                        field_code: 'UF_RELEASE',
                        value: 1877,
                        type: CellTypeEnum::NUMBER,
                    ))->toArray(),
                ],
                'pet' => [
                    'name' => (new CellDTO(
                        entity_type_id: 161,
                        entity_id: 2,
                        field_code: 'UF_PET_NAME',
                        value: 'Buddy',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                    'type' => (new CellDTO(
                        entity_type_id: 161,
                        entity_id: 2,
                        field_code: 'UF_PET_TYPE',
                        value: 'dog',
                        type: CellTypeEnum::STRING,
                    ))->toArray(),
                ],
            ],
            [
                'furniture' => (new CellDTO(
                    entity_type_id: 164,
                    entity_id: 1,
                    field_code: 'TITLE',
                    value: 'Chair',
                    type: CellTypeEnum::STRING,
                ))->toArray(),
            ]
        ];

        foreach ($ar as $row) {
            yield json_encode($row, JSON_UNESCAPED_UNICODE);
        }
    }
}
