<?php

namespace App\Infrastructure\Services\Table\VueTable\Repository;

use App\Infrastructure\Contracts\Tables\RepositoryInterface;
use Bitrix\Main\LoaderException;
use Bitrix\Main\UI\Extension;
use CMain;
use Exception;
use InvalidArgumentException;

class VueTableRepository implements RepositoryInterface
{
    /**
     * Метод получает шаблон Vue-компонента, актуальный для всех новых таблиц
     * @return string
     */
    public function getTemplate(): string
    {
        ob_start();
        ?>
        <div>
            <input
                    type='submit'
                    class='transition export'
                    @click="excelExport"
                    value="Экспорт в Excel"
            >
            <table>
                <thead>
                <tr
                        class="headerAboveRow"
                        v-for="headerAboveRow in headersAbove"
                >
                    <th
                            class="headerAbove"
                            v-for="header in headerAboveRow"
                            :id=header.id
                            :colspan=header.length
                            :index=header.index
                    >
                            <span v-if="header.name !== 'empty'">
                                {{ header.name }}
                            </span>
                    </th>
                </tr>

                <tr class="headerRow">
                    <th
                            class="header"
                            v-for="header in headers"
                            :id=header.id
                            :index=header.index
                    >
                            <span v-if="header.index !== 0">
                                {{ header.name }}
                            </span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr
                        class="row"
                        v-for="(row, rowIndex) in rows"
                        :id="rowIndex"
                >
                    <td
                            class="cell"
                            v-for="cell in row"
                            v-if="cell.index !== 0"
                            :field_code="cell.field_code"
                            :entity_type_id="cell.entity_type_id"
                            :entity_id="cell.entity_id"
                    >
                        <input
                                v-model="cell.value"
                                v-if="
                                    editingRows.findIndex(editingRow => editingRow.index === rowIndex) !== -1 &&
                                    cell.value!=='Ошибка' &&
                                    cell.type!=='not-editable'
                                "
                                :type="cell.type"
                        >

                        <span v-else>{{ cell.value }}</span>
                    </td>
                    <td
                            class="cell-edit"
                            v-else-if="cell.index === 0"
                    >
                        <label
                                class="toggler-wrapper styler"
                        >
                            <input
                                    type="checkbox"
                                    :row="rowIndex"
                                    @click="editRow(rowIndex)"
                            >
                            <span class="toggler-slider">
                                <span class="toggler-knob">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>
<!--                                    TODO: Брать изображение из UI Extension Icons, а не по ссылке-->
                                </span>
                            </span>
                        </label>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Метод получает скрипт Vue-приложения
     * @param string $appId
     * @return void
     * @throws LoaderException
     * @throws Exception
     */
    public function getVueScript(string $appId): void
    {
        Extension::load('jquery');
        Extension::load('ui.vue');
        Extension::load("ui.notification");

        if (!str_starts_with($appId, '#')) {
            $appId = '#' . $appId;
        }
        ?>

        <script>
            let App = BX.Vue.create({
                el: '<?=$appId?>',
                data: {
                    editingRows: [],
                    editable: false,
                    isEditModeOn: false,
                    headersAbove: [ //уровень верхнего заголовка
                        [ //массив заголовков на строке заголовков
                            // { //объект одного заголовка
                            //     index: 1,
                            //     id: '1',
                            //     name: 'test',
                            //     length: 1,
                            // }
                        ]
                    ],
                    headers: [
                        // {
                        //     index: 1,
                        //     id: '1',
                        //     name: 'test',
                        // }
                    ],
                    rows: [
                        // [ //Первая строка
                        //     { //Первый элемент
                        //         id: 'apple',
                        //         value: 'apple',
                        //         index: 1
                        //     },
                        // ],
                        // [ //Вторая строка
                        //     { //Первый элемент
                        //         id: 'orange',
                        //         value: 'orange',
                        //         index: 1
                        //     }
                        // ]
                    ]
                },
                template: `<?=$this->getTemplate()?>`,
                watch: {
                    editable: function(to, from) {
                        if (to === true) {
                            //Добавляем колонку для переключателей режима редактирования
                            this.headers.unshift(
                                {
                                    id: 0,
                                    index: 0,
                                    name: 'empty',
                                }
                            )

                            //Добавляем headerAbove для столбца переключателей при необходимости
                            //TODO:определять уровень заголовка по уровню вложенности (имею в виду [0])
                            if (this.headersAbove[0].length > 0) {
                                this.headersAbove[0].unshift({
                                    id: 0,
                                    index: 0,
                                    name: 'empty',
                                    length: 1,
                                })
                            }

                            //Заполняем созданный столбец переключателями
                            this.rows.forEach(row => {
                                row.unshift({
                                    id: '',
                                    value: '',
                                    index: 0,
                                    field_code: '',
                                    entity_type_id: '',
                                    entity_id: ''
                                })
                            })
                        } else if (from === true) {
                            //Удаляем колонку для переключателей режима редактирования
                            let indexToRemove = this.headers.findIndex(el => el.index === 0);
                            if (indexToRemove !== -1) {
                                this.headers.splice(indexToRemove, 1);
                            }

                            //Удаляем headerAbove столбца переключателей при необходимости
                            //TODO:определять уровень заголовка по уровню вложенности (имею в виду [0])
                            if (this.headersAbove[0].length > 0) {
                                let indexToRemove = this.headersAbove[0].findIndex(el => el.index === 0);
                                if (indexToRemove!== -1) {
                                    this.headersAbove[0].splice(indexToRemove, 1);
                                }
                            }

                            //Удаляем из строк переключатели
                            this.rows.forEach(row => {
                                let indexToRemove = row.findIndex(el => el.index === 0);
                                if (indexToRemove!== -1) {
                                    row.splice(indexToRemove, 1);
                                }
                            })
                        }
                    }
                },
                methods: {
                    addRow(row) {
                        let newRow = [];

                        Object.keys(row).forEach(key => {
                            //Если передан многоуровневый хедер
                            if (this.isMultilevelHeader(row[key])) {
                                //Проходимся по всем заголовкам таблицы
                                let headerCount = this.headers.length;

                                //Если заголовок выше других заголовков
                                //TODO:определять уровень заголовка по уровню вложенности (имею в виду [0])
                                let headerAboveObj = this.headersAbove[0].find(header => header.name === key);
                                if (typeof headerAboveObj === 'undefined') {
                                    //Определяем позицию относительно существующих на этом уровне заголовков
                                    let headerAboveCount = 0;

                                    //Проходимся по всем уже существующим заголовкам на этом уровне
                                    //TODO:определять уровень заголовка по уровню вложенности (имею в виду [0])
                                    this.headersAbove[0].forEach(headerAbove => {
                                        headerAboveCount += headerAbove.length;
                                    })

                                    //Если уже есть заголовки на уровне и они не покрывают все заголовки ниже
                                    if (headerCount !== headerAboveCount) {
                                        //Отступаем на разницу количества заголовков нижнего уровня и суммы длин заголовков текущего уровня
                                        //TODO:определять уровень заголовка по уровню вложенности (имею в виду [0])
                                        this.headersAbove[0].push({
                                            id: headerAboveCount + '',
                                            index: headerAboveCount,
                                            name: 'empty',
                                            length: headerCount - headerAboveCount,
                                        })
                                    }

                                    headerCount = ++headerCount + '';

                                    //Определяем длину для colspan
                                    let headerLength = Object.keys(row[key]).length
                                    //Создаём объект заголовка
                                    let headerAbove = {
                                        id: headerCount,
                                        index: Number(headerCount),
                                        name: key,
                                        length: headerLength,
                                    };
                                    //Добавляем заголовок вверху (только для одного уровня, надо расширить)
                                    //TODO:определять уровень заголовка по уровню вложенности (имею в виду [0])
                                    this.headersAbove[0].push(headerAbove)
                                }

                                //Добавляем новые хедеры нижнего уровня
                                let newHeaderPosition =
                                    typeof headerAboveObj === 'undefined' ?
                                        this.headers.length :
                                        headerAboveObj.index;

                                let cellIndex;

                                Object.keys(row[key]).forEach(column => {
                                    let cellHeader = this.headers.find(
                                        header =>
                                            header.name === column &&
                                            header.index >= newHeaderPosition
                                    );

                                    if (typeof cellHeader === 'undefined') {
                                        this.pushHeader(
                                            {
                                                id: ++newHeaderPosition + '',
                                                index: newHeaderPosition,
                                                name: column,
                                            }
                                        )
                                        cellIndex = newHeaderPosition;
                                    } else {
                                        cellIndex = cellHeader.index;
                                    }

                                    //Добавляем в созданный столбец значение ячейки
                                    newRow.push({
                                        id: column,
                                        index: cellIndex,
                                        entity_type_id: row[key][column].entity_type_id,
                                        entity_id: row[key][column].entity_id,
                                        field_code: row[key][column].field_code,
                                        value: row[key][column].value,
                                        type: row[key][column].type,
                                    })
                                })
                            }
                            //Если передан вид column: {cell}
                            else {
                                //Добавляем заголовки первого уровня (основные) (для обычных)
                                let newHeaderPosition = this.headers.length;
                                let cellIndex;
                                let cellHeader = this.headers.find(header => header.name === key);

                                if (typeof cellHeader === 'undefined') {
                                    this.pushHeader(
                                        {
                                            id: ++newHeaderPosition + '',
                                            index: newHeaderPosition,
                                            name: key,
                                        }
                                    )
                                    cellIndex = newHeaderPosition;
                                } else {
                                    cellIndex = cellHeader.index;
                                }

                                //Собираем объект ячейки и отправляем её в массив строки на отрисовку
                                newRow.push({
                                    index: cellIndex,
                                    id: key,
                                    entity_type_id: row[key].entity_type_id,
                                    entity_id: row[key].entity_id,
                                    field_code: row[key].field_code,
                                    value: row[key].value,
                                    type: row[key].type,
                                })
                            }
                        })

                        this.includeRow(newRow);
                    },
                    includeRow(row) {
                        let newRow = [];

                        row.forEach(cell => {
                            //Добавляем заголовки первого уровня (основные) (для вложенных)
                            let newHeaderPosition = this.headers.length;
                            let cellHeader = this.headers.find(header => header.index === cell.index);

                            if (typeof cellHeader === 'undefined') {
                                this.pushHeader(
                                    {
                                        id: ++newHeaderPosition + '',
                                        index: newHeaderPosition,
                                        name: cell.id,
                                    }
                                )
                            }
                        })

                        this.headers.forEach(header => {
                            let addingCell = row.find(cell => cell.index === header.index);
                            let cellPosition = header.index;
                            if (typeof addingCell === 'undefined') {
                                newRow.push({
                                    index: ++cellPosition,
                                    value: null,
                                    id: header.id,
                                    type: 'not-editable'
                                });
                            } else {
                                newRow.push(addingCell);
                            }
                        })

                        this.rows.push(newRow);
                    },
                    pushHeader(header) {
                        this.rows.forEach(row => {
                            while (row.length < header.index) {
                                let index = row.length;
                                row.push({
                                    index: ++index,
                                    value: null,
                                    id: index,
                                    type: 'not-editable'
                                })
                            }
                        })

                        this.headers.push(header);
                    },
                    isMultilevelHeader(cell) {
                        for (let key in cell) {
                            if (typeof cell[key] === 'object') {
                                return true;
                            }
                        }

                        return false;
                    },
                    objectLength(obj) {
                        return Object.keys(obj).length;
                    },
                    toggleEditable(isEditable) {
                        this.editable = isEditable;
                    },
                    editRow(rowIndex) {
                        let editingRowIndex = this.editingRows.findIndex(row => row.index === rowIndex);

                        //Если false -> true
                        if (editingRowIndex === -1) {
                            this.editingRows.push({
                                index: rowIndex,
                                data: JSON.parse(JSON.stringify( //Иначе привязывается ссылка на переменную
                                    this.rows[rowIndex].filter(
                                        //Сохраняем старые значения только для тех, у кого они есть и могут быть изменены
                                        cell => Boolean(cell.value) && cell.type !== 'not-editable'
                                    )
                                ))
                            });
                            //Если true -> false
                        } else {
                            //проверка изменённых данных
                            this.editingRows[editingRowIndex].data.forEach(cell => {
                                let diffIndex = this.rows[rowIndex].findIndex(newCell =>
                                    newCell.index === cell.index &&
                                    newCell.value === cell.value
                                )

                                if (diffIndex === -1) {
                                    let editedCell = this.rows[rowIndex].find(newCell =>
                                        newCell.index === cell.index
                                    )

                                    $.ajax({
                                        type: "POST",
                                        url: "/api/v1/vue/table/update",
                                        dataType: "json",
                                        contentType: "application/json",
                                        data: JSON.stringify(editedCell),
                                        success: function (response) {
                                            if (response.success) {
                                                BX.UI.Notification.Center.notify({
                                                    content: "Изменения успено сохранены",
                                                    autoHideDelay: 2000
                                                });
                                            } else {
                                                BX.UI.Notification.Center.notify({
                                                    content: "Что-то пошло не так",
                                                    autoHideDelay: 2000
                                                });
                                                console.log(response)
                                            }
                                        },
                                        error: function (jqXHR, exception) {
                                            console.log(jqXHR)

                                            if (jqXHR.status === 0) {
                                                alert('Not connect. Verify Network.');
                                            } else if (jqXHR.status == 404) {
                                                alert('Requested page not found (404).');
                                            } else if (jqXHR.status == 500) {
                                                alert('Internal Server Error (500).');
                                            } else if (exception === 'parsererror') {
                                                alert('Requested JSON parse failed.');
                                            } else if (exception === 'timeout') {
                                                alert('Time out error.');
                                            } else if (exception === 'abort') {
                                                alert('Ajax request aborted.');
                                            } else {
                                                alert('Uncaught Error. ' + jqXHR.responseText);
                                            }
                                        }
                                    })
                                }
                            })

                            this.editingRows.splice(editingRowIndex, 1);
                        }
                    },
                    excelExport() {
                        //TODO: Реализовать AJAX-запрос на генерацию excel файла
                    }
                }
            })

            BX.Vue.createApp(App).mount('<?=$appId?>');
        </script>
        <?php
    }

    /**
     * Метод вызывает JS метод AddRow для переданной строки таблицы
     * @param $row
     * @return void
     */
    public function addRow($row): void
    {
        ?>
        <script>
            App.addRow(
                <?=
                    match (gettype($row)) {
                        'array' => json_encode($row, JSON_UNESCAPED_UNICODE),
                        'string' => json_decode($row, true) ? $row : throw new \InvalidArgumentException(json_last_error_msg()),
                        default => throw new \InvalidArgumentException('Неподдерживаемый тип строки таблицы')
                    }
                    ?>
            );
        </script>
        <?php
    }

    /**
     * Метод подключает Vue-приложению файл переданного стиля
     * @param string $path путь к css файлу относительно корня проекта
     * @return void
     */
    public function addStyle(string $path): void
    {
        if (!preg_match('/^\/include/', $path)) {
            throw new InvalidArgumentException('Путь до стиля должен начинаться с \'/include\'');
        }

        (new CMain())->SetAdditionalCSS($path);
    }

    /**
     * @param bool $editable
     * @return self
     */
    public function setEditable(bool $editable): self
    {
        ?>
        <script>
            App.editable = <?=$editable? 'true' : 'false'?>;
        </script>
        <?php

        return $this;
    }
}
