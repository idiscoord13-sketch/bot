<?php

/**
 * @param $str
 * @return string
 */
function string_encode($str): string
{
    $encode = new Base2n(5);
    return $encode->encode($str);
}

/**
 * @param $str_encode
 * @return string|null
 */
function string_decode($str_encode): ?string
{
    $encode = new Base2n(5);
    return $encode->decode($str_encode);
}