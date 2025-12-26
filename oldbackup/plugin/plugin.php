<?php /** @noinspection ALL */

defined('BASE_DIR') || die('NO ACCESS');




require LIB_DIR . '/scan.php';



try {


    foreach (scan_dir(PLUGINS_DIR) as $item) {
        if (file_exists(PLUGINS_DIR . '/' . $item) && $item != 'plugin.php') {
            require PLUGINS_DIR . '/' . $item;
        }
    }

} catch (Exception | ErrorException | Throwable | ArithmeticError  $e) {

    
    $message = "<u>ERROR TO LOAD FILE</u>" . "\n";
    $message .= "<i>ERROR LINE: {" . $e->getLine() . "}</i>" . "\n \n";
    $message .= "<i>ERROR ON FILE: {" . $e->getFile() . "}</i>" . "\n \n";
    $message .= "<b>CONTACT ERROR: [" . $e->getMessage() . "]</b>";
    SendMessage(ADMIN_LOG, $message, null, null, 'html');
    $message = '⛔️ خطایی رخ داد. ⚠️ گزارش خطا برای پشتیبانی ارسال شد.';
    SendMessage($chat_id ?? $chatid, $message);
    
}