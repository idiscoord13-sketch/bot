<?php

use Gettext\Loader\PoLoader;

function __(string $context, array $replace = [], string $language = 'fa_IR')
{
    $poLoader = new PoLoader();
    $translations = $poLoader->loadString(file_get_contents(LANGUAGE_DIR . '/' . $language . '.po'));
    $data = $translations->toArray();
    foreach ($data['translations'] as $datum) {
        if ($datum['original'] == $context) {
            $string = $datum['translation'];
            foreach ($replace as $search => $value) {
                $string = str_replace($search, $value, $string);
            }
            return $string;
        }
    }
    return $context;
}