<?php

namespace Advina\importExcelContacts;

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
    public const CONTACT_TYPE = 'Пассажиры';
    public const conatctColumns = [
        'last_name' => 1,
        'name' => 2,
        'second_name' => 3,
        'lat_last_name' => 4,
        'lat_name' => 5,
        'birth_date' => 6,
        'citizenship' => 7,
        'document_type' => 8,
        'document_number' => 9,
        'int_pass_num' => 10,
        'int_pass_validity' => 11,
        'issue_date' => 12,
    ];
    public const B24_EXCEL_COLUMNS = [
        'LAST_NAME' => 'Фамилия',
        'NAME' => 'Имя',
        'SECOND_NAME' => 'Отчество',
        'BIRTHDATE' => 'Дата рождения',
        'UF_CRM_1548676399' => 'Гражданство',
        'UF_CRM_1548673994' => 'Тип документа',
        'UF_CRM_1548675457' => 'Номер документа',
    ];

    public static function checkingDate ($date) {
        $d = \DateTime::createFromFormat("d.m.Y", $date);
        return $d && $d->format("d.m.Y") === $date;
    }
    public static function addNewContacts() {
        CRest::setLog([
            'FILES' => $_FILES,
            'POST' => $_POST,
        ], 'addNewContact_start');
        $importExcel = $_FILES['import_file'];

        if ($importExcel['size'] !== 0) {
            CRest::setLog([
                '$importExcel' => $importExcel,
            ], '$importExcel');
//            region Получение данных из Excel
            $importExcelFile = $importExcel['tmp_name'];
            $excel = IOFactory::load($importExcelFile);
            $sheet = $excel->getActiveSheet();
            $totalRows = $sheet->getHighestRow();
            $totalColumn = Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());

//           region Проверка на пустые строки. Если строка пустая удаляем из общего кол-ва
            for ($currentRow = $totalRows; $currentRow > 18; $currentRow--) {
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
//            endregion

            $i = 0; //Счётчик для новых контактов
            $j = 0; //Счётчик для апдейта контактов
            $err = 0; //Счётчик ошибок
            $newContactsArr = [];
            $updateContactsArr = [];

            for ($currentRow = 19; $currentRow <= $totalRows; $currentRow++) {
                $lastName = trim($sheet->getCell([self::conatctColumns['last_name'], $currentRow]));
                $name = trim($sheet->getCell([self::conatctColumns['name'], $currentRow]));
                $secondName = trim($sheet->getCell([self::conatctColumns['second_name'], $currentRow]));
                $latLastName = trim($sheet->getCell([self::conatctColumns['lat_last_name'], $currentRow]));
                $lat_name = trim($sheet->getCell([self::conatctColumns['lat_name'], $currentRow]));
                $birthDate = trim($sheet->getCell([self::conatctColumns['birth_date'], $currentRow]));
                $citizenship = trim($sheet->getCell([self::conatctColumns['citizenship'], $currentRow]));
                $documentType = trim($sheet->getCell([self::conatctColumns['document_type'], $currentRow]));
                $documentNumber = preg_replace('%[^A-Za-zА-Яа-я0-9]%', '', $sheet->getCell([self::conatctColumns['document_number'], $currentRow]));
//                $documentNumber = trim($sheet->getCell([self::conatctColumns['document_number'], $currentRow]));
                $intPassNum = trim($sheet->getCell([self::conatctColumns['int_pass_num'], $currentRow]));
                $intPassValidity = trim($sheet->getCell([self::conatctColumns['int_pass_validity'], $currentRow]));
                $issueDate = trim($sheet->getCell([self::conatctColumns['issue_date'], $currentRow]));

                CRest::setLog([
                    '$lastName' => $lastName,
                    '$name' => $name,
                    '$secondName' => $secondName,
                    '$latLastName' => $latLastName,
                    '$lat_name' => $lat_name,
                    '$birthDate' => $birthDate,
                    '$citizenship' => $citizenship,
                    '$documentType' => $documentType,
                    '$documentNumber' => $documentNumber,
                    '$intPassNum' => $intPassNum,
                    '$intPassValidity' => $intPassValidity,
                    '$issueDate' => $issueDate,
                ], 'excel_info_parsed');

//                region Приведение к формату даты
                $phpBirthDate = '';
                $phpIssueDate = '';
                if (is_numeric($birthDate)) {
                    $phpBirthDate = date('d.m.Y', PhpSpreadsheet\Shared\Date::excelToTimestamp($birthDate));
                } elseif (self::checkingDate($birthDate)) {
                    $phpBirthDate = $birthDate;
                }

                if (!empty($issueDate) and is_numeric($issueDate)) {
                    $phpIssueDate = date('d.m.Y', PhpSpreadsheet\Shared\Date::excelToTimestamp($issueDate));
                } elseif (self::checkingDate($issueDate)) {
                    $phpIssueDate = $issueDate;
                }
//                endregion

//                region Получение данных из Битрикс
                $allData = CRest::callBatch([
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
                    'contact_fields' => [
                        'method' => 'crm.contact.fields',
                    ],
                    'contact_type_list' => [
                        'method' => 'crm.status.list',
                        'params' => [
                            'order' => [
                                'SORT' => 'ASC',
                            ],
                            'filter' => [
                                'ENTITY_ID' => 'CONTACT_TYPE',
                                'NAME' => self::CONTACT_TYPE,
                            ],
                        ],
                    ],
                ]);
                CRest::setLog([
                    '$allData' => $allData,
                ], '$allData');

//                endregion

//                region Получение значений для списочных полей
                $contactTypeId = null;
                if (isset($allData['result']['result']['contact_type_list'][0])) {
                    $contactTypeId = $allData['result']['result']['contact_type_list'][0]['STATUS_ID'];
                    CRest::setLog([
                        '$contactTypeId' => $contactTypeId,
                    ], '$contactTypeId');
                }

                $citizenshipId = null;
                foreach ($allData['result']['result']['contact_fields']['UF_CRM_1548676399']['items'] as $citizenshipField) {
                    if ($citizenshipField['VALUE'] === $citizenship) {
                        $citizenshipId = $citizenshipField['ID'];
                        break;
                    }
                }

                $docTypeId = null;
                foreach ($allData['result']['result']['contact_fields']['UF_CRM_1548673994']['items'] as $docType) {
                    if ($docType['VALUE'] === $documentType) {
                        $docTypeId = $docType['ID'];
                        CRest::setLog([
                            '$docTypeId' => $docTypeId,
                        ], '$docTypeId');
                    }
                }
//                endregion
                $passangerInfo = [
                    'LAST_NAME' => $lastName,
                    'NAME' => $name,
                    'SECOND_NAME' => $secondName,
                    'BIRTHDATE' => $phpBirthDate,
                    'UF_CRM_1548676399' => $citizenshipId,
                    'UF_CRM_1548673994' => $docTypeId,
                    'UF_CRM_1548675457' => $documentNumber,
                ];
                CRest::setLog([
                    '$passangerInfo' => $passangerInfo,
                ], '$passangerInfo');
//                region Проверка строк на соответствие
//                Проверка заполнения полей
                foreach ($passangerInfo as $k => $v) {
                    if (empty($v)) {
                        if ($k === 'UF_CRM_1548676399' or $k === 'UF_CRM_1548673994') {
                            if (empty($citizenship) or empty($documentType)) {
                                $err++;
                                echo 'Поле ' . self::B24_EXCEL_COLUMNS[$k] . " не заполнено. Строка {$currentRow}.<br>";
                                continue;
                            }
                            $err++;
                            echo 'Поле ' . self::B24_EXCEL_COLUMNS[$k] . " заполнено некорректно. Строка {$currentRow}.<br>";
                            continue;
                        }
                        $err++;
                        echo 'Поле ' . self::B24_EXCEL_COLUMNS[$k] . " не заполнено. Строка {$currentRow}.<br>";
                    }
                }

//                Проверка возраста
                if (strtotime("-14 year") > strtotime($phpBirthDate) and $docTypeId == 43105) {
//                    $err++;
                    echo "Пассажиру больше 14 лет. Тип документа должен быть паспорт. Строка {$currentRow}.<br>";
                }
//                endregion
                if ($err === 0 and !isset($allData['result']['result']['contact_list']) or !is_array($allData['result']['result']['contact_list']) or count($allData['result']['result']['contact_list']) === 0) {
                    $passangerInfo['TYPE_ID'] = $contactTypeId; //Тип контакта
                    $passangerInfo['UF_CRM_1548676812'] = $latLastName; //Фамилия латиницей
                    $passangerInfo['UF_CRM_1548676835'] = $lat_name; //Имя латиницей
                    $passangerInfo['UF_CRM_5E170D790DFD6'] = $intPassNum; //Номер заграничного паспорта РФ
                    $passangerInfo['UF_CRM_5E170D7946395'] = $intPassValidity; //Срок действия заграничного паспорта РФ
                    $passangerInfo['UF_CRM_1548676321'] = $phpIssueDate; //Дата выдачи
                    CRest::setLog([
                        '$passangerInfo' => $passangerInfo,
                    ], '$passangerInfo_contact_add');

                    $newContactsArr[$i] = $passangerInfo;
                    $i++;

                } elseif ($err === 0 and isset($allData['result']['result']['contact_list']) or is_array($allData['result']['result']['contact_list']) or count($allData['result']['result']['contact_list']) > 0) {
                    $contactId = $allData['result']['result']['contact_list'][0]['ID'];
                    $updateContactsArr[$j] = [
                        'id' => $contactId,
                        'fields' => [
                            'UF_CRM_1548676812' => empty($latLastName) ? null : $latLastName,
                            'UF_CRM_1548676835' => empty($lat_name) ? null : $lat_name,
                            'UF_CRM_1548676399' => $citizenshipId,
                            'UF_CRM_1548673994' => $docTypeId,
                            'UF_CRM_1548675457' => $documentNumber,
                            'UF_CRM_5E170D790DFD6' => empty($intPassNum) ? null : $intPassNum,
                            'UF_CRM_5E170D7946395' => empty($intPassValidity) ? null : $intPassValidity,
                            'UF_CRM_1548676321' => empty($phpIssueDate) ? null : $phpIssueDate,
                        ],
                    ];
                    $j++;
                }
            }

            if ($err === 0) {
                if (count($newContactsArr) > 0) {
                    foreach ($newContactsArr as $contact) {
                        $newContact = CRest::call(
                            'crm.contact.add',
                            [
                                'fields' => $contact,
                            ],
                        );
                        CRest::setLog([
                            '$newContact' => $newContact,
                        ], '$newContact_contact_add');
                    }
                }
                if (count($updateContactsArr) > 0) {
                    foreach ($updateContactsArr as $updContact) {
                        $updateContact = CRest::call(
                            'crm.contact.update',
                            $updContact,
                        );
                    }
                }
            }

        }
    }
}
