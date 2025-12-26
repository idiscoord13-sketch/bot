<?php

use library\Server;


/**
 * @return array|false
 */
function get_games()
{
    global $link, $from_id, $chatid;

    if ( $from_id == ADMIN_ID || $chatid == ADMIN_ID || $from_id == ADMIN_LOG || $chatid == ADMIN_LOG || $chatid == '321415151')
    {
        return $link->get_result( "SELECT * FROM `name_game`" );
    }

    return $link->get_result( "SELECT * FROM `name_game` WHERE `id` != 5" );

}





add_action('report_start_game', function( $text ) {
    global $telegram;
    $telegram->sendMessage([
        'chat_id'    => GP_REPORT,
        'text'       => $text,
        'parse_mode' => 'html'
    ]);
});


/**
 * @param int $user_id
 * @param  $server_id
 * @return false|mixed
 */
function name( int $user_id, $server_id )
{
    global $link;
    return $link->get_var("SELECT `name` FROM `names` WHERE `server_id` = {$server_id} AND `user_id` = {$user_id} LIMIT 1");
}

/**
 * @param int $user_id
 * @param int $server_id
 * @param string|null $name
 * @return bool
 * @throws Exception
 */
function add_name( int $user_id, int $server_id, string $name = null ) : bool
{
    global $link;
    try
    {
        $link->insert('names', [
            'server_id' => $server_id,
            'user_id'   => $user_id,
            'name'      => $name ?? user($user_id)->name
        ]);
        return true;
    } catch ( Exception $e )
    {
        throw new Exception("ERROR TO ADD NEW NAME USER : " . $user_id);
    }
}

/*add_action('report_start_game', function( $text, $server ) {

    if ( $server instanceof Server )
    {

        foreach ( $server->server() as $item )
        {
            add_name($item->user_id, $server->getId());
        }

    }
    else
    {


        $users_server = get_users_by_server($server->id);
        foreach ( $users_server as $item )
        {
            add_name($item->user_id, $server->id);
        }

    }

}, 10, 2);*/


global $link;
$chatReportCheck = $link->get_var("SELECT `value` FROM `setting` WHERE `id` = 14 LIMIT 1");
if($chatReportCheck == "1"){
    add_action('report_game', function ($text) {
        global $telegram;
        $telegram->sendMessage([
            'chat_id' => GP_CHAT,
            'text' => $text,
            'parse_mode' => 'html'
        ]);
    });
}


add_filter('filter_mafia', function( $user_id ) {
    return !is_server_meta(is_user_in_which_server($user_id)->id, 'bakreh');
});

/**
 * @param int $server_id
 * @param int $user_id
 * @return bool
 */
function is_user_in_game( int $server_id, int $user_id ) : bool
{
    return is_user_in_which_server($user_id)->id == $server_id;
}

/**
 * <h2>IF Returned TRUE User In Prison Or Not</h2>
 * @param int $server_id
 * @param int $user_id
 * @return bool
 */
function is_user_in_prisoner( int $server_id, int $user_id ) : bool
{
    return get_server_meta($server_id, 'select', ROLE_Bazpors) == $user_id;
}

/**
 * @param int $user_id
 * @param int $server_id
 * @return bool
 */
function is_user_hacked( int $user_id, int $server_id ) : bool
{
    return get_server_meta($server_id, 'hack') == $user_id;
}