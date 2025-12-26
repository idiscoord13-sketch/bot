<?php


function encode_zip($path_dir, $zip_created)
{
    $zip = new ZipArchive();
    if ($zip->open($zip_created, ZipArchive::CREATE) === TRUE) {
        $dir = opendir($path_dir);
        while ($file = readdir($dir)) {
            if (is_file($path_dir . $file)) {
                $zip->addFile($path_dir . $file, $file);
            }
        }
        $zip->close();
    }
}

function decode_zip($file, $path_dir)
{
    $zip = new ZipArchive;
    $zip->open($file);
    $zip->extractTo($path_dir);
    $zip->close();
}