<?php

defined('BASE_DIR') || die('NO ACCESS');

require CONTROLL_DIR . '/FilterWords.php';

function getRandomeString($number) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $number; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

function convertNumbers($srting, $toPersian = true)
{
    $en_num = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $fa_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
    if ($toPersian) return str_replace($en_num, $fa_num, $srting);
    else return str_replace($fa_num, $en_num, $srting);
}

/*
$filter = new FilterWords(['Words']);
$warning_word = $filter->getWarningWords();
$filter->wordsfilter('YOUR WORD FOR FILTER');
$filter->filterwords('YOUR WORD FOR FILTER','WORD CHANGED');
*/