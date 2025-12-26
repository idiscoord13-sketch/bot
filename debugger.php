<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🚀 Bot Debugger 2025</h2>";

// ۱. تست اتصال خروجی به تلگرام
echo "<b>1. Testing Outbound Connection:</b> ";
$ch = curl_init("https://api.telegram.org/bot2125201956:AAGkJwxRjyVh7963BbWrwMMKRzTejuFcBGY/getMe");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
if ($response) {
    echo "✅ Success! Server can talk to Telegram.<br>";
} else {
    echo "❌ Failed! Error: " . curl_error($ch) . "<br>";
}
curl_close($ch);

// ۲. تست متد POST (شبیهی تلگرام)
echo "<b>2. Testing Local POST Request:</b> ";
$self_url = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?bot=0";
$ch = curl_init($self_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['update_id' => 123]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$res = curl_exec($ch);
if ($status_code == 403) {
    echo "❌ <b>ALERT!</b> Server blocked POST request with 403. This is the problem!<br>";
} else {
    echo "✅ Success! HTTP Status: $status_code<br>";
}
curl_close($ch);

// ۳. بررسی محدودیتهای PHP
echo "<b>3. Environment Check:</b><br>";
echo "- PHP Version: " . phpversion() . "<br>";
echo "- file_get_contents('php://input'): " . (is_readable('php://input') ? "✅ OK" : "⚠️ Potentially Restricted") . "<br>";
echo "- allow_url_fopen: " . (ini_get('allow_url_fopen') ? "✅ ON" : "❌ OFF") . "<br>";

// ۴. بررسی وجود ماژوی
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "- ModSecurity: " . (in_array('mod_security', $modules) ? "❌ Active" : "✅ Not found in Apache") . "<br>";
}
?>