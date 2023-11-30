<?php

namespace Advina\importExcel;

require_once '../vendor/autoload.php';
require_once '../products/products.php';

use Cassandra\Date;
use CProduct;
use CRest;
use PhpOffice\PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell;

class Service
{

    /**
     * @throws PhpSpreadsheet\Exception
     */
//    const IBLOCK_OFFERS = 26;
    public const iblockIdCatalog = 25;
    public const aviaColumn = [
        'deal_id' => 4,
        'blank_num' => 5,
        'last_name' => 6,
        'name' => 7,
        'second_name' => 8,
        'birth_date' => 9,
        'cost_price' => 10,
        'commission' => 11,
        'service_charge' => 12,
        'agency_services' => 13,
        'retention' => 14,
        'flight1' => 17,
        'flight_date1' => 18,
        'departure1' => 19,
        'arrival1' => 20,
        'class1' => 21,
        'flight2' => 22,
        'flight_date2' => 23,
        'departure2' => 24,
        'arrival2' => 25,
        'class2' => 26,
        'flight3' => 27,
        'flight_date3' => 28,
        'departure3' => 29,
        'arrival3' => 30,
        'class3' => 31,
        'flight4' => 32,
        'flight_date4' => 33,
        'departure4' => 34,
        'arrival4' => 35,
        'class4' => 36,
        'flight5' => 37,
        'flight_date5' => 38,
        'departure5' => 39,
        'arrival5' => 40,
        'class5' => 41,
        'flight6' => 42,
        'flight_date6' => 43,
        'departure6' => 44,
        'arrival6' => 45,
        'class6' => 46,
        'provider' => 47,
        'section_id' => 41,
    ];

    public const aviaClasses = [
        '1' => [
			'class'  => 21,
			'date'   => 18,
			'flight' => 17,
			'name'   => '1',
			'pole4'  => '423',
        ],
        '2' => [
			'class'  => 26,
			'date'   => 23,
			'flight' => 22,
			'name'   => '2',
			'pole4' => '431',

        ],
        '3' => [
            'class' => 31,
            'date' => 28,
            'flight' => 27,
            'name' => '3',
			'pole4' => '439',
        ],
        '4' => [
            'class' => 36,
            'date' => 33,
            'flight' => 32,
            'name' => '4',
			'pole4' => '447',
        ],
        '5' => [
            'class' => 41,
            'date' => 38,
            'flight' => 37,
            'name' => '5',
			'pole4' => '455',
        ],
        '6' => [
            'class' => 46,
            'date' => 43,
            'flight' => 42,
            'name' => '6',
			'pole4' => '463',
        ],
    ];

    public const railColumn = [
        'deal_id' => 1,
        'blank_num' => 2,
        'last_name' => 3,
        'name' => 4,
        'second_name' => 5,
        'birth_date' => 6,
        'cost_price' => 7,
        'commission' => 8,
        'service_charge' => 9,
        'agency_services' => 10,
        'retention' => 11,
        'train_num' => 14,
        'train_date' => 15,
        'departure' => 16,
        'arrival' => 17,
        'class' => 18,
        'provider' => 19,
        'section_id' => 45,
    ];

    public const AviaCities = [
        'el1' => [
            'el' => 19,
            'list' => 79,
            'name' => 'departure1',
        ],
        'el1_1' => [
            'el' => 20,
            'list' => 79,
            'name' => 'arrival1',
        ],
        'el2' => [
            'el' => 24,
            'list' => 79,
            'name' => 'departure2',
        ],
        'el2_2' => [
            'el' => 25,
            'list' => 79,
            'name' => 'arrival2',
        ],
        'el3' => [
            'el' => 29,
            'list' => 79,
            'name' => 'departure3',
        ],
        'el3_3' => [
            'el' => 30,
            'list' => 79,
            'name' => 'arrival3',
        ],
        'el4' => [
            'el' => 34,
            'list' => 79,
            'name' => 'departure4',
        ],
        'el4_4' => [
            'el' => 35,
            'list' => 79,
            'name' => 'arrival4',
        ],
        'el5' => [
            'el' => 39,
            'list' => 79,
            'name' => 'departure5',
        ],
        'el5_5' => [
            'el' => 40,
            'list' => 79,
            'name' => 'arrival5',
        ],
        'el6' => [
            'el' => 44,
            'list' => 79,
            'name' => 'departure6',
        ],
        'el6_6' => [
            'el' => 45,
            'list' => 79,
            'name' => 'arrival6',
        ],
    ];

    public const railwayData = [
        'el1' => [
            'el' => 16,
            'list' => 81,
            'name' => 'departure1',
        ],
        'el2' => [
            'el' => 17,
            'list' => 81,
            'name' => 'arrival1',
        ],
        'el3' => [
            'el' => 18,
            'list' => 85,
            'name' => 'wagonClass1',
        ],
    ];

//    public static function catchAthor() {
//        CRest::setLog([
//            'POST' => $_POST,
//        ], 'catchAthor_start');
//        CRest::setLog([
//            'server' => $_SERVER['REQUEST_METHOD'],
//        ], 'catchAthor_REQUEST_METHOD');
//        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['AUTH_ID'])) {
//            return $_POST['AUTH_ID'];
//        }
//    }

    public static function addNewContact()
    {
        CRest::setLog([
            'FILES' => $_FILES,
            'POST' => $_POST,
        ], 'addNewContact_start');

        $importExcel = $_FILES['import_file'];
        if ($importExcel['size'] !== 0) {

            // region Получение данных из Excel
            $importExcelFile = $importExcel['tmp_name'];
            $excel = IOFactory::load($importExcelFile);
            $sheet = $excel->getActiveSheet();
            $totalRows = $sheet->getHighestRow();
            $totalColumn = Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());

            for ($currentRow = $totalRows; $currentRow > 1 ; $currentRow--) {
                $i = 0;
                for ($cell = 1; $cell <= $totalColumn; $cell++) {
                    $currentCell = trim($sheet->getCell([$cell, $currentRow]));
                    if (empty(trim($currentCell))) {
                        $i++;
                    }
                }

                if ($i === $totalColumn) {
                    $totalRows--;
                }
            }

            CRest::setLog([
                '$importExcel' => $importExcel,
                '$importExcelFile' => $importExcelFile,
            ], 'addNewContact_import_2');
            // endregion

            #region Импорт данных
            for ($currentRow = 2; $currentRow <= $totalRows; $currentRow++) {
                CRest::setLog([
                    '$importExcel' => $importExcel,
                    '$importExcelFile' => $importExcelFile,
                ], 'addNewContact_import_iterator_1');

                #region Проверка авиа или ж/д
                $columnNum = '';
                $checkingData = '';
                $errString1 = '';
                $errString2 = '';
                $segmentNum = 0;

                if ($_POST['importType'] === 'avia') {
                    $columnNum = self::aviaColumn;
                    $checkingData = self::AviaCities;
                    $errString1 = ' аэропорт ';
                    $errString2 = ' сегмента ';
                } elseif ($_POST['importType'] === 'railway') {
                    $columnNum = self::railColumn;
                    $checkingData = self::railwayData;
                    $errString1 = 'а станция ';
                } else {
                    CRest::setLog([
                        '$columnNum' => $columnNum,
                    ], 'addNewContact_import_no_type');
                }
                #endregion

                #region сбор данных

                $sectionID = $columnNum['section_id'];
                $lastName = trim($sheet->getCell([$columnNum['last_name'], $currentRow]));
                $name = trim($sheet->getCell([$columnNum['name'], $currentRow]));
                $secondName = trim($sheet->getCell([$columnNum['second_name'], $currentRow]));
                $birthDate = $sheet->getCell([$columnNum['birth_date'], $currentRow]);
                $dealId = trim($sheet->getCell([$columnNum['deal_id'], $currentRow]));
                $blankNum = trim($sheet->getCell([$columnNum['blank_num'], $currentRow]));
                $costPrice = trim($sheet->getCell([$columnNum['cost_price'], $currentRow]));
                $commission = trim($sheet->getCell([$columnNum['commission'], $currentRow]));
                $serviceCharge = trim($sheet->getCell([$columnNum['service_charge'], $currentRow]));
                $agencyServices = trim($sheet->getCell([$columnNum['agency_services'], $currentRow]));
                $retention = trim($sheet->getCell([$columnNum['retention'], $currentRow]));
                $provider = trim($sheet->getCell([$columnNum['provider'], $currentRow]));

                $phpBirthDate = '';
                if (is_numeric($birthDate->getValue())) {
                    $phpBirthDate = date('d.m.Y', PhpSpreadsheet\Shared\Date::excelToTimestamp($birthDate->getValue()));
                }
                #endregion

                #region получение данных из списков и справочников
                $batches = [
                    'contact_list' => [
                        'method' => 'crm.contact.list',
                        'params' => [
                            'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'BIRTHDATE'],
                            'filter' => [
                                'NAME' => $name,
                                'LAST_NAME' => $lastName,
                                'SECOND_NAME' => $secondName,
                                'BIRTHDATE' => $phpBirthDate,
                            ],
                        ],
                    ],
                    'company_list' => [
                        'method' => 'crm.company.list',
                        'params' => [
                            'select' => ['ID', 'TITLE'],
                            'filter' => [
                                'TITLE' => $provider,
                            ],
                        ],
                    ],
//                    'classList' => [
//                        'method' => 'catalog.productPropertyEnum.list',
//                        'params' => [
//                            'filter' => [
//                                'iblockId' => 25,
//                                'productType' => 1,
//                            ],
//                        ],
//                    ],
                ];

                CRest::setLog([
                    '$batches' => $batches,
                ], 'batches_creator_1');

                foreach ($checkingData as $element) {
                    $elCell = trim($sheet->getCell([$element['el'], $currentRow]), ' ');
                    CRest::setLog([
                        '$elCell' => $elCell,
                    ], '$elCell_el_1');
                    if (!empty($elCell)) {
                        $batches[$element['name']] = [
                            'method' => 'lists.element.get',
                            'params' => [
                                'IBLOCK_TYPE_ID' => 'lists',
                                'IBLOCK_ID' => $element['list'],
                                'filter' => [
                                    'NAME' => $elCell,
                                ],
                            ],
                        ];
                    }
                }

                CRest::setLog([
                    '$batches' => $batches,
                ], 'batches_creator_2');

                $allData = CRest::callBatch($batches);
                #endregion

                #region Проверка корректности введённых данных
                $err = 0;
                $i = 0;
                foreach ($allData['result']['result'] as $k => $arr) {
                    $i++;
                    if (empty($arr)) {
                        if ($k === 'company_list') {
                            echo 'Некорретно введён поставщик. Строка ' . $currentRow . '.<br>';
                            $err++;
                        } elseif ($k === 'wagonClass1') {
                            if (array_key_exists($k, $batches)) {
                                $searchData = $batches[$k]['params']['filter']['NAME'];
                                echo 'Некорретно введён класс вагона' . $searchData . '. Строка ' . $currentRow . '.<br>';
                                $err++;
                            }
                        } elseif ($i > 3) {
                            if (array_key_exists($k, $batches)) {
                                $searchData = $batches[$k]['params']['filter']['NAME'];
                                $segmentNum = ceil(($i - 3) / 2);
                                if ($_POST['importType'] === 'railway') {
                                    $segmentNum = '';
                                }
                                echo 'Некорректно введен' . $errString1 . $searchData . $errString2 . $segmentNum . '. Строка ' . $currentRow . '. <br>';
                                $err++;
                            }
                        }
                    }
                }

//                Проверка класса авиабилета
                $classIds = [];
                $i = 0;
//                Верно ли введён класс
                if ($_POST['importType'] === 'avia') {
                    foreach (self::aviaClasses as $segment) {
                        $i++;
                        $avia_class = trim($sheet->getCell([$segment['class'], $currentRow]));
                        $avia_class_id = '';

                        if (!empty($avia_class)) {

                            $classList = CRest::call(
                                'catalog.productPropertyEnum.list',
                                [
                                    'filter' => [
                                        'propertyId' => $segment['pole4'],
                                    ],
                                ],
                            );

                            $avia_class_id = null;
                            foreach ($classList['result']['productPropertyEnums'] as $class) {
                                if ($class['value'] === $avia_class) {
                                    $avia_class_id = $class['id'];
                                    $classIds[$segment['name']] = $avia_class_id;

//                                CRest::setLog([
//                                    '$classIds' => $classIds,
//                                ], '$$classIds_1.1');
                                }
                            }

                            if (is_null($avia_class_id)) {
                                $segmentNum = $i;
                                $err++;
                                echo 'Не могу найти класс ' . $avia_class . $errString2 . $segmentNum . '. Строка ' . $currentRow . '. <br>';
                            }
                        }
                    }
//                CRest::setLog([
//                    '$classIds' => $classIds,
//                ], '$$classIds_1');


                    $segmentQuantity = count($classIds);
                    if ($segmentQuantity === 0) {
                        $err++;
                        echo 'Не хватает данных о сегменте. Строка ' . $currentRow . '. <br>';
                    }
                }

                #endregion

                #region Создание нового контакта

                if ($err === 0) {
                    if (!isset($allData['result']['result']['contact_list']) or
                        !is_array($allData['result']['result']['contact_list']) or
                        count($allData['result']['result']['contact_list']) === 0) {
                        CRest::setLog(['params' => [
                            'NAME' => $name,
                            'LAST_NAME' => $lastName,
                            'SECOND_NAME' => $secondName,
                            'BIRTHDATE' => $phpBirthDate,
                        ]], 'crm.contact.add');

                        $newContact = CRest::call(
                            'crm.contact.add',
                            [
                                'fields' => [
                                    'NAME' => $name,
                                    'LAST_NAME' => $lastName,
                                    'SECOND_NAME' => $secondName,
                                    'BIRTHDATE' => $phpBirthDate,
                                ],
                            ],
                        );

//                        CRest::setLog([
//                            '$newContacts' => $newContact['result'],
//                        ], '$newContacts');

                    }

                    #endregion


                    #region Ищем id контакта

                    $contactId = '';
                    if (isset($allData['result']['result']['contact_list']) and
                        is_array($allData['result']['result']['contact_list']) and
                        count($allData['result']['result']['contact_list']) > 0) {
                        $contactId = $allData['result']['result']['contact_list'][0]['ID'];
                    } elseif (!empty($newContact['result'])) {
                        $contactId = $newContact['result'];
                        CRest::setLog([
                            '$contactId' => $contactId,
                        ], '$contactId');
                    }
                    #endregion

//                 2. Создаём товар в каталоге
                    #region 2.1 Делаем общую переменную с входными параметрами

                    $companyId = 'CO_' . $allData['result']['result']['company_list'][0]['ID'];
                    $params = [
                        'fields' => [
                            'iblockId' => self::iblockIdCatalog,
                            'iblockSectionId' => $sectionID,
                            'name' => 'Билееееет',
                            'property191' => ['value' => $blankNum],
                            'property207' => ['value' => $companyId],
                            'property217' => ['value' => $contactId],
                            'property221' => ['value' => $costPrice],
                            'property223' => ['value' => $commission],
                            'property397' => ['value' => $serviceCharge],
                            'property399' => ['value' => $agencyServices],
                            'property401' => ['value' => $retention],
                        ]
                    ];
                    #endregion

                    #region              2.2 Добавляем поля для авиа


                    if ($_POST['importType'] === 'avia') {

//                    Добавляем авиаполя

                        $vars = [
                            1 => [
                                'pole1' => 'property417',
                                'pole2' => 'property419',
                                'pole3' => 'property421',
                                'pole4' => 'property423',
                                'pole5' => 'property497',
                                'departure' => 'departure1',
                                'arrivalCity' => 'arrival1',
                            ],
                            2 => [
                                'pole1' => 'property425',
                                'pole2' => 'property427',
                                'pole3' => 'property429',
                                'pole4' => 'property431',
                                'pole5' => 'property499',
                                'departure' => 'departure2',
                                'arrivalCity' => 'arrival2',
                            ],
                            3 => [
                                'pole1' => 'property433',
                                'pole2' => 'property435',
                                'pole3' => 'property437',
                                'pole4' => 'property439',
                                'pole5' => 'property501',
                                'departure' => 'departure3',
                                'arrivalCity' => 'arrival3',
                            ],
                            4 => [
                                'pole1' => 'property441',
                                'pole2' => 'property443',
                                'pole3' => 'property445',
                                'pole4' => 'property447',
                                'pole5' => 'property503',
                                'departure' => 'departure4',
                                'arrivalCity' => 'arrival4',
                            ],
                            5 => [
                                'pole1' => 'property449',
                                'pole2' => 'property451',
                                'pole3' => 'property453',
                                'pole4' => 'property455',
                                'pole5' => 'property505',
                                'departure' => 'departure5',
                                'arrivalCity' => 'arrival5',
                            ],
                            6 => [
                                'pole1' => 'property457',
                                'pole2' => 'property459',
                                'pole3' => 'property461',
                                'pole4' => 'property463',
                                'pole5' => 'property507',
                                'departure' => 'departure6',
                                'arrivalCity' => 'arrival6',
                            ]
                        ];



                        CRest::setLog([
                            '$segmentQuantity' => $segmentQuantity,
                        ], '$activeSegments_creator_after_avia');

                        for ($i = 1; $i <= $segmentQuantity; $i++) {

                            $flightDate = $sheet->getCell([self::aviaClasses[$i]['date'], $currentRow]);
                            $phpFlightDate = '';
                            if (is_numeric($flightDate->getValue())) {
                                $phpFlightDate = date('d.m.Y', PhpSpreadsheet\Shared\Date::excelToTimestamp($flightDate->getValue()));
                            }

                            $params['fields'][$vars[$i]['pole2']]['value'] = trim($sheet->getCell([self::aviaClasses[$i]['flight'], $currentRow]));
                            $params['fields'][$vars[$i]['pole1']]['value'] = $allData['result']['result'][$vars[$i]['departure']][0]['ID'];
                            $params['fields'][$vars[$i]['pole3']]['value'] = $phpFlightDate;
                            $params['fields'][$vars[$i]['pole4']]['value'] = $classIds[$i];
                            $params['fields'][$vars[$i]['pole5']]['value'] = $allData['result']['result'][$vars[$i]['arrivalCity']][0]['ID'];

                            CRest::setLog([
                                '$params' => $params,
                            ], '$activeSegments_creator_inFor');
                        }
                        CRest::setLog([
                            '$params' => $params,
                        ], '$activeSegments_creator_after_avia');
                        #endregion

                        #region                2.3 Добавляем поля для ж/д

                    } elseif ($_POST['importType'] === 'railway') {

//                        Добавляем ж/д поля

                        $trainDate = $sheet->getCell([$columnNum['train_date'], $currentRow]);
                        $phpTrainDate = '';
                        if (is_numeric($trainDate->getValue())) {
                            $phpTrainDate = date('d.m.Y', PhpSpreadsheet\Shared\Date::excelToTimestamp($trainDate->getValue()));
                        }
                        $params['fields']['property407']['value'] = $phpTrainDate;
                        $params['fields']['property409']['value'] = trim($sheet->getCell([$columnNum['train_num'], $currentRow]));
                        $params['fields']['property413']['value'] = $allData['result']['result']['departure1'][0]['ID'];
                        $params['fields']['property495']['value'] = $allData['result']['result']['arrival1'][0]['ID'];
                        $params['fields']['property555']['value'] = $allData['result']['result']['wagonClass1'][0]['ID'];
                    }
                    CRest::setLog([
                        '$params' => $params,
                    ], '$activeSegments_creator_after_railway');
                    #endregion


                    #region 2.4 Создаём товар в товарном каталоге

                    $newGood = CRest::call(
                        'catalog.product.add',
                        $params,
                    );
                    #endregion

//                3. Привязываем товар к сделке
                    #region 3.1 Создаём название и цену продукта
                    $product_id = $newGood['result']['element']['id'];

                    $data = CRest::callBatch(
                        [
                            'fields' => [
                                'method' => 'crm.product.fields',
                                'params' => [],
                            ],
                            'product' => [
                                'method' => 'crm.product.get',
                                'params' => ['id' => $product_id],
                            ],
                            'section' => [
                                'method' => 'crm.productsection.get',
                                'params' => ['id' => '$result[product][SECTION_ID]'],
                            ],
                            'sku' => [
                                'method' => 'catalog.product.list',
                                'params' => [
                                    'select' => ['id', 'iblockId', '*'],
                                    'filter' => ['iblockId' => IBLOCK_OFFERS, 'parentId' => $product_id],
                                    'order' => ['id' => 'ASC'],
                                ],
                            ],
                        ],
                    );

                    $result_name = null;
                    $result_price = null;
                    $prod_class = CProduct::getByData($data);

                    if (isset($prod_class)) {
                        $result_price = $prod_class->getPrice();
                        $result_name = $prod_class->getName();
                    }
                    #endregion

                    #region 3.2 Привязываем товар к сделке

                    $newDealRow = CRest::call(
                        'crm.item.productrow.add',
                        [
                            'fields' => [
                                'ownerId' => $dealId,
                                'ownerType' => 'D',
                                'productId' => $newGood['result']['element']['id'],
                                'productName' => $result_name,
                                'price' => $result_price,
                                'quantity' => 1,
                                'customized' => 'N',
                                'measureCode' => 796,
                            ]
                        ],
                    );
                    #endregion


                } else {
                    echo 'Строка ' . $currentRow . ' не загружена. <br>';
                }
            }
            #endregion
        }
    }
}
