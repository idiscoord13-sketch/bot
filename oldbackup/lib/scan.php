<?php


if (!function_exists('scan_dir')) {
    function scan_dir($directory)
    {
        return array_diff(scandir($directory), array('..', '.'));
    }
}