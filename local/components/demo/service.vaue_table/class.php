<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Contract\Controllerable;

/**
 * Класс компонента для вызова сервиса. Можно использовать обычные страницы
 */
class DemoComponent extends CBitrixComponent implements Controllerable
{
    public function configureActions(): array
    {
        return [];
    }

    public function executeComponent(): void
    {
        $this->arResult['ENTITY_TYPE_ID'] = $this->arParams['ENTITY_TYPE_ID'];
        $this->arResult['ENTITY_ID'] = $this->arParams['ENTITY_ID'];

        $this->includeComponentTemplate();
    }
}
