<?php

defined('BASE_DIR') || die('NO ACCESS');

function copy_dir($src, $dest)
{
    foreach (scan_dir($src) as $file) {
        if (!is_readable($src . '/' . $file)) continue;
        if (is_dir($src . '/' . $file)) {
            mkdir($dest . '/' . $file);
            copy_dir($src . '/' . $file, $dest . '/' . $file);
        } else {
            copy($src . '/' . $file, $dest . '/' . $file);
        }
    }
}


/*
 * copy_dir('HOME DIRECTORY','PAST DIRECTORY');
*/
