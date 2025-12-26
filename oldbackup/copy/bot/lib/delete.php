<?php

defined('BASE_DIR') || die('NO ACCESS');

function delete_dir($dirPath)
{
    if (!is_dir($dirPath)) {
        throw new Exception("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            delete_dir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function delete_folder($path)
{
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file)
            delete_folder(realpath($path) . '/' . $file);
        return rmdir($path);
    } else
        if (is_file($path) === true)
            return unlink($path);
    return false;
}