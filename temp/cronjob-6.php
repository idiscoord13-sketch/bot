<?php


require_once __DIR__ . '/../config.php';

date_default_timezone_set( 'Asia/Tehran' );
$date = date( file_get_contents( BASE_DIR . '/break.txt' ) );
if ( time() <= strtotime( $date ) - 2 )
{
    exit( 'Time Break' );
}

use library\Server;

const TOKEN_API = '2125201956:AAGkJwxRjyVh7963BbWrwMMKRzTejuFcBGY';
require __DIR__ . '/../vendor/autoload.php';
require_once INCLUDE_DIR.'/handler1.php';

$telegram       = new Telegram( TOKEN_API, false );
$link           = new Tdb( HOST, USERNAME, PASSWORD, DB_NAME );
$number_to_word = new NumberToWord();


/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/


//include BASE_DIR . "/library/User.php";
require_once INCLUDE_DIR.'/handler2.php';
$token_bot = require( CONFIG_DIR . '/bots.php' );

$time = time();


try {


    // ------- RUN SERVER GAME --------------


    $servers = $link->get_result("SELECT `server`.* FROM `server` INNER JOIN `server_meta` ON  
    `server`.`status` = 'started' AND
    `server`.`cron` = 6 AND
    `server`.`id` = `server_meta`.`server_id` AND
    `server_meta`.`meta_key` = 'next-time' AND 
    `server_meta`.`meta_value` <= {$time}");

    require 'source.php';

} catch (Exception|ErrorException|Throwable|ArithmeticError  $e) {

    $message = "<b>🔴 ERROR ON FILE CRON JOB 6! 🔴</b>" . "\n";
    $message .= "<b>👉 Error File : { " . $e->getFile() . ':' . "<code>" . $e->getLine() . "</code>" . " }</b>" . "\n";
    $message .= "<b>👤 Error User: { " . ( $chat_id ?? $chatid ) . " }</b>" . "\n";
    $message .= "<b>👾 Error Content:</b>" . "\n \n";
    $message .= "<b><code>" . $e->getMessage() . "</code></b>";
    SendMessage(ADMIN_LOG, $message, null, null, 'html');

    /*if (file_exists(BASE_DIR . '/error_log.txt')) {

        $error_number = file_get_contents(BASE_DIR . '/error_log.txt');
        if ($error_number >= 10) {

            file_put_contents(BASE_DIR . '/break.txt', date('Y-m-d H:i:s', strtotime('+2 Minutes')));
            $link->update("server", [
                'status' => 'closed',
                'count' => 0
            ]);
            $users = $link->get_result("SELECT * FROM `user_game`");
            foreach ($users as $user) {
                $link->where('user_id', $user->user_id)->update("users", [
                    'status' => null
                ]);
            }
            $link->delete("user_game");
            file_put_contents(BASE_DIR . '/error_log.txt', 0);

        } else {

            file_put_contents(BASE_DIR . '/error_log.txt', $error_number + 1);

        }

    } else {

        file_put_contents(BASE_DIR . '/error_log.txt', 0);

    }*/

}