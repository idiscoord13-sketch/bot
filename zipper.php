<?php
/**
 * Smart Bot Backup Script
 * Excludes the 'files' directory and large log files.
 */

$zip_file = 'bot_source_code.zip';
$path = __DIR__;
$exclude_folder = 'files'; // نام پوشه‌ای که نمی‌خواهی در زیپ باشد

$zip = new ZipArchive();

if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($path) + 1);

            // ۱. بررسی برای حذف پوشه files
            if (strpos($relativePath, $exclude_folder . DIRECTORY_SEPARATOR) === 0 || $relativePath === $exclude_folder) {
                continue;
            }

            // ۲. حذف فایل زیپ فعلی و لاگ‌های سنگین برای کم شدن حجم
            if ($relativePath !== $zip_file && !strpos($relativePath, 'error_log')) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    $zip->close();
    
    if (file_exists($zip_file)) {
        echo "<h3>✅ فایل پشتیبان با موفقیت ساخته شد!</h3>";
        echo "<p>پوشه <b>$exclude_folder</b> با موفقیت فیلتر شد.</p>";
        echo "<a href='$zip_file' style='padding:10px; background:green; color:white; text-decoration:none; border-radius:5px;'>📥 همین حالا دانلود کن</a>";
    }
} else {
    echo "❌ خطایی در ساخت فایل زیپ رخ داد. پرمیشن‌ها را چک کن.";
}
?>