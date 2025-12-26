<?php


require_once __DIR__ . '/../config.php';
const TOKEN_API = '2125201956:AAGkJwxRjyVh7963BbWrwMMKRzTejuFcBGY';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/plugin.php';


if ( ! class_exists( 'library\Server' ) )
{

    require_once BASE_DIR . '/library/Server.php';

}

$telegram = new Telegram(TOKEN_API, false);
$link     = new Tdb(HOST, USERNAME, PASSWORD, DB_NAME);

$time = time();


$token_bot = require( BASE_DIR . '/bots.php' );
include BASE_DIR . "/library/User.php";
require_once BASE_DIR . '/plugin/plugin.php';

try
{
    


    // ------- LIST USERS BAN --------------
    $bans = $link->get_result( "SELECT * FROM `bans` WHERE `end_time` <= {$time} AND `status` = 1 ORDER BY `id` DESC" );

    if ( count( $bans ) > 0 )
    {
        foreach ( $bans as $user )
        {
            $message = '🌐 پیام سرور :' . "\n \n";
            $message .= '⏱ زمان مسدودیت اکانت شما به پایان رسید.' . "\n";
            $message .= '🔸 ' . "<u>لطفا به قوانین ربات پایبند باشید</u>" . ' 🌷' . "\n \n";
            $message .= '➖ درصورت نیاز نام خود را در بازی عوض کنید .' . "\n";
            $message .= 'قوانین ربات :  /ghavanin';
            SendMessage( $user->user_id , $message , null , null , 'html' );
            unban( $user->user_id );
        }
    }

    // ------- Private Server Close --------------

    $before_date = date( 'Y-m-d H:i:s' , strtotime( '-30 Minutes' ) );
    $list_server = $link->get_result( "SELECT * FROM `server` WHERE `type` = 'private' AND `status` = 'opened' AND `created_at` <= '{$before_date}'" );

    if ( count( $list_server ) > 0 )
    {

        foreach ( $list_server as $server )
        {

            $BOT_ID    = $server->bot;
            $TOKEN_API = $token_bot[$server->bot];

            if ( ! class_exists( 'library\Server' ) )
            {

                include BASE_DIR . "/library/Server.php";

            }
            $server = new Server( $server->id );

            $server->close() && tun_off_server( $server->getId() );

        }

    }



}
catch ( Exception | ErrorException | Throwable | ArithmeticError  $e )
{

    $message = "<b>🔴 ERROR ON FILE CRON JOB 1! 🔴</b>" . "\n";
    $message .= "<b>👉 Error File : { " . $e->getFile() . ':' . "<code>" . $e->getLine() . "</code>" . " }</b>" . "\n";
    $message .= "<b>👤 Error User: { " . ( $chat_id ?? $chatid ) . " }</b>" . "\n";
    $message .= "<b>👾 Error Content:</b>" . "\n \n";
    $message .= "<b><code>" . $e->getMessage() . "</code></b>";
    SendMessage( ADMIN_LOG , $message , null , null , 'html' );


}


// if ( file_exists( 'error_log' ) ) unlink( 'error_log' );


die('OK');