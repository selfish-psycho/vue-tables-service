<?php

namespace App\Infrastructure\Services\Table\VueTable\Actions;

use App\Infrastructure\Contracts\Tables\ActionsInterface;
use App\Infrastructure\Contracts\Tables\DataClassInterface;
use App\Infrastructure\Contracts\Tables\ServiceInterface;
use App\Infrastructure\Enums\Table\ServicesEnums;
use App\Infrastructure\Services\Table\TableService;
use Bitrix\Main\Loader;
use RuntimeException;

class VueTableActions implements ActionsInterface
{
    private ServiceInterface $service;

    /**
     * Метод определяет DOM-узел для инициализации Vue-приложения и сохраняет класс описания сбора данных для таблицы
     * @param string $appId
     * @param DataClassInterface $dataClass
     * @return self
     */
    public function init(string $appId, DataClassInterface $dataClass): self
    {
        //Определяем Vue-приложение
        $this->service = TableService::create(ServicesEnums::VUE->value);
        $this->service->repository()->getVueScript($appId);

        //Определяем источник данных
        try {
            foreach ($dataClass->getTableRows() as $row) {
                $this->service->repository()->addRow($row);
            }
        } catch (\Exception $e) {
            $this->service->repository()->throwError($e->getMessage());
        }

        return $this;
    }

    /**
     * Метод подключает файлы стилей из директории '/local/App/Infrastructure/Services/Table/VueTable/Assets/styles'
     * @return $this
     */
    public function includeStyles(): self
    {
        $this->validateInit();

        $stylesPath = '/local/css/services/VueTable/styles';
        $path = Loader::getDocumentRoot() . $stylesPath;

        if (!is_dir(Loader::getDocumentRoot() . $stylesPath)) {
            throw new RuntimeException('У сервиса не существует стилей!');
        }

        foreach (array_diff((array)scandir($path), ["..", "."]) as $style) {
            $this->service->repository()->addStyle($stylesPath . '/' . $style);
        }

        return $this;
    }

    /**
     * @param bool $editable
     * @return self
     */
    public function setEditable(bool $editable = true): self
    {
        $this->validateInit();

        //Включаем возможность редактирования
        $this->service->repository()->setEditable($editable);

        return $this;
    }

    /**
     * Метод проверяет инициализировано ли Vue-приложение.
     * Используется перед вызовом методов взаимодействия с ним.
     * @return void
     */
    private function validateInit(): void
    {
        if (empty($this->service)) {
            throw new RuntimeException('Сервис не инициализирован!');
        }
    }
}
