<?php

use App\Infrastructure\Services\Table\VueTable\Controller\VueTableController;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    //region VueTableService
    $routes
        ->prefix('api/v1/vue/table')
        ->name('vue_table')
        ->group(function (RoutingConfigurator $routes) {
            $routes->post('update', function (HttpRequest $request) {
                return VueTableController::updateField($request->getJsonList()->getValues());
            });
            $routes->post('export', function (HttpRequest $request) {
                return VueTableController::exportExcel($request->getJsonList()->getValues());
            });
            $routes->post('delete', function (HttpRequest $request) {
                return VueTableController::deleteExcel($request->getJsonList()->getValues());
            });
            $routes->post('rows/get', function (HttpRequest $request) {
                return VueTableController::getRows($request->getJsonList()->getValues());
            });
        })
    ;
    //endregion VueTableService
};
