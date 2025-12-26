<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Tehran');

$Authority = $_GET['Authority'];
session_start();

if ( empty( $_GET['Authority'] ) )
{
    header( 'location: tg://resolve?domain=iranimafiabot' );
    exit( 'ERROR ON GET DATA' );
}

require_once 'config.php';

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

$token_bot = require( CONFIG_DIR . '/bots.php' );

$BOT_ID = $_GET['bot'] ?? 0;

define( "TOKEN_API", $token_bot[$BOT_ID] );
$TOKEN_API = $token_bot[$BOT_ID];
define( "BOT_ID", $BOT_ID );

require_once INCLUDE_DIR.'/handler1.php';
require __DIR__ . '/vendor/autoload.php';


$telegram = new Telegram( $TOKEN_API, false );
$link     = new Tdb( HOST, USERNAME, PASSWORD, DB_NAME );

$order = $link->get_row( "SELECT * FROM `orders` WHERE `auth` = '{$Authority}'" );


if ( count( $order ) == 0 || !empty( $order->ref_id ) )
{
    header( 'location: tg://resolve?domain=' . GetMe()->username );
    exit();
}


try
{

    require_once INCLUDE_DIR.'/handler2.php';

    $data = array(
        "merchant_id" => MERCHANT_ID,
        "authority"   => $Authority,
        "amount"      => $order->amount
    );

    $jsonData = json_encode( $data );
    $ch       = curl_init( 'https://api.zarinpal.com/pg/v4/payment/verify.json' );
    curl_setopt( $ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4' );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonData );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen( $jsonData )
    ) );

    $result = curl_exec( $ch );
    curl_close( $ch );
    $result = json_decode( $result, true );

    if ( $err )
    {

        SendMessage( ADMIN_LOG, "cURL Error #:" . $err, null, null, 'html' );
        $message = '💰 گزارش خرید سکه ' . "\n";
        $message .= '➖ تعداد : ' . ( $coin ?? 0 ) . "\n";
//    $message .= '➖ خریدار : ' . '<a href="tg://user?id=' . $order->user_id . '">' . $order->user_id . '</a>' . "\n";
        $message .= '➖ وضعیت : ناموفق' . "\n";
        $message .= '➖ تاریخ و ساعت : ' . jdate( 'Y/m/d H:i:s' ) . "\n";

        add_filter( 'send_massage_text', function ( $text ) {
            return tr_num( $text, 'en', '.' );
        }, 11 );

        $telegram->sendMessage( [
            'chat_id'    => GP_PAYMENY,
            'text'       => $message,
            'parse_mode' => 'html'
        ] );

    }
    else
    {

        if ( $result['data']['code'] == 100 )
        {

            $message = 'تراکنش با موفقیت انجام شد!' . "\n \n";
            $message .= 'شماره تراکنش: `' . $result['data']['ref_id'] . '`' . "\n";
            $message .= 'مبلغ تراکنش: `' . number_format( $order->amount ) . 'ریال`' . "\n";
            // =====================================================================================

            switch ( $order->amount )
            {
                case PLAN_1:
                    $coin = 100;
                    break;
                case PLAN_2:
                    $coin = 200;
                    break;
                case PLAN_3:
                    $coin = 400;
                    break;
                case PLAN_4:
                    $coin = 800;
                    break;
                case PLAN_5:
                    $coin = 1000;
                    break;
                case PLAN_6:
                    $coin = 3000;
                    break;
                case PLAN_7:
                    $coin = 5000;
                    break;
            }

            $user_coin = user( $order->user_id )->coin;
            $message   .= 'تعداد سكه خریداری شده به شما: `' . number_format( $coin ) . '`' . "\n";
            $message   .= 'تعداد سكه شما هم اکنون: `' . number_format( $user_coin + $coin ) . '`' . "\n \n";
            $message   .= 'با تشکر از اعتماد شما 🌷';

            $telegram->sendMessage( [
                'chat_id'    => $order->user_id,
                'text'       => $message,
                'parse_mode' => 'MarkDown'
            ] );

            add_coin( $order->user_id, $coin );

            $message = '💰 گزارش خرید سکه ' . "\n";
            $message .= '➖ تعداد : ' . ( $coin ?? 0 ) . "\n";
            $message .= '➖ خریدار : ' . '<a href="tg://user?id=' . $order->user_id . '">' . $order->user_id . '</a>' . "\n";
            $message .= '➖ وضعیت : موفق' . "\n";
            $message .= '➖ کد پیگیری : ' . "<code>" . $result['data']['ref_id'] . "</code> \n";
            $message .= '➖ تاریخ و ساعت : ' . jdate( 'Y/m/d H:i:s' ) . "\n";

            $telegram->sendMessage( [
                'chat_id'    => GP_PAYMENY,
                'text'       => $message,
                'parse_mode' => 'html'
            ] );


            if ( !is_user_meta( $order->user_id, 'vip-user' ) )
            {

                $sum = $link->get_var( "SELECT SUM(`amount`) as `sum` FROM `orders` WHERE `ref_id` IS NOT NULL AND `user_id` = {$order->user_id}" );
                if ( $sum >= 2000000 )
                {

                    add_user_meta( $order->user_id, 'vip-user', 'yes' );

                }

            }


            update_factor( $Authority, $result['data']['ref_id'] );

            http_response_code(200);
            echo "Payment verification successful, please check your payment status.";
            exit;




        }
        else
        {

            $message = '💰 گزارش خرید سکه ' . "\n";
            $message .= '➖ تعداد : ' . ( $coin ?? 0 ) . "\n";
            $message .= '➖ کد خطا : ' . ( $result['data']['code'] ) . "\n";
            $message .= '➖ خریدار : ' . '<a href="tg://user?id=' . $order->user_id . '">' . $order->user_id . '</a>' . "\n";
            $message .= '➖ وضعیت : ناموفق' . "\n";
            $message .= '➖ تاریخ و ساعت : ' . jdate( 'Y/m/d H:i:s' ) . "\n";
            $telegram->sendMessage( [
                'chat_id'    => GP_PAYMENY,
                'text'       => $message,
                'parse_mode' => 'html'
            ] );


        }

        header( 'location: tg://resolve?domain=' . GetMe()->username );

    }

}
catch ( Exception | Throwable  $e )
{

    $message = "<i>ERROR ON FILE PAYMENT</i>" . "\n \n";
    $message = "<i>ERROR LINE: {" . $e->getLine() . "}</i>" . "\n \n";
    $message .= "<i>ERROR User: {" . ( $chat_id ?? $chatid ) . "}</i>" . "\n \n";
    $message .= "<u>ERROR ON FILE: {" . $e->getFile() . "}</u>" . "\n \n";
    $message .= "<b>CONTACT ERROR: [" . $e->getMessage() . "]</b>";
    $telegram->sendMessage( [
        'chat_id'    => ADMIN_LOG,
        'text'       => $message,
        'parse_mode' => 'html'
    ] );

}

