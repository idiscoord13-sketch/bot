<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = 'GP_SUPPORT'; // دنبال نام ثابت میگردیم
$path = __DIR__; // کل پوشه ربات را بگرد

echo "Searching for $search...<br>";

$it = new RecursiveDirectoryIterator($path);
$found = false;

foreach(new RecursiveIteratorIterator($it) as $file) {
    if (strpos($file, '.php') !== false && strpos($file, 'finder.php') === false) {
        $content = file_get_contents($file);
        if (strpos($content, $search) !== false) {
            echo "<b>Found in:</b> " . $file . "<br>";
            $found = true;
        }
    }
}

if (!$found) echo "Nothing found. Try searching for '-1001635860906' instead.";
?>