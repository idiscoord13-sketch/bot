<?php

defined('BASE_DIR') || die('NO ACCESS');

require SRC_DIR . '/ReadXLSX.php';
require SRC_DIR . '/XLSXWriter_BuffererWriter.php';
require SRC_DIR . '/XlsxWriter.php';


#READ
/*
if ($xlsx = ReadXLSX::parse($filename)) {

    // Produce array keys from the array values of 1st array element
    $header_values = $rows = [];

    foreach ($xlsx->rows() as $k => $r) {
        if ($k === 0) {
            $header_values = $r;
            continue;
        }
        $rows[] = array_combine($header_values, $r);
    }

    $data = [];
    $numbers = [];
    $i = 0;
    $day = data();
    foreach ($rows as $id => $row) {

    }
}
*/

#WRITE
/*
$writer = new XlsxWriter();
foreach ($query as $item) {
    $height = 20;
    $data[] = [
        // DATA
    ];
}
$writer->writeSheetHeader('Sheet1',
    [
        // DATA TYPE
        // ARRAY id => 'string','integer'
    ],
    [
        'widths' => [20, 50, 20, 20, 20, 20, 20, 20, 20,],
        'border' => 'left,right,top,bottom',
        'halign' => 'center',
        'align' => 'center',
        'valign' => 'center',
        'freeze_rows' => 1,
        'freeze_columns' => 1,
    ]
);
foreach ($data as $row)
    $writer->writeSheetRow('Sheet1', $row, ['height' => $height, 'halign' => 'center']);
$filename = uniqid() . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer->writeToStdOut();
*/