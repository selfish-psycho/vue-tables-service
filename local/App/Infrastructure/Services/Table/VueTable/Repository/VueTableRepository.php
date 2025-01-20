<?php

namespace App\Infrastructure\Services\Table\VueTable\Repository;

//use App\Infrastructure\Contracts\Tables\ComponentClassInterface;
use App\Infrastructure\Contracts\Tables\RepositoryInterface;
use Bitrix\Main\LoaderException;
use Bitrix\Main\UI\Extension;
use Exception;

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

        <table style="border: 2px solid black">
            <thead>
            <tr v-for="headerAboveRow in headersAbove">
                <th
                        :style="header.name === 'empty' ? '' : 'border: 2px solid black'"
                        v-for="header in headerAboveRow"
                        :id=header.id
                        :colspan=header.length
                >
                    <span v-if="header.name !== 'empty'">
                        {{ header.name }}
                    </span>
                </th>
            </tr>

            <tr>
                <th
                        style="border: 2px solid black"
                        v-for="header in headers"
                        :id=header.id
                        :index=header.index
                >
                    {{ header.name }}
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in rows">
                <td
                        style="border: 2px solid black"
                        v-for="cell in row"
                >
                    {{ cell.value }}
                </td>
            </tr>
            </tbody>
        </table>

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
        Extension::load('ui.vue');

        if (!str_starts_with($appId, '#')) {
            $appId = '#'.$appId;
        }
        ?>

        <script>
            let App = BX.Vue.create({
                el: '<?=$appId?>',
                data: {
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
                methods: {
                    addRow(row) {
                        let newRow = [];

                        Object.keys(row).forEach(key => {
                            //Если передан многоуровневый хедер
                            if (typeof row[key] === 'object') {
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
                                            id: headerAboveCount+'',
                                            index: headerAboveCount,
                                            name: 'empty',
                                            length: headerCount-headerAboveCount,
                                        })
                                    }

                                    headerCount = ++headerCount+'';

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
                                                id: ++newHeaderPosition+'',
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
                                        value: row[key][column],
                                        index: cellIndex,
                                    })
                                })
                            }
                            //Если передан вид column: value
                            else {
                                //Добавляем заголовки первого уровня (основные) (для обычных)
                                let newHeaderPosition = this.headers.length;
                                let cellIndex;
                                let cellHeader = this.headers.find(header => header.name === key);

                                if (typeof cellHeader === 'undefined') {
                                    this.pushHeader(
                                        {
                                            id: ++newHeaderPosition+'',
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
                                    value: row[key],
                                    id: key,
                                })
                            }
                        })

                        this.includeRow(newRow)
                    },
                    includeRow(row) {
                        let newRow = [];

                        row.forEach(cell => {
                            //Добавляем заголовки первого уровня (основные) (для вложенных)
                            let newHeaderPosition = this.headers.length;
                            let cellIndex;
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
                                })
                            }
                        })

                        this.headers.push(header);
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
                        match(gettype($row)) {
                            'array' => json_encode($row, JSON_UNESCAPED_UNICODE),
                            'string' => json_decode($row, true) ? $row : throw new \InvalidArgumentException(json_last_error_msg()),
                            default => throw new \InvalidArgumentException('Неподдерживаемый тип строки таблицы')
                        }
                    ?>
                );
            </script>
        <?php
    }
}
