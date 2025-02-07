<?php

namespace App\Infrastructure\Services\Table\VueTable\Facades;

use Bitrix\Disk\Driver;
use Bitrix\Disk\Internals\FolderTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use CFile;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VueTableExcelExport
{
    /**
     * Массив стилей таблицы по умолчанию
     * @var array
     */
    private static array $style = [
        //Шрифт
        'font' => [
            'name' => 'Times New Roman',
            'size' => '14',
        ],
        //Перенос текста, выравнивание
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ],
        //Границы
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    /**
     * Метод создаёт Excel файл по переданным данным и возвращает пути для скачивания и удаления файла
     * @param array $data
     * @return string JSON
     * @throws ArgumentException|Exception
     */
    public static function export(array $data): string
    {
        //region Создание файла xlsx

        //Создаем экземпляр класса электронной таблицы
        $spreadsheet = new Spreadsheet();

        //Получаем текущий активный лист
        $sheet = $spreadsheet->getActiveSheet();

        //Определяем начальные и конечные координаты и указатель
        $firstLetter = $x = 'A';
        $firstNumber = $y = 1;
        $lastLetter = 'A';
        $lastNumber = 1;
        $headerFirstNumber = $headerLastNumber = 1;

        //Проходимся по всем заголовкам заголовков
        foreach ($data['headersAbove'] as $headersAboveRow) {
            foreach ($headersAboveRow as $headerAbove) {
                //Пишем заголовок в ячейку
                $cell = $x . $y;

                //Записываем заголовок в ячейку
                $sheet->getCell($cell)
                    ->setValueExplicit($headerAbove['name'] === 'empty' ? '' : $headerAbove['name'])
                ;

                //Объединяем ячейки по длине заголовка
                for ($i = 1; $i < $headerAbove['length']; $i++) {
                    $x++;
                }
                //Объединяем ячейки
                $sheet->mergeCells($cell . ':' . $x . $y);
                $sheet
                    ->getStyle($cell . ':' . $x . $y)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK)
                ;

                //Продвигаем указатель на следующую ячейку
                $x++;
            }
            //Сбрасываем указатель на начало следующего ряда
            $x = $firstLetter;
            $y++;
            $headerLastNumber++;
        }

        //Проходимся по заголовкам
        foreach ($data['headers'] as $header) {
            //Определяем максимальную букву (последний столбец) UPD: strlen потому, что Z >AA (поменял empty($sLastLetter) на условие длин строк)
            if (strlen($lastLetter) < strlen($x) || $lastLetter < $x) {
                $lastLetter = $x;
            }

            $sheet->getCell($x . $y)
                ->setValueExplicit($header['name'] === 'empty' ? '' : $header['name'])
                ->getStyle()->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK)
            ;

            $x++;
        }

        //Сбрасываем указатель на начало следующего ряда
        $x = $firstLetter;
        $y++;

        //Проходимся по данным
        foreach ($data['rows'] as $row) {
            foreach ($row as $cell) {
                $coordinate = $x . $y;

                //Записываем значение в ячейку
                $sheet->getCell($coordinate)
                    ->setValueExplicit($cell['value'] ?: '')
                    ->getStyle()->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                //Продвигаем указатель на следующую ячейку
                $x++;
            }
            //Сбрасываем указатель на начало следующего ряда
            $x = $firstLetter;
            $y++;
        }

        $lastNumber = --$y; //Одна строка добавляется в цикле выше - удаляем

        //Set table columns width
        for (
            $letter = $firstLetter;
            $letter <= $lastLetter || strlen($letter) < strlen($lastLetter);
            $letter++
        ) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        //Headers style
        $sheet
            ->getStyle($firstLetter . $headerFirstNumber . ':' . $lastLetter . $headerLastNumber)
            ->applyFromArray(array_merge(static::$style, [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'C2C2C2']
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THICK,
                    ],
                ],
            ]))
        ;

        //Data style
        $sheet
            ->getStyle($firstLetter . ++$headerLastNumber . ':' . $lastLetter . $lastNumber)
            ->applyFromArray(array_merge(static::$style, [
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THICK,
                    ],
                ],
            ]))
        ;

        //endregion Создание файла xlsx

        //region Сохранение файла xlsx
        //Формируем файл xlsx
        $obWriter = new Xlsx($spreadsheet);

        //Сохраняем
        //WARNING: Не удалять эту папку!!!!! Пускай падает в указанную папку files. Файл всё равно удалится автоматически, так что практически она и так будет всегда пуста
        $sPath = Application::getDocumentRoot() . '/upload/reports_export/';
        if (!is_dir($sPath)) {
            mkdir($sPath, 0775);
        }

        $sName = $sPath . 'report_export_' . date('d.m.Y H:i:s') . '.xlsx'; //Сохраняем файл

        //Сохраняем файл, который только что создали, чтобы отдать ссылкой на скачивание
        try {
            $obWriter->save($sName);
        } catch (WriterException $e) {
            return Json::encode(['error' => 'Cannot save the file $sName: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }

        try {
            $sSavedFile = static::saveXlsxFile($sName);
        } catch (ArgumentException|LoaderException|SystemException $e) {
            return Json::encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }

        if ( is_array(json_decode($sSavedFile, true)) ) {
            return $sSavedFile;
        }
        return Json::encode(['error' => $sSavedFile], JSON_UNESCAPED_UNICODE);
        //endregion Сохранение файла xlsx
    }

    /**
     * Метод сохраняет файл на диске пользователя Битрикс.
     * Отдаёт JSON массив с:
     * ID файла на диске;
     * путём до физического фала для удаления через обратную AJAX функцию;
     * путём до скачивания файла для подмены url,
     * @param $sFile
     * @return string JSON
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     */
    private static function saveXlsxFile($sFile): string
    {
        Loader::includeModule('disk');

        $arFileData = [];
        $obStorage = Driver::getInstance()->getStorageByUserId(CurrentUser::get()->getId());

        if (!$obStorage) {
            return Json::encode(['error' => 'Cannot drive the obStorage']);
        }

        $obFolder = $obStorage->getChild([
            '=NAME' => 'Файлы экспорта',
            'TYPE' => FolderTable::TYPE_FOLDER
        ]);

        if (!$obFolder) {
            $obFolder = $obStorage->addFolder([
                'NAME' => 'Файлы экспорта',
                'CREATED_BY' => CurrentUser::get()->getId() ?: 1,
            ]);
        }

        $arFileData = CFile::MakeFileArray($sFile);

        try {
            $obFile = $obFolder->uploadFile($arFileData, [
                'CREATED_BY' => 1
            ]);
        } catch (ArgumentException $e) {
            return Json::encode(['error' => 'Cannot upload file: ' . $e->getMessage()]);
        }

        return (string)Json::encode([
            'success' => true,
            'fileId' => $obFile->getId(),
            'fullPath' => $sFile,
            'downloadPath' => substr($sFile, strpos($sFile, '/upload')),
        ]);
    }

    /**
     * Метод удаляет файл из директории, где он создался
     * @param string $sFileName Путь физического файла
     * @return string
     * @throws ArgumentException
     */
    public static function deleteExcel(string $sFileName): string
    {
        if (empty($sFileName)) {
            return Json::encode([
                'success' => false,
                'message' => 'FileName is empty'
            ]);
        }

        unlink($sFileName);

        return Json::encode(['success' => true]);
    }
}
