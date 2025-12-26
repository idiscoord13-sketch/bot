<?php

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

require 'config.php';

const TOKEN_API = '2125201956:AAGkJwxRjyVh7963BbWrwMMKRzTejuFcBGY';
$TOKEN_API = TOKEN_API;
require __DIR__ . '/vendor/autoload.php';


require 'lib/plugin.php';


$telegram = new Telegram( TOKEN_API, false );
$link     = new Tdb( HOST, USERNAME, PASSWORD, DB_NAME );

include "library/User.php";


include 'plugin/plugin.php';

include "library/Text.php";

$token_bot = require( BASE_DIR . '/bots.php' );


foreach ( $token_bot as $BOT_ID => $item )
{

    $telegram = new Telegram( $item, false );
    $telegram->endpoint( 'setWebhook', [

        'url'                  => SRC_URL . 'index.php?bot=' . $BOT_ID,
        'ip_address'           => '157.90.199.46',
        'max_connections'      => 100,
        'drop_pending_updates' => true

    ] );


}


die( 'DOne' );


