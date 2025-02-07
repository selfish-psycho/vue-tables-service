<?php

namespace App\Infrastructure\Services\Table\VueTable\Controller;

use App\Infrastructure\Enums\Table\ServicesEnums;
use App\Infrastructure\Services\Table\TableService;
use App\Infrastructure\Services\Table\VueTable\Facades\VueTableExcelExport;
use Bitrix\Crm\Service\Container;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use CCrmOwnerType;
use PhpOffice\PhpSpreadsheet\Exception;

class VueTableController extends Controller
{
    /**
     * Метод обновляет значение по данным переданного массива DTO ячейки отчёта
     * @param array $cell
     * @return string JSON
     * @throws ArgumentException
     */
    public static function updateField(array $cell): string
    {
        try {
            Loader::includeModule("crm");

            $entityTypeId = $cell['entity_type_id'];
            $entityId = $cell['entity_id'];
            $field = $cell['field_code'];
            $value = $cell['value'];

            if (in_array($entityTypeId, CCrmOwnerType::GetAll())) {
                //TODO: реализовать обновление сущностей - сделок, контактов и тд
            } elseif (CCrmOwnerType::isPossibleDynamicTypeId($entityTypeId)) {
                $dynamic = Container::getInstance()->getFactory($entityTypeId);
                $item = $dynamic->getItem($entityId);

                $item->set($field, $value)->save();
            }
        } catch (\Exception $e) {
            return Json::encode([
                'success' => false,
                'data' => $e->getMessage()
            ]);
        }

        return Json::encode([
            'success' => true,
            'data' => $cell
        ]);
    }

    /**
     * Метод API экспорта отчёта в Excel
     * @param array $data
     * @return string JSON
     * @throws ArgumentException|Exception
     */
    public static function exportExcel(array $data): string
    {
        return VueTableExcelExport::export($data);
    }

    /**
     * Метод API удаления физического файла экспорта отчёта с сервера
     * @param array $data
     * @return string JSON
     * @throws ArgumentException
     */
    public static function deleteExcel(array $data): string
    {
        $filePath = $data['path'] ?: '';

        return VueTableExcelExport::deleteExcel($filePath);
    }

    /**
     * Метод API для асинхронного получения строк отчёта
     * @param array $data
     * @return string JSON
     * @throws ArgumentException
     */
    public static function getRows(array $data): string
    {
        $actions = TableService::create(ServicesEnums::VUE->value)->actions();

        return Json::encode($actions->getRows(base64_decode($data['data_class']), $data['params']));
    }
}
