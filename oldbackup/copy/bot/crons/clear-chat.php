<?php



require_once __DIR__ . '/../config.php';
const TOKEN_API = '2125201956:AAGkJwxRjyVh7963BbWrwMMKRzTejuFcBGY';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/plugin.php';

$telegram = new Telegram(TOKEN_API, false);
$link     = new Tdb(HOST, USERNAME, PASSWORD, DB_NAME);



/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

function deleteDir( $path )
{
    return is_file( $path ) ?
        @unlink( $path ) :
        array_map( __FUNCTION__, glob( $path . '/*' ) ) == @rmdir( $path );
}

try
{

    $dir      = BASE_DIR . "/../";
    $home_dir = $dir . 'chats/';
    deleteDir( $home_dir );
    
//    $link->query('TRUNCATE `chat`');
//    $link->where( 'created_at', strtotime( '-1 h' ), '>=' )->delete( 'chat' );

    $link->query( 'TRUNCATE `private_chat`' );
//    $link->where( 'created_at', strtotime( '-1 h' ), '>=' )->delete( 'private_chat' );

    $link->query( 'TRUNCATE `report`' );
//    $link->where( 'created_at', date( 'Y-m-d H:i:s', strtotime( '-1 h' ) ), '>=' )->delete( 'report' );


}
catch ( Exception | ErrorException | Throwable | ArithmeticError  $e )
{
    $message = "<u>ERROR ON FILE CRON JOB CHAT!</u>" . "\n \n";
    $message .= "<i>ERROR LINE: {" . $e->getLine() . "}</i>" . "\n \n";
    $message .= "<i>ERROR File: {" . $e->getFile() . "}</i>" . "\n \n";
    $message .= "<i>ERROR Code: {" . $e->getCode() . "}</i>" . "\n \n";
    $message .= "<b>CONTACT ERROR: [" . $e->getMessage() . "]</b>";
    SendMessage( ADMIN_LOG, $message, null, null, 'html' );
}

exit( 'SUCCESS' );