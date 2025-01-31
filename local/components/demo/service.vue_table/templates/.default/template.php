<?php

use App\Infrastructure\Enums\Table\DataClassesEnum;
use App\Infrastructure\Enums\Table\ServicesEnums;
use App\Infrastructure\Services\Table\TableService;

/**
 * @var $arResult
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>

    <div id="app"></div>

<?php

//Определяем класс сбора информации для таблицы
$dataClass = DataClassesEnum::DEMO->value;

//Инициализируем сервис
$service = TableService::create(ServicesEnums::VUE->value);
$service->actions()
    ->init('app', (new $dataClass($arResult)))
    ->includeStyles()
    ->setEditable()
;
