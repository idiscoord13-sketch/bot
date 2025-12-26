<?php /** @noinspection ALL */

defined('BASE_DIR') || die('NO ACCESS');




require_once LIB_DIR . '/scan.php';



try {
	require_once CLASS_DIR . '/User.php';
	require_once CONFIG_DIR . '/keyboard.php';
	require_once TRAIT_DIR . '/Base.php';
	foreach (scan_dir(PLUGIN_DIR) as $item) {
        if (file_exists(PLUGIN_DIR . '/' . $item)) {
            require_once PLUGIN_DIR . '/' . $item;
        }
    }
	include_once INCLUDE_DIR.'/actionlist.php';
	
	require_once CLASS_DIR . '/Server.php';
	require_once CLASS_DIR . '/Role.php';
	require_once CLASS_DIR . '/Text.php';
	require_once CLASS_DIR . '/Media.php';
	
	


} catch (Exception | ErrorException | Throwable | ArithmeticError  $e) {

    
    $message = "<u>ERROR TO LOAD FILE</u>" . "\n";
    $message .= "<i>ERROR LINE: {" . $e->getLine() . "}</i>" . "\n \n";
    $message .= "<i>ERROR ON FILE: {" . $e->getFile() . "}</i>" . "\n \n";
    $message .= "<b>CONTACT ERROR: [" . $e->getMessage() . "]</b>";
    SendMessage(ADMIN_LOG, $message, null, null, 'html');
    $message = '⛔️ خطایی رخ داد. ⚠️ گزارش خطا برای پشتیبانی ارسال شد.';
    SendMessage($chat_id ?? $chatid, $message);
    
}