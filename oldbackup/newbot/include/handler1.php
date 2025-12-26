<?php /** @noinspection ALL */

defined('BASE_DIR') || die('NO ACCESS');




require_once LIB_DIR . '/scan.php';



try {
	
	if (file_exists(PACK_DIR . '/jdf.php')) {
            require_once PACK_DIR . '/jdf.php';
        }
	require_once WP_DIR . '/action.php';
	require_once WP_DIR . '/filter.php';
	require_once WP_DIR . '/functions.php';
	
	require_once CLASS_DIR . '/ExceptionAccess.php';
	require_once CLASS_DIR . '/ExceptionError.php';
	require_once CLASS_DIR . '/ExceptionMessage.php';
	require_once CLASS_DIR . '/ExceptionWarning.php';
	
	foreach (scan_dir(LIB_DIR) as $item) {
        if (file_exists(LIB_DIR . '/' . $item)) {
            require_once LIB_DIR . '/' . $item;
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