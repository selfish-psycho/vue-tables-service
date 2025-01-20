<?php

use App\Infrastructure\Enums\Table\DataClassesEnum;
use App\Infrastructure\Enums\Table\ServicesEnums;
use App\Infrastructure\Services\Table\TableService;
use Bitrix\Main\UI\Extension;

/**
 * @var $arResult
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

//Подключаем расширение 'BX.Vue'
Extension::load('ui.vue');

//Создаем пространство для определения Vue-компонента
$tableDivId = 'app';
?>

<div id="<?=$tableDivId?>"></div>

<?php

//Определяем класс сбора информации для таблицы
$dataClass = DataClassesEnum::DEMO->value;

//Инициализируем сервис
$service = TableService::create(ServicesEnums::VUE->value);
//Инициализируем Vue-приложение
$service->actions()->init($tableDivId, (new $dataClass($arResult)));
