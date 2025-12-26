<?php


/**
 * @return array|false
 */
function get_vip_leagues()
{
    global $link;
    return $link->get_result( "SELECT * FROM `vip_league`" );
}

/**
 * @param int $id
 * @return false|object|null
 */
function get_vip_league( int $id )
{
    global $link;
    return $link->get_row( "SELECT * FROM `vip_league` WHERE `id` = {$id}" );
}

/**
 * @param string $emoji
 * @return false|object|null
 */
function get_vip_league_by_emoji( string $emoji )
{
    global $link;
    return $link->get_row( "SELECT * FROM `vip_league` WHERE `emoji` LIKE '{$emoji}'" );
}

/**
 * @param int $user_id
 * @param string $emoji
 * @param string $name
 * @return bool
 * @throws Exception
 */
function add_vip_league( int $user_id, string $league_id, string $name ) : bool
{
    global $link;
    try
    {
        $league = get_vip_league( $league_id );
        $link->insert( 'user_league', [
            'user_id' => $user_id,
            'emoji'   => $league->emoji,
            'name'    => $name,
            'coin'    => $league->coin
        ] );
        update_user( [
            'coin'   => user( $user_id )->coin - $league->coin,
            'league' => $link->getInsertId()
        ], $user_id );
        return true;
    }
    catch ( Exception $e )
    {
        throw new Exception( 'ERROR TO ADD NEW LEAGUE VIP USER ' . $user_id . ' EMOJI ' . $emoji );
    }
}

/**
 * @param int $user_id
 * @param int $league_id
 * @param string $name
 * @return bool
 * @throws Exception
 */
function update_name_vip_league( int $user_id, int $league_id, string $name ) : bool
{
    global $link;
    try
    {
        $link->where( 'user_id', $user_id )->where( 'id', $league_id )->update( 'user_league', [
            'name' => $name
        ] );
        return true;
    }
    catch ( Exception $e )
    {
        throw new Exception( 'ERROR TO ADD NEW LEAGUE VIP USER ' . $user_id . ' EMOJI ' . $emoji );
    }
}

/**
 * @param int $user_id
 * @return array|false
 */
function get_vip_league_user( int $user_id )
{
    global $link;
    return $link->get_result( "SELECT * FROM `user_league` WHERE `user_id` = {$user_id} ORDER BY `id` DESC" );
}

/**
 * @param int $id
 * @return false|object|null
 */
function get_vip_league_user_by_id( int $id )
{
    global $link;
    return $link->get_row( "SELECT * FROM `user_league` WHERE `id` = {$id}" );
}

/**
 * @param string $emoji
 * @param int $coin
 * @param int|null $user_id
 * @return int
 * @throws Exception
 */
function add_new_vip_league( string $emoji, int $coin, int $user_id = null ) : int
{
    global $link;
    try
    {
        $link->insert( 'vip_league', [
            'emoji'   => $emoji,
            'coin'    => $coin,
            'user_id' => $user_id
        ] );
        return $link->getInsertId();
    }
    catch ( Exception $e )
    {
        throw new Exception( "ERROR TO ADD NEW LEAGUE" );
    }
}

/**
 * @param int $user_id
 * @return bool
 * @throws Exception
 */
function delete_all_vip_league_user( int $user_id ) : bool
{
    global $link;
    try
    {
        $link->where( 'user_id', $user_id )->delete( 'user_league' );
        return true;
    }
    catch ( Exception $e )
    {
        throw new Exception( "ERROR ON DELETE ALL VIP LEAGUE USER : " . $user_id );
    }
}

/**
 * @param int $user_id
 * @param int $vip_league_id
 * @return bool
 */
function is_league_for_user( int $user_id, int $vip_league_id ) : bool
{
    global $link;
    return (bool) $link->get_row( "SELECT * FROM `user_league` WHERE `user_id` = {$user_id} AND `id` = {$vip_league_id} LIMIT 1" );
}

add_filter( 'filter_league_user', function ( $obj, $user_id ) {
    if ( isset( $obj->emoji ) )
    {
        $user_league = user( $user_id )->league;
        if ( !is_null( $user_league ) )
        {

            if ( intval( $user_league ) === 0 || is_league_for_user( $user_id, $user_league ) )
            {

                $vip_league = get_vip_league_user_by_id( $user_league );
                if ( !empty( $vip_league->emoji ) )
                {
                    $obj->emoji = $vip_league->emoji;
                }
            }
            else
            {
                update_user( [
                    'league' => null
                ], $user_id );
            }
        }
    }
    return $obj;
}, 10, 2 );