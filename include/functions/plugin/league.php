<?php


/**
 * @param int $user_id
 * @return false|object|null
 */
function get__league_user( int $user_id = 0 )
{
    global $chat_id, $chatid, $link;
    if ( $user_id == 0 )
    {
        $user_id = $chat_id ?? $chatid;
    }
    return apply_filters( 'filter_league_user', $link->get_row( "SELECT `league`.* FROM `league` INNER JOIN `users` ON `league`.`point` <= `users`.`point` AND `users`.`user_id` = {$user_id} ORDER By `point` DESC LIMIT 1" ), $user_id );
}

/**
 * @param int $id
 * @return false|object|null
 */
function get__league( int $id )
{
    global $link;
    return $link->get_row( "SELECT * FROM `league` WHERE `id` = {$id}" );
}

/**
 * @param int $server_id
 * @param string $where
 * @return false|object|null|\helper\Role
 */
function get_random_role( int $server_id, string $where = '' )
{
    global $link;
    return $link->get_row( "SELECT `role`.* FROM `role` INNER JOIN `server_role` ON `server_role`.`server_id` = {$server_id} AND {$where} `server_role`.`role_id` = `role`.`id` ORDER BY RAND() LIMIT 1" );
}
