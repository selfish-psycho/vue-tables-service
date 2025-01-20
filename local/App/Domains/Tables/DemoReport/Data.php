<?php

namespace App\Domains\Tables\DemoReport;

use App\Infrastructure\Contracts\Tables\DataClassInterface;
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
                'fruits' => 'apple',
                'vegetables' => 'tomato',
                'art' => ['artist' => 'Aivazovsky', 'name' => '9 wave'],
            ],
            [
                'vegetables' => 'potato',
                'fruits' => 'orange',
                'pet' => ['name' => 'Snowball', 'type' => 'cat'],
                'art' => ['artist' => 'Malevich', 'name' => '9 Black square'],
            ],
            [
                'color' => 'red',
                'fruits' => 'banana',
                'music' => ['composer' => 'Tchaikovsky', 'name' => 'Swan Lake', 'year' => '1877'],
                'pet' => ['name' => 'Buddy', 'type' => 'dog'],
            ],
            [
                'furniture' => 'chair'
            ]
        ];

        foreach ($ar as $row) {
            yield json_encode($row, JSON_UNESCAPED_UNICODE);
        }
    }
}
