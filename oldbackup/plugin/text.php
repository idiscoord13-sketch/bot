<?php

/**
 * @param $string
 * @return array|string|string[]|null
 */
function remove_emoji_from_text($string)
{
    // Match Enclosed Alphanumeric Supplement
    $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
    $clear_string = preg_replace($regex_alphanumeric, '', $string);

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clear_string = preg_replace($regex_symbols, '', $clear_string);

    // Match Emoticons
    $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clear_string = preg_replace($regex_emoticons, '', $clear_string);

    // Match Transport And Map Symbols
    $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clear_string = preg_replace($regex_transport, '', $clear_string);

    // Match Supplemental Symbols and Pictographs
    $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
    $clear_string = preg_replace($regex_supplemental, '', $clear_string);

    // Match Miscellaneous Symbols
    $regex_misc = '/[\x{2600}-\x{26FF}]/u';
    $clear_string = preg_replace($regex_misc, '', $clear_string);

    // Match Dingbats
    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    $clear_string = preg_replace($regex_dingbats, '', $clear_string);

    return $clear_string;
}

/**
 * @param $string
 * @return mixed
 */
if (!function_exists('is_persian')) {
    function is_persian($string)
    {
        return apply_filters('filter_is_persian', !preg_match('/^[^\x{600}-\x{6FF}]+$/u', str_replace("\\\\", "", $string)));
//        return apply_filters('filter_is_persian', preg_match('/^[ـ .؟پچجحخهعغفقثصضشسیبلاتنمکگوئدذرآزطظژؤإأءًٌٍَُِّ\s]+$/u', remove_emoji_from_text('ا' . $string)));
//        return apply_filters('filter_is_persian', !preg_match('/[a-z A-Z]+/', $string));
    }
}

 function getAllLatinNames()
{
    global $link;
    $results = $link->get_result("SELECT name FROM `user_latin_names` WHERE   `status` = 'done'");
    $names = array_map(fn($row) => $row->name, $results);
    return $names;
}

function decodeFont($inputText) {
    foreach (FONTS as $style => $characters) {
        // Create the mapping from the current style to normal characters
        $replace = array_combine(mb_str_split($characters), mb_str_split(FONTS['normal']));
        $inputText = strtr($inputText, $replace);
    }

    return $inputText;
}

if (!function_exists('is_english')) {
    function is_english($string)
    {
        $string = decodeFont($string);
        return apply_filters('filter_is_english', preg_match('/[a-zA-Z]+/', $string));
    }
}

add_filter('send_massage_text', function ($text) {
    return tr_num($text, 'fa', '.');
});

/**
 * @param string $string
 * @param array $replace
 * @return string
 */
if (!function_exists('__replace__')) {
    function __replace__(string &$string, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $string = str_replace($key, $value, $string);
        }
        return $string;
    }
}


/**
 * @param $text
 * @return array|string|string[]
 */
function number_to_english( $text )
{
    return tr_num( $text, 'en', '.' );
}
