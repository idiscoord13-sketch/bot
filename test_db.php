<?php
// فعال کردن نمایش خطاها برای اینکه بفهمیم درد دیتابیس چیه
ini_set('display_errors', 1);
error_reporting(E_ALL);

// اطلاعاتی که از فایل config.php استخراج کردیم
$host = 'localhost';
$user = 'iranimaf_black';
$pass = 'F{e.087U@QXH&;}?';
$db   = 'iranimaf_main';

echo "<h3>🛠 Database Connection Test</h3>";

$c = mysqli_connect($host, $user, $pass, $db);

if (!$c) {
    echo "❌ <b>خطای اتصال:</b> " . mysqli_connect_error();
    echo "<br>کد خطا: " . mysqli_connect_errno();
} else {
    echo "✅ <b>تبریک!</b> دیتابیس زنده است و متصل شد.";
    mysqli_close($c);
}
?>