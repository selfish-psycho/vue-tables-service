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

$appId = 'app';

?>

    <div id="<?=$appId?>"></div>

<?php

//Инициализируем сервис
$service = TableService::create(ServicesEnums::VUE->value);
$service->actions()
    ->init($appId, DataClassesEnum::DEMO, $arResult)
    ->includeStyles()
    ->setEditable()
;
