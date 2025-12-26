<?php

/**
 * @param int $user_id
 * @return string
 */
function folder_chat_user( int $user_id ) : string
{

    $dir      = BASE_DIR . "/../";
    $home_dir = $dir . 'chats/';
    if ( !file_exists( $home_dir ) )
    {
        mkdir( $home_dir );
    }
    $user_dir = $home_dir . $user_id . "/";
    if ( !file_exists( $user_dir ) )
    {
        mkdir( $user_dir );
    }
    $file_messages_user = $user_dir . "messages.json";
    if ( !file_exists( $file_messages_user ) )
    {
        file_put_contents( $file_messages_user, json_encode( [] ) );
    }
    return $file_messages_user;
}


/**
 * @param int $user_id
 * @param int $server_id
 * @param string $text
 * @param string $type
 * @param int|null $to_user
 * @param null $to_user_emoji
 * @return bool
 * @throws Exception
 */
function add_chat( int $user_id, int $server_id, string $text, int $to_user = null, $to_user_emoji = null ) : bool
{
    global $link;
    try
    {

        $address = folder_chat_user( $user_id );
        $argc    = json_decode( file_get_contents( $address ), true );
        $argc[]  = [
            'id'            => count( $argc ) + 1,
            'text'          => $text,
            'league'        => get__league_user( $user_id )->emoji,
            'name'          => user( $user_id )->name,
            'to_user'       => $to_user,
            'to_user_emoji' => $to_user_emoji,
            'created_at'    => time()
        ];
        file_put_contents( $address, json_encode( $argc ) );

        /*$link->insert( 'chat', [
            'user_id'       => $user_id,
            'server_id'     => $server_id,
            'text'          => $text,
            'league'        => get__league_user( $user_id )->emoji,
            'name'          => user( $user_id )->name,
            'to_user'       => $to_user,
            'to_user_emoji' => $to_user_emoji,
            'created_at'    => time()
        ] );*/
        if ( get_server_meta( $server_id, 'is-online', $user_id ) == 'no' )
        {

            add_server_meta( $server_id, 'is-online', 'yes', $user_id );

        }
        return true;
    }
    catch ( Exception $e )
    {
        throw new Exception( 'ERROR ON ADDED CHAT USER ' . $user_id . ' SERVER ' . $server_id );
    }
}

/**
 * @param int $user_id
 * @param int $limit
 * @return object[]|null
 */
function get_chats( int $user_id, int $limit = 30 ) : ?array
{
//    global $link;
//    return $link->get_result( "SELECT * FROM `chat` WHERE `user_id` = {$user_id} ORDER BY `id` DESC LIMIT {$limit}" );
    $argc = array_reverse( json_decode( file_get_contents( folder_chat_user( $user_id ) ) ) );
    return array_slice( $argc, 0, $limit );
}

/**
 * @param int $user_id
 * @param int $id_chat
 * @param int $limit
 * @return array|false|\helper\Chat
 */
function get_chats_from_id_by_user( int $user_id, int $id_chat, int $limit = 30 )
{
//    global $link;
//    return $link->get_result( "SELECT * FROM `chat` WHERE `user_id` = {$user_id} AND `id` <= {$id_chat} ORDER BY `id` DESC LIMIT {$limit}" );
    $argc = array_reverse( json_decode( file_get_contents( folder_chat_user( $user_id ) ) ) );
    return array_slice( $argc, $id_chat, $limit );
}

/**
 * @param int $user_id
 * @param int $timeRef
 * @param string $type
 * @return bool
 */
function check_time_chat( int $user_id, int $timeRef = 1, string $type = 'message' ) : bool
{
    if ( !file_exists( FILES_DIR . '/' . $user_id ) )
    {
        mkdir( FILES_DIR . '/' . $user_id );
    }
    if ( !file_exists( FILES_DIR . '/' . $user_id . '/' . $type . '.txt' ) )
    {
        file_put_contents( FILES_DIR . '/' . $user_id . '/' . $type . '.txt', time() );
        return true;
    }
    $time = file_get_contents( FILES_DIR . '/' . $user_id . '/' . $type . '.txt' );
    if ( time() - $time > $timeRef )
    {
        file_put_contents( FILES_DIR . '/' . $user_id . '/' . $type . '.txt', time() );
        return true;
    }
    return false;
}

/**
 * @param int $user_id
 * @param int $server_id
 * @param string $text
 * @return int
 * @throws Exception
 */
function add_prive_chat( int $user_id, int $server_id, string $text ) : int
{
    global $link;
    try
    {
        $link->insert( 'private_chat', [
            'user_id'    => $user_id,
            'server_id'  => $server_id,
            'text'       => $text,
            'created_at' => time()
        ] );
        return $link->getInsertId();
    }
    catch ( Exception $e )
    {
        throw new Exception( 'ERROR ON ADDED CHAT USER ' . $user_id . ' SERVER ' . $server_id );
    }
}

/**
 * @param int $id
 * @return false|object|null
 */
function get_private_chat( int $id )
{
    global $link;
    return $link->get_row( "SELECT * FROM `private_chat` WHERE `id` = {$id}" );
}

/**
 * @param int $id
 * @return void
 * @throws Exception
 */
function delete_private_chat( int $id )
{
    global $link;
    $link->where( 'id', $id )->delete( 'private_chat' );
}