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
    



    $before_date = date( 'Y-m-d H:i:s' , strtotime( '-30 Minutes' ) );
    
    $minutes_2_ago = date( 'Y-m-d H:i:s' , strtotime( '-2 Minutes' ) );

    $list_server = $link->get_result( "SELECT * FROM `server` WHERE `type` = 'public' AND `status` = 'opened' " );

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


            $problems = json_decode( $server->getMeta( 'problems' ), true );

            if ( $server->count == $server->count()) 
            {
                if ($server->get_league()->count == $server->count() and !$problems['get_roles_server']) {
                    $problems['get_roles_server'] = date('Y-m-d H:i:s');
                    
                }

            }
            
            
            if ($problems['get_members_server'] == 'on') {
                // get_members_server
                $status = 'started';
                if ($server->count != $server->count()) {

                    $status = 'opened';
                    $server->update([
                        'count' => $server->count(),
                        'status' => $status
                    ]);

                }
                
            }
            if ($problems['get_roles_server']) {
                $get_roles_server = date_create($problems['get_roles_server']);
                $date_now = date_create(date('Y-m-d H:i:s'));
                $diff = date_diff( $get_roles_server, $date_now );
                if ( $diff->i >= 5 ){
                    if ($server->count == $server->count() AND $server->get_league()->count == $server->count()) {
                        $server->update([
                            'status' => 'started',
                            'count' => $server->count()
                        ]);
    
                        update_server_meta($server->getId(), 'time', time()); // تاریخ شروع شدن بازی
    
                        switch ($server->league_id) {
                            case 3:
                                update_server_meta($server->getId(), 'next-time', time() + 15); // تاریخ باز شدن
                                update_server_meta($server->getId(), 'status', 'welcome'); // وضعیت روز
                                break;
                            default:
                                update_server_meta($server->getId(), 'next-time', time() + 25); // تاریخ باز شدن
                                update_server_meta($server->getId(), 'status', 'night'); // وضعیت روز
                                break;
                        }
                        update_server_meta($server->getId(), 'day', 1); // روز چندم
                    }
                    unset($problems['get_roles_server']);
                }

                

            }
            elseif ($problems['start_server'] == 'on') {

            }
            else {

            }

           
            $server->updateMeta( 'problems', json_encode( $problems ) );   
            // $server->close() && tun_off_server( $server->getId() );

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