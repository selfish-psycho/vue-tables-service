<?php

namespace App\Infrastructure\Services\Table\VueTable\Controller;

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use CCrmOwnerType;
use Exception;

class VueTableController extends Controller
{
    public static function updateField(array $cell)
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
        } catch (Exception $e) {
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
}
