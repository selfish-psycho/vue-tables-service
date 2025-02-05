<?php

namespace App\Infrastructure\Enums\Table;

enum CellTypeEnum: string
{
    case DATE = 'date';
    case STRING = 'text';
    case NUMBER = 'number';
    case SELECT = 'select';
    case BOOL = 'checkbox';
    case NOT_EDITABLE = 'not-editable';
}
