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
 * Decodes the encoded token and extracts the user ID.
 *
 * @param string $encoded_str
 * @return string|null
 */
function string_decode($encoded_str): ?string
{
    $encode = new Base2n(5);
    $decoded_data = $encode->decode($encoded_str);

    // Extract the user ID from the decoded data
    // Since the current time is 10 digits long
    $user_id_str = substr($decoded_data, 10);

    return $user_id_str !== false ? $user_id_str : null;
}

function string_decodeOld($str_encode): ?string
{
    $encode = new Base2n(5);
    return $encode->decode($str_encode);
}