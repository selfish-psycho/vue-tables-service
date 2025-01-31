<?php

use App\Infrastructure\Services\Table\VueTable\Controller\VueTableController;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    //region VueTableService
    $routes
        ->prefix('api/v1/vue')
        ->name('vue_table')
        ->group(function (RoutingConfigurator $routes) {
            $routes->post('table/update', function (HttpRequest $request) {
                return VueTableController::updateField($request->getJsonList()->getValues());
            });
        })
    ;
    //endregion VueTableService
};