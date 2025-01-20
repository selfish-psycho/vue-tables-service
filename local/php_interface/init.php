<?php

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require(dirname(__DIR__).'/vendor/autoload.php');

try {
    //Регистрируем пространство имен сервисов
    Loader::registerNamespace(
        "App",
        Loader::getDocumentRoot() . "/local/App"
    );
} catch (LoaderException $e) {
    //Log error
}
