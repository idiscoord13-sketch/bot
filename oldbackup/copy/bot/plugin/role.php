<?php

/**
 * @param int $level_id
 * @return array|false
 */
function get_roles( int $level_id )
{
    global $link;
    return $link->get_result( "SELECT `role`.* FROM `role` INNER JOIN `game_role` ON `game_role`.`level` = {$level_id} AND `game_role`.`role_id` = `role`.`id` ORDER BY `role`.`id` ASC" );
}

/**
 * @param $role_id
 * @return false|object|null|\helper\Role
 */
function get_role( $role_id )
{
    global $link;
    return $link->get_row( "SELECT * FROM `role` WHERE `id` = {$role_id}" );
}

/**
 * @param $id
 * @return string
 */
function group_name( $id ) : string
{
    switch ( $id )
    {
        default:
            return 'مساوی ⚪️';
        case 1:
            return 'شهروند 🟢';
        case 2:
            return 'عضو مافیا 🔴';
        case 3:
            return 'مستقل 🟡';
        case 4:
            return 'شگفت‌انگیز 🟣';
    }
}

/**
 * @param $id
 * @return string
 */
function emoji_group( $id ) : string
{
    switch ( $id )
    {
        default:
            return '⚪️';
        case 1:
            return '🟢';
        case 2:
            return '🔴';
        case 3:
            return '🟡';
        case 4:
            return '🟣';
    }
}

/**
 * @param int $server_id
 * @param int $group_id
 * @return array|false|\helper\Role|\helper\Users
 */
function get_role_by_group( int $server_id, int $group_id )
{
    global $link;
    return $link->get_result( "SELECT * FROM `role` INNER JOIN `server_role` ON `server_role`.`server_id` = {$server_id} AND `server_role`.`role_id` = `role`.`id` AND `role`.`group_id` = {$group_id} ORDER BY `role`.`id` ASC" );
}

/**
 * @param int $group_id
 * @param int $league_id
 * @return array|false
 */
function get_keyboard_roles_by_group_and_game( int $group_id, int $league_id )
{
    global $link;
    return $link->get_result( "SELECT `role`.* FROM `role` INNER JOIN `keyboard_roles` ON `keyboard_roles`.`level` = {$league_id} AND `role`.`group_id` = {$group_id} AND `keyboard_roles`.`role_id` = `role`.`id` ORDER BY `role`.`level` ASC" );
}

/**
 * @param int $server_id
 * @param int $role_id
 * @param int $user_id
 * @return bool
 * @throws Exception
 */
function add_role_to_server( int $server_id, int $role_id, int $user_id ) : bool
{
    global $link;

    if ( $role_id <= 0 ) return false;

    if ( role_exists( $role_id, $server_id ) ) return false;

    $server_league = get_league_server_with_role( $server_id );
    // Edit Sam // get roles by level league

    $roles_by_level = get_roles_by_level( $role_id, $server_league );
   
    foreach ( $roles_by_level as $role ) {
        if ( role_exists( (int) $role, $server_id ) ) {
            return false;
        } 
    }
    switch ( $role_id )
    {

        case ROLE_MozakarehKonandeh:

            $roles = get_roles_by_level( ROLE_KhabarNegar, $server_league );
            foreach ( $roles as $role ) if ( $role != ROLE_KhabarNegar && role_exists( (int) $role, $server_id ) ) return false;

            break;

        case ROLE_KhabarNegar:

            $roles = get_roles_by_level( ROLE_MozakarehKonandeh, $server_league );
            foreach ( $roles as $role ) if ( $role != ROLE_MozakarehKonandeh && role_exists( (int) $role, $server_id ) ) return false;

            break;

    }

    $link->insert( "server_role", [
        'user_id'   => $user_id,
        'server_id' => $server_id,
        'role_id'   => $role_id,
        'type'   => 'preselect',
    ] );
    return true;
}

/**
 * @param int $server_id
 * @return array|false
 */
function get_role_save_server( int $server_id )
{
    global $link;
    $result = $link->get_result( "SELECT * FROM `server_role` WHERE `server_id` = {$server_id} AND `type` != 'random' ORDER BY `role_id` ASC" );
    if ( count( $result ) == 0 ) return false;
    return $result;
}

/**
 * @param int $league_id
 * @param int $role_id
 * @return bool
 */
function role_hash_game( int $league_id, int $role_id ) : bool
{
    global $link;
    return (bool) $link->get_row( "SELECT * FROM `keyboard_roles` WHERE `level` = {$league_id} AND `role_id` = {$role_id}" );
}

/**
 * @param int $server_id
 * @return int
 */
function get_league_server_with_role( int $server_id ) : int
{
    global $link;
    return (int) $link->get_var( "SELECT `league_id` FROM `server` WHERE `id` = {$server_id}" );
}

/**
 * @param int $role_id
 * @param int $level
 * @return array
 */
// Edit Sam
function get_roles_by_level( int $role_id, int $level ) : array
{
    global $link;
    $roles = [];
    $game_role = $link->get_result( "SELECT * FROM `game_role` WHERE `level` = {$level}");
    foreach ( $game_role as $role) {
        $role_array = explode( ',', $role->role_id );
        if (in_array($role_id, $role_array)) {
            $roles = $role_array;
            break;
        }
    }
    return $roles;
}
function get_roles_by_role( int $role_id, int $level ) : array
{
    global $link;
    return explode( ',', $link->get_var( "SELECT `role_id` FROM `game_role` WHERE `level` = {$level} AND `role_id` LIKE '%{$role_id}%' LIMIT 1" ) );
}

/**
 * @param array $roles
 * @param int $level
 * @return array
 */
function check_role_link_exits( array $roles, int $level ) : array
{

    foreach ( $roles as $role )
    {

        switch ( $role )
        {

            case ROLE_KhabarNegar:

                $group_roles = get_roles_by_level( ROLE_MozakarehKonandeh, $level );
                foreach ( $roles as $index => $role )
                {
                    if (in_array($role, $group_roles)) {
                        $roles[ $index ] = ROLE_MozakarehKonandeh;
                        continue 3;
                    }
                    // foreach ( $group_roles as $gp_role )
                    // {
                    //     if ( $gp_role == $role )
                    //     {

                    //         $roles[ $index ] = ROLE_MozakarehKonandeh;
                    //         continue 4;

                    //     }
                    // }
                }

                break;

            case ROLE_MozakarehKonandeh:

                $group_roles = get_roles_by_level( ROLE_KhabarNegar, $level );
                foreach ( $roles as $index => $role )
                {
                    if (in_array($role, $group_roles)) {
                        $roles[ $index ] = ROLE_KhabarNegar;
                        continue 3;
                    }
                    // foreach ( $group_roles as $gp_role )
                    // {
                    //     if ( $gp_role == $role )
                    //     {

                    //         $roles[ $index ] = ROLE_KhabarNegar;
                    //         continue 4;

                    //     }
                    // }
                }

                break;

        }

    }

    return $roles;
}

/**
 * @param int $level_id
 * @param array $out_roles
 * @return array
 */
function get_roles_for_create_server_new( int $level_id, array $out_roles = [] ) : array
{
    global $link;

    $out_roles = array_values( $out_roles );
    $roles     = $link->get_result( "SELECT * FROM `game_role` WHERE `level` = {$level_id} ORDER BY RAND()" );

    $list_roles = [];
    $skip_MozakarehKonandeh = false;
    $skip_KhabarNegar = false;
    

    if (in_array(ROLE_KhabarNegar, $out_roles) OR in_array(ROLE_MozakarehKonandeh, $out_roles)) {
        if (in_array(ROLE_KhabarNegar, $out_roles) AND in_array(ROLE_MozakarehKonandeh, $out_roles)) {
            $skip_MozakarehKonandeh = true;
            $skip_KhabarNegar = true;
        }
        elseif (in_array(ROLE_KhabarNegar, $out_roles)) {
            $skip_MozakarehKonandeh = true;
            $list_roles[] = ROLE_MozakarehKonandeh;
        }
        elseif (in_array(ROLE_MozakarehKonandeh, $out_roles)) {
            $skip_KhabarNegar = true;
            $list_roles[] = ROLE_KhabarNegar;
        }
    }
    
    foreach ( $roles as $role )
    {
        
        $roles_group = explode( ',', $role );
        if ( ($skip_KhabarNegar AND in_array(ROLE_KhabarNegar, $roles_group) ) OR ($skip_MozakarehKonandeh AND in_array(ROLE_MozakarehKonandeh, $roles_group))) {
            continue;
        }
        foreach ( $roles_group as $roles_datum )
        {
            if ( in_array( $roles_datum, $out_roles ) ) continue 2;
        }
       
        $list_roles[] =(int) $roles_group[ array_rand( $roles_group ) ];
        

    }
    shuffle($list_roles);

    return check_role_link_exits( $list_roles, $level_id );

}
function get_roles_for_create_server( int $level_id, array $out_roles = [] ) : array
{
    global $link;

    $out_roles = array_values( $out_roles );
    $roles     = $link->get_result( "SELECT * FROM `game_role` WHERE `level` = {$level_id} ORDER BY RAND()" );

    $list_roles = [];
    foreach ( $roles as $role )
    {

        $roles_data = explode( ',', $role->role_id );
        foreach ( $roles_data as $roles_datum )
        {
            if ( in_array( $roles_datum, $out_roles ) ) continue 2;
        }
        $list_roles[] = (int) $roles_data[ array_rand( $roles_data ) ];

    }

    return check_role_link_exits( $list_roles, $level_id );

}

/**
 * @param array $users
 * @param array $roles
 * @param array $users_roles
 * @return array
 */
function merge_role_with_users( array $users, array $roles, array $users_roles ) : array
{

    foreach ( $users as $user )
    {

        if ( ! isset( $users_roles[ $user ] ) )
        {

            $rand_role            = array_rand( $roles );
            $users_roles[ $user ] = $roles[ $rand_role ];
            unset( $roles[ $rand_role ] );

        }

    }

    return $users_roles;

}

/**
 * @param int $server_id
 * @param int $level_id
 * @return array
 * @throws \Throwable
 */
function set_role_user_by_server( int $server_id, int $level_id ) : array
{

    global $link;
    $save_roles = get_role_save_server( $server_id );
    $league_id = get_league_server_with_role( $server_id ); // Edit Sam
    $list_roles = [];
    if ( count( $save_roles ) > 0 )
    {

        foreach ( $save_roles as $role )
        {

            if ( role_hash_game( $league_id, $role->role_id ) )
            {

                if($role->type == 'paid'){
                    $list_roles[ $role->user_id ] = $role->role_id;
                }else if ($role->type == 'preselect'){
                    $_role = get_role( $role->role_id ); // Edit Sam
                    if ( demote_coin( $role->user_id, $_role->coin ) ){
                        $list_roles[ $role->user_id ] = $role->role_id;
                        
                        $link->where( 'id', $role->id )->update( "server_role", [
                            'type'  => 'paid'
                        ] );
    
                    }
                    else
                    {
    
                        $message = '⚠️ موجودی شما برای خرید نقش درخواستی کم است!';
                        SendMessage( $role->user_id, $message );
    
    
                    }
                    
                }

            }

            // $link->where( 'user_id', $role->user_id )->where( 'server_id', $server_id )->delete( 'server_role', 1 );

        }

    }

    $roles = get_roles_for_create_server( $level_id, $list_roles );
    $users = get_list_users( $server_id );
    return shuffle_assoc( merge_role_with_users( $users, $roles, $list_roles ) );

}

/**
 * @param int $server_id
 * @param int $group_id
 * @return array|false
 */
function get_attacker_list( int $server_id, int $group_id = 2 )
{
    global $link;
    return $link->get_result( "SELECT `role`.`id` AS `role_id`,`server_meta`.* FROM `server_meta` INNER JOIN `role` ON `server_meta`.`server_id` = {$server_id} AND `server_meta`.`meta_key` = 'select' AND `server_meta`.`user_id` = `role`.`id` AND `role`.`group_id` = {$group_id}" );
}

/**
 * @param int $server_id
 * @param int $group_id
 * @return \helper\Role|false
 */
function get_priority_attacker( int $server_id, int $group_id = 2 )
{
    global $link;

    if ( $group_id == 2 )
    {

        $god_father = get_role_by_user( $server_id, ROLE_Godfather ) ?? 0;
        if ( ! dead( $server_id, $god_father ) && ! is_user_in_prisoner( $server_id, $god_father ) ) return get_role( ROLE_Godfather );

    }

    $roles = $link->get_result( "SELECT `role`.* FROM `role` WHERE `group_id` = {$group_id} AND `id` != " . ROLE_Godfather . " ORDER BY `id` ASC" );
    foreach ( $roles as $role )
    {
        $user = get_role_by_user( $server_id, $role->id );
        if ( isset( $user ) && ! dead( $server_id, $user ) && ! is_user_in_prisoner( $server_id, $user ) )
        {
            return $role;
        }
    }
    return get_role( ROLE_Godfather );
}

/**
 * @param int $user_id
 * @param int $server_id
 * @param string $select
 * @return \library\User[]
 */
function get_list_attacker_by_user( int $user_id, int $server_id, string $select = 'select' ) : array
{
    global $link;
    $role_id = get_role_user_in_server( $user_id, $server_id )->role_id ?? 0;
    $users   = $link->get_result( "SELECT * FROM `server_meta` WHERE `meta_value` = {$user_id} AND `user_id` != {$role_id} AND `server_id` = {$server_id} AND `meta_key` = '{$select}'" );
    $list    = [];
    foreach ( $users as $user )
    {
        $list[] = new \library\User( get_role_by_user( $server_id, $user->user_id ) ?? 0, $server_id );
    }
    return $list;
}

/**
 * @param int $role_id
 * @param int $server_id
 * @return bool
 */
function role_exists( int $role_id, int $server_id ) : bool
{
    global $link;
    return (bool) $link->get_row( "SELECT * FROM `server_role` WHERE `role_id` = {$role_id} AND `server_id` = {$server_id}" );
}