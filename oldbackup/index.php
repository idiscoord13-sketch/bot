<?php

/**
 * Project: Mr/Mrs/Company
 * Desiccation: Power By Core Create Bots Telegram
 * Version: 1.0.0
 * Creator: Ali Shahmohammadi
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$updates = file_get_contents( 'php://input' );
/** @var \helper\Update $update */
$update = json_decode( $updates );
isset( $update->update_id ) || exit( 'NO ACCESS' );
isset( $_GET[ 'bot' ] ) || exit( 'TOKEN NOT EXISTS' );

// file_put_contents( "data.json", $updates );

require_once 'config.php';

$token_bot = require( BASE_DIR . '/bots.php' );


if ( $_GET[ 'bot' ] < 0 || ! isset( $token_bot[ $_GET[ 'bot' ] ] ) )
{

    exit( 'NO ACCESS BOT' );

}


define( "TOKEN_API", $token_bot[ $_GET[ 'bot' ] ] );
$TOKEN_API = $token_bot[ $_GET[ 'bot' ] ];
define( "BOT_ID", $_GET[ 'bot' ] );
$BOT_ID = $_GET[ 'bot' ];

require __DIR__ . '/vendor/autoload.php';
require_once 'lib/plugin.php';

$telegram = new Telegram( $TOKEN_API , !isLocalhost());
date_default_timezone_set( 'Asia/Tehran' );
/**
 * Logs the request details if the chat ID matches the specified ID,
 * and removes logs older than one hour.
 */
/**
 * Logs the request details if the chat ID matches the specified ID,
 * and logs any errors encountered during the process.
 */

// Check for specific chat ID and call logRequest function
$logFilePath = BASE_DIR . '/logs/request_log.txt';
file_put_contents($logFilePath, json_encode($update), FILE_APPEND);
if($BOT_ID == 2)
{
    $logFilePath = BASE_DIR . '/logs/request_log_2.txt';
    file_put_contents($logFilePath, json_encode($update), FILE_APPEND);
}


if ( isset( $update->message ) )
{
    $message    = $update->message;
    $chat_id    = $message->chat->id;
    $text       = tr_num( $message->text ) ?? null;
    $message_id = $message->message_id;
    $from_id    = $message->from->id;
    $first_name = $message->from->first_name;
    $last_name  = $message->from->last_name;
    $username   = $message->from->username;
    $caption    = $message->caption;
    $contact    = $message->contact;
    $reply      = $message->reply_to_message->forward_from->id;
    $reply_id   = $message->reply_to_message->from->id;
    $forward    = $message->forward_from;
    $forward_id = $message->forward_from->id;
    $sticker_id = $message->sticker->file_id;
    $video_id   = $message->video->file_id;
    $voice_id   = $message->voice->file_id;
    $file_id    = $message->document->file_id;
    $music_id   = $message->audio->file_id;
    $photo0_id  = $message->photo[ 0 ]->file_id;
    $photo1_id  = $message->photo[ 1 ]->file_id;
    $photo2_id  = $message->photo[ 2 ]->file_id;
    $txt        = $message->chat->text;
    $type       = $message->chat->type;
    $urlLink    = $message->entities;
}

$logEntry = "==== Request on " . date('Y-m-d H:i:s') . " ====\n";
$logEntry .= "🔗 URL: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n\n";
$logEntry .= "📦 Body:\n" . json_encode($update, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
$logEntry .= "===============================\n\n";

// Send the formatted log entry as a message
//$telegram->telegram()->sendMessage(5889394964, $logEntry, null, 'html');


function stylizeText($text, $fontName) {
    // Map all styled characters back to normal characters
    $normalAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $normalizedText = '';

    foreach (mb_str_split($text) as $char) {
        foreach (FONTS as $font => $styledAlphabet) {
            $pos = mb_strpos($styledAlphabet, $char);
            if ($pos !== false) {
                // Convert styled character to normal equivalent
                $normalizedText .= mb_substr($normalAlphabet, $pos, 1);
                break;
            }
        }
        if ($pos === false) {
            // Keep character as is if it's not in any styled alphabet
            $normalizedText .= $char;
        }
    }

    // Apply the desired font style
    $styledText = '';
    $styledAlphabet = FONTS[$fontName] ?? $normalAlphabet; // Use normal font if $fontName is invalid

    foreach (mb_str_split($normalizedText) as $char) {
        $pos = mb_strpos($normalAlphabet, $char);
        $styledText .= $pos !== false ? mb_substr($styledAlphabet, $pos, 1) : $char;
    }

    return $styledText;
}


function prependLog($string, $path) {
    $directory = __DIR__ . $path;


    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    $filePath = $directory;

    $initialString = '';

    if (file_exists($filePath)) {
        $fileContentsArray = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $initialString = implode("\n", $fileContentsArray);
    }

    $newContent = $string . "\n" . $initialString;

    file_put_contents($filePath, $newContent);


}

function isLocalhost() {
    $whitelist = ['127.0.0.1', '::1', 'localhost'];
    return in_array($_SERVER['REMOTE_ADDR'], $whitelist) || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
}


function return_time_and_times($input)
{
    $parts = explode(":", $input);

    // Ensure we have exactly 2 parts after splitting
    if (count($parts) !== 2) {
        return null; // or handle the error as appropriate
    }

    // Create an associative array with 'time' and 'id' keys
    return [
        'time' => $parts[0],
        'id' => $parts[1]
    ];
}

function delete_old_items($items, $now, $tresh_hold, $path)
{
    $directory = __DIR__;
    $directory = $directory . $path;
    wipe_file_content($directory);
    foreach ($items as $item) {
        $final_item = return_time_and_times($item);
        if ($final_item['time'] > $now - $tresh_hold) {
            prependLog($final_item['time'].':'.$final_item['id'], $path);
        }
    }
}

function wipe_file_content($file_to_wipe) {
    if (file_exists($file_to_wipe)) {
        // Open the file in write mode ("w") to truncate the content
        $file_handle = fopen($file_to_wipe, 'w');

        if ($file_handle) {
            // Write an empty string to overwrite existing content
            fwrite($file_handle, "");
            fclose($file_handle);
            return "File content wiped successfully!";
        } else {
            return "Failed to open file for wiping. Please check permissions or try again later.";
        }
    } else {
        return "The file you specified does not exist.";
    }
}

function check_user_id_times($data, $id, $ban_path, $times_tresh_hold)
{
    $count = 1;

    foreach ($data as $item) {
        // Check if the target string is present after colon (:)
        if (return_time_and_times($item)['id'] == $id) {
            $count++;
        }
    }

    if ($count > $times_tresh_hold) {
        prependLog($id, $ban_path);
    }

}

function check_ban_ids($id, $ban_path)
{
    global $telegram;
    $directory = __DIR__;
    $directory = $directory . $ban_path;
    $ban_ids = file($directory, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($id, $ban_ids)) {
        $message = '❌به علت حجم درخواست بالا شما موقتا بن شده اید. ' ;
        $telegram->sendMessage( [
            'chat_id'    => $id ,
            'text'       => $message,
            'parse_mode' => 'html'
        ] );
        exit();
    }

}

function prevent_ddos_attack($chat_id,$ban_path, $path, $time_minuts_tresh_hold, $tims_repeated_tresh_hold)
{
    $currentTimestamp = time();
    $totalMinutes = intval($currentTimestamp / 60);

    $directory = __DIR__;
    $directory = $directory . $path;
    $stored_data = file($directory, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $count = count($stored_data);
    $final_item = return_time_and_times($stored_data[0]);

    check_ban_ids($chat_id, $ban_path);

    if ($totalMinutes > $final_item['time']) {
        delete_old_items($stored_data, $totalMinutes, $time_minuts_tresh_hold, $path);
    }
    check_user_id_times($stored_data, $chat_id, $ban_path, $tims_repeated_tresh_hold);
    prependLog($totalMinutes.':'.$chat_id, $path);

}



if ( isset( $update->callback_query ) )
{
    /** @var $callback_query \helper\CallbackQuery */
    $callback_query = $update->callback_query;
    $Data           = $callback_query->data;
    $dataid         = $callback_query->id;
    $chatid         = $callback_query->message->chat->id;
    $fromid         = $callback_query->from->id;
    $tccall         = $callback_query->message->chat->type;
    $messageid      = $callback_query->message->message_id;
    $type           = $callback_query->message->chat->type;
    $first_name     = $callback_query->from->first_name;
}
if ( isset( $update->channel_post ) )
{
    $channel_post = $update->channel_post;
    $type         = $channel_post->chat->type;
    $chat_id      = $channel_post->chat->id;
}
if ( isset( $update->edited_message ) )
{
    $message    = $update->edited_message;
    $chat_id    = $message->chat->id;
    $text       = $message->text;
    $message_id = $message->message_id;
    $from_id    = $message->from->id;
    $first_name = $message->from->first_name;
    $last_name  = $message->from->last_name;
    $username   = $message->from->username;
}


if ( isset( $update->inline_query ) )
{
    $inline_query = $update->inline_query;
    $query        = $inline_query->query;
    $chatid       = $inline_query->from->id;
}

if ($chat_id == '202910544'  and count($message->photo)) {
    $fopen = fopen( './test_file.txt', 'a');
    
    $fwrite = json_encode($message->photo);
    fwrite($fopen, $fwrite);
    fclose($fopen);
}

if ( $text == '/id' )
{
    SendMessage( $chat_id, $chat_id );
    exit();
}

 // exit();
try
{

    if (
        ( isset( $type ) && ( $type == 'private' ) ) && ( $from_id != ADMIN_LOG && $chatid != ADMIN_LOG ) && ( $from_id != ADMIN_ID && $chatid != ADMIN_ID ) && ( $from_id != '321415151' && $chatid != '321415151' ) 
    )
    {
        $date = date( file_get_contents( BASE_DIR . '/break.txt' ) );

        //$date = '2023-10-1 16:00:00';
        if ( time() <= strtotime( $date ) - 2 )
        {
            $now         = new DateTime();
            $future_date = new DateTime( $date );

            $interval = $future_date->diff( $now );

            if ( ! empty( $interval->format( "%d" ) ) )
            {
                $time = $interval->format( "%d روز " );
                $time .= $interval->format( "%h ساعت " );
            }
            elseif ( ! empty( $interval->format( "%h" ) ) )
            {
                $time = $interval->format( "%h ساعت " );
                $time .= $interval->format( "%i دقیقه " );
            }
            elseif ( ! empty( $interval->format( "%i" ) ) )
            {
                $time = $interval->format( "%i دقیقه " );
                $time .= $interval->format( " %s ثانیه" );
            }
            elseif ( ! empty( $interval->format( "%s" ) ) )
            {
                $time = $interval->format( "%s ثانیه " );
            }

            $message = '⚜️ زمان تعمییر و نگه داری:' . "\n \n";
            $message .= '🔴 بعد از  ' . "<u>" . $time . "</u>" . ' دیگر برمیگردیم.';
            $telegram->telegram()->sendMessage( $chat_id ?? $chatid, $message, null, 'html' );
            exit();
        }
    }

    if (isset($chat_id) and $chat_id != '') {
        prevent_ddos_attack($chat_id, '/attacks/ban.txt', '/attacks/logfile.txt', 2, 200 );
    } else if(isset($chatid) and $chatid != '') {
        //prevent_ddos_attack($chatid, '/attacks/ban.txt', '/attacks/logfile.txt', 2, 40 );
    }



    $link = new Tdb( HOST, USERNAME, PASSWORD, DB_NAME );
    


    include "library/User.php";
    include 'plugin/plugin.php';
    include 'plugins.php';
    include "library/Server.php";
    include "library/Role.php";
    require 'library/Text.php';
    require 'library/Media.php';

    usleep(mt_rand(1000, 100000));

    function set_first_token()
    {
        global $token_bot;
        return $token_bot[ 0 ];
    }



    $keyboard = [];

    # don't move this component
    // ----- Private Chat Reply To Message -------
    if ( $type == 'private' && isset( $message->reply_to_message ) )
    {

        require_once SOURCE_DIR . '/reply_to_message.php';

    }

    // ----- Callback Query Message -------
    if ( isset( $update->callback_query ) )
    {

        $data = explode( '-', $Data );
        if ( $data[ 0 ] == 'create_game' )
        {

            require_once SOURCE_DIR . '/create_game.php';

        }

    }

    // ----- Private Chat Message -------
    if ( $type == 'private' && isset( $message->from ) )
    {

        $user = new \library\User( $from_id );

        $data = explode( ' ', $text );
        require_once SOURCE_DIR . '/friend_server.php';

        require_once SOURCE_DIR . '/private_chat.php';
    }

    if ( $type == 'supergroup' && isset( $message->reply_to_message ) )
    {

        require_once SOURCE_DIR . '/group_reply_to_message.php';

    }

    // ----- Private Chat Callback Query -------
    if ( $type == 'private' && isset( $update->callback_query ) )
    {
        $user = new \library\User( $fromid );

        $data_day = explode( '/', $Data );
        if ( count( $data_day ) == 2 ) $data = explode( '-', $data_day[ 1 ] );
        else
            $data = explode( '-', $data_day[ 0 ] );

        require_once SOURCE_DIR . '/callback_query.php';
    }

    // ----- Private Chat inline Query -------
    if ( isset( $update->inline_query ) )
    {
        $inline_query = $update->inline_query;
        $query        = $inline_query->query;
        $chatid       = $inline_query->from->id;


        do_action( 'join_channel' );
        do_action( 'check_ban' );

        require_once SOURCE_DIR . '/inline_query.php';
    }

    // ----- Manager Group Message -------
    if ( ( $type == 'supergroup' || $type == 'group' ) && isset( $message->from ) )
    {
        $data = explode( ' ', $text );
        require_once SOURCE_DIR . '/group_private.php';
    }

    if ( ( $type == 'supergroup' || $type == 'group' ) && isset( $message->from ) )
    {
        if (
            in_array( $from_id, get_admins() )
            && in_array( strtolower( $text ), [
                '/report',
                '/manager',
                '/support',
                '/chat'
            ] )
        )
        {
            $config = file_get_contents( BASE_DIR . '/config.php' );
            switch ( strtolower( $text ) )
            {
                case '/report':
                    $config = str_replace( GP_REPORT, $chat_id, $config );
                    break;
                case '/manager':
                    $config = str_replace( GP_MANAGER, $chat_id, $config );
                    break;
                case '/support':
                    $config = str_replace( GP_SUPPORT, $chat_id, $config );
                    break;
                case '/chat':
                    $config = str_replace( GP_CHAT, $chat_id, $config );
                    break;
            }
            file_put_contents( BASE_DIR . '/config.php', $config );
            SendMessage( $chat_id, '✅ انجام شد.' );
        }
    }
    if (isset( $message->from ) && in_array( $from_id, get_admins() ) && (strtolower( $text ) == "/turnchatoff" || strtolower( $text ) == "/turnchaton")){
        $turnChat = strtolower( $text ) == "/turnchaton" ? '1' : '0';
        global $link;
        global $from_id;
        $from_id = $message->from->id;
        try
        {
            $link->where( 'id', '14' )->update( 'setting', [
                'value' => $turnChat,
            ] );
            SendMessage( $chat_id, '✅ انجام شد.' );
        } catch ( Exception $e )
        {
        }
            // if(in_array( $from_id, get_admins() )){
    }
    // ----- Manager Group Callback Query -------
    if ( ( $type == 'supergroup' || $type == 'group' ) && isset( $update->callback_query ) )
    {

        add_filter( 'filter_token', 'set_first_token' );
        $data = explode( '-', $Data );
        require_once SOURCE_DIR . '/group_callback_query.php';

    }

    clearstatcache();
    header( 'Connection: close' );

}
catch ( ExceptionWarning $exception )
{

    $message = '⚠️ خطا، ' . $exception->getMessage();
    $telegram->sendMessage( [
        'chat_id'    => $chat_id ?? $chatid,
        'text'       => $message,
        'parse_mode' => 'html'
    ] );

}
catch ( ExceptionError $exception )
{

    $message = '❌خطا، ' . $exception->getMessage();
    $telegram->sendMessage( [
        'chat_id'    => $chat_id ?? $chatid,
        'text'       => $message,
        'parse_mode' => 'html'
    ] );

}
catch ( ExceptionMessage $exception )
{

    $telegram->sendMessage( [
        'chat_id'    => $chat_id ?? $chatid,
        'text'       => $exception->getMessage(),
        'parse_mode' => 'html'
    ] );

}
catch ( ExceptionAccess $exception )
{

    if ( $exception->getCode() == - 100 ) $chat_id = ADMIN_LOG;

    $message = '⛔️خطا، ' . $exception->getMessage();
    $telegram->sendMessage( [
        'chat_id'    => $chat_id ?? $chatid,
        'text'       => $message,
        'parse_mode' => 'html'
    ] );

}
catch ( Exception | Throwable $e )
{

    $message = "<i>ERROR LINE: {" . $e->getLine() . "}</i>" . "\n \n";
    $message .= "<i>ERROR User: {" . ( $chat_id ?? $chatid ) . "}</i>" . "\n \n";
    $message .= "<u>ERROR ON FILE: {" . $e->getFile() . "}</u>" . "\n \n";
    $message .= "<b>CONTACT ERROR: [" . $e->getMessage() . "]</b>";
    error_log($message);
    SendMessage( 5889394964, $message, null, null, 'html' );
    $message = '🔴 متاسفانه خطایی رخ داد،' . "\n \n" . '⚠️ گزارش خطا برای پشتیبانی ارسال شد. لطفا مجددا تلاش کنید🙏';
    SendMessage( $chat_id ?? $chatid, $message );

    /*bot('setWebhook', [
        'url'                  => SRC_URL . 'index.php?bot=' . $BOT_ID,
        'ip_address'           => '157.90.14.20',
        'max_connections'      => 100,
        'drop_pending_updates' => true
    ]);*/

}


// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// if ( file_exists( 'error_log' ) ) unlink( 'error_log' );