<?php

namespace App\Infrastructure\Contracts\Tables;

use Generator;

interface DataClassInterface
{
    /**
     * Метод отдаёт информацию для таблицы по строкам в виде массива строки или JSON-объекта строки
     * @return Generator
     */
    public function getTableRows(): Generator;
}