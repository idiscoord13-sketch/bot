<?php

use library\Server;

/**

 * @param int $user_id

 * @return false|object|null

 */

function get_game( int $user_id = 0 )

{

    global $chat_id, $chatid, $link;

    if ( $user_id == 0 )

    {

        $user_id = $chat_id ?? $chatid;

    }

    if ( ! is_numeric( $user_id ) ) return false;

    return $link->get_row( "SELECT * FROM `user_game` WHERE `user_id` = {$user_id}" );

}

/**

 * @param int $user_id

 * @return bool

 */

function is_user_row_in_game( int $user_id = 0 ) : bool

{

    global $chat_id, $chatid, $link;

    if ( $user_id == 0 )

    {

        $user_id = $chat_id ?? $chatid;

    }

    if ( ! is_numeric( $user_id ) ) return false;

    return (bool) $link->get_row( "SELECT * FROM `user_game` WHERE `user_id` = {$user_id}" );

}

/**

 * @param int $user_id

 * @param int $server_id

 * @return bool|mixed

 * @throws Exception

 */

function add_game2( int $user_id, int $server_id ) {

    global $link;

    $link->insert( 'user_game', [

        'user_id'   => $user_id,

        'server_id' => $server_id,

        'bot'       => $_GET[ 'bot' ] ?? 0

    ] );

    return true;

}

function add_game( int $user_id, int $server_id )

{

    if ( ! is_user_row_in_game( $user_id ) )

    {

        global $link;

        $link->insert( 'user_game', [

            'user_id'   => $user_id,

            'server_id' => $server_id,

            'bot'       => $_GET[ 'bot' ] ?? 0

        ] );

        return true;

    }

    return update_game( $user_id, $server_id );

}

/**

 * @param int $user_id

 * @param int $server_id

 * @return bool|mixed

 * @throws Exception

 */

function update_game( int $user_id, int $server_id )

{

    if ( is_user_row_in_game( $user_id ) )

    {

        global $link;

        $link->where( 'user_id', $user_id )->update( 'user_game', [

            'server_id' => $server_id

        ] );

        return true;

    }

    return add_game( $user_id, $server_id );

}

/**

 * @param int $server_id

 * @return array|false

 */

function get_users_by_server( int $server_id )

{

    global $link;

    if ( get_server( $server_id )->status != 'started' )

    {

        $results = $link->get_result( "SELECT * FROM `user_game` INNER JOIN `users` ON `user_game`.user_id = `users`.user_id AND `user_game`.server_id = {$server_id} ORDER BY `user_game`.`created_at` ASC" );

    }

    else

    {

        $results = $link->get_result( "SELECT * FROM `server_role` INNER JOIN `users` ON `server_role`.user_id = `users`.user_id AND `server_role`.server_id = {$server_id} ORDER BY `server_role`.`id` ASC" );

    }

    return apply_filters( 'filter_name_player_in_game', $results );

}

/**

 * @param int $server_id

 * @return array

 */

function get_list_users( int $server_id ) : array

{

    global $link;

    $result = $link->get_result( "SELECT * FROM `user_game` WHERE `server_id` = {$server_id} ORDER BY `created_at` ASC" );

    $users  = [];

    foreach ( $result as $item )

    {

        $users[] = $item->user_id;

    }

    return $users;

}

/**

 * @param object|\helper\Server $server

 * @param string $prefix_body

 * @return bool

 * @throws Exception

 */

function game_mostaghel( object $server, string $prefix_body = '' ) : bool

{

    $Server = new Server( $server->id );

    $users_server       = $Server->users();

    $count_users_server = count( $users_server );

    $dead_body       = $Server->getDeadUsers();

    $count_dead_body = count( $dead_body );

    $group = - 1;

    if ( $count_dead_body == $count_users_server ) $group = 0;

    if ( $Server->getCountDeadCity() == $Server->getCountCity() ) $group = 3;

    if ( $Server->getCountDeadMostaghel() == $Server->getCountMostaghel() ) $group = 1;

    if ( $group > - 1 ) return result_server( $server, $group, $prefix_body );

    return false;

}

/**

 * @param object|\helper\Server $server

 * @param string $prefix_body

 * @return bool

 * @throws Exception

 */

function check_status_game( object $server, string $prefix_body = '' ) : bool

{

    global $link,$chatid, $chat_id;

    if ( get_server_meta( $server->id, 'status' ) == 'chatting' ) return false;

    if ( get_server_meta( $server->id, 'day' ) <= 1 ) return false;

    if ( $server->league_id == LEAGUE_MOSTAGHEL ) return game_mostaghel( $server, $prefix_body );

    $Server = new Server( $server->id );

    $message   = false;

    $dead_body = (int) $link->get_var( "SELECT COUNT(*) FROM `server_meta` WHERE `server_id` = {$server->id} AND `meta_key` = 'status' AND (`meta_value` = 'dead' OR `meta_value` = 'killed') AND `user_id` != 0" );

    $server_count = count( get_users_by_server( $server->id ) );

    $count_city           = $Server->getCountCity();

    $count_city_dead      = $Server->getCountDeadCity();

    $count_terror         = $Server->getCountTerror();

    $count_terror_dead    = $Server->getCountDeadTerror();

    $count_mostaghel      = $Server->getCountMostaghel();

    $count_mostaghel_dead = $Server->getCountDeadMostaghel();

    $count_amazing        = $Server->getCountAmazing();

    $count_amazing_dead   = $Server->getCountDeadAmazing();

    if ( $dead_body == $server_count )

    {

        $message = '🏁 بازی تمام شد.' . "\n";

        $message .= '⚪️ بازی مساوی شد!' . "\n \n";

        $group   = 0;

    }

    elseif ( $count_terror == $count_terror_dead && $count_mostaghel == $count_mostaghel_dead && $count_amazing == $count_amazing_dead )

    {

        $message = '🏁 بازی تمام شد.' . "\n";

        $message .= '🟢 شهروندان برنده بازی شدند!' . "\n \n";

        $group   = 1;

    }

    elseif ( $count_mostaghel == $count_mostaghel_dead && $count_amazing == $count_amazing_dead && $count_city == $count_city_dead )

    {

        $message = '🏁 بازی تمام شد.' . "\n";

        $message .= '🔴 گروه مافیا برنده بازی شدند!' . "\n \n";

        $group   = 2;

    }

    elseif ( $count_city == $count_city_dead && $count_terror == $count_terror_dead && $count_mostaghel > 0 )

    {

        $message = '🏁 بازی تمام شد.' . "\n";

        $message .= '🟡 مستقل برنده بازی شد!' . "\n \n";

        $group   = 3;

    }

    elseif ( $dead_body == ( $server_count - 2 ) && $count_mostaghel != $count_mostaghel_dead )

    {

        $free_time = (int) get_server_meta( $server->id, 'free-time' );

        if ( $free_time == 2 )

        {

            $message = '🏁 بازی تمام شد.' . "\n";

            $message .= '🟡 مستقل برنده بازی شد!' . "\n \n";

            $group   = 3;

        }

        else

        {

            add_server_meta( $server->id, 'free-time', $free_time + 1 );

        }

    }

    elseif ( $count_city == $count_city_dead && $count_terror == $count_terror_dead && $count_amazing > 0 )

    {

        $message = '🏁 بازی تمام شد.' . "\n";

        $message .= '🟣 شگفت انگیر برنده بازی شد!' . "\n \n";

        $group   = 4;

    }

    elseif ( $dead_body == ( $server_count - 2 ) && $count_amazing != $count_amazing_dead )

    {

        $free_time = (int) get_server_meta( $server->id, 'free-time' );

        if ( $free_time == 2 )

        {

            $message = '🏁 بازی تمام شد.' . "\n";

            $message .= '🟣 شگفت انگیر برنده بازی شد!' . "\n \n";

            $group   = 4;

        }

        else

        {

            add_server_meta( $server->id, 'free-time', $free_time + 1 );

        }

    }

    if ( $message )

    {

        return result_server( $server, $group, $prefix_body );

    }

    if ( $Server->role_exists( ROLE_Bazmandeh ) ) return if_bazmandeh_exists( $server, $prefix_body );

    return false;

}

/**

 * @param $server_id

 * @return array|false

 */

function get_dead_body( $server_id )

{

    global $link;

    return $link->get_result( "SELECT * FROM `server_meta` WHERE `server_id` = {$server_id} AND `meta_key` = 'status' AND (`meta_value` = 'dead' or `meta_value` = 'killed') AND `user_id` != 0" );

}

/**

 * @param object|\helper\Server $server

 * @throws Exception

 */

function if_bazmandeh_exists( object $server, string $prefix_body ) : bool

{

    global $link;

    if ( role_exists( ROLE_Bazmandeh, $server->id ) )

    {

        $user_role = get_role_by_user( $server->id, ROLE_Bazmandeh );

        if ( ! dead( $server->id, $user_role ) )

        {

            $count_terror = (int) $link->get_var(

                "SELECT COUNT(*) FROM `role` 

                                                INNER JOIN `server_role` ON 

                                                `server_role`.`server_id` = {$server->id} AND

                                                `server_role`.`role_id` = `role`.`id` AND

                                                `role`.`group_id` = 2"

            );

            $count_terror_2 = (int) $link->get_var(

                "SELECT COUNT(*) FROM `role` 

    INNER JOIN `server_role` ON `server_role`.`server_id` = {$server->id} AND `server_role`.`role_id` = `role`.`id` AND `role`.`group_id` = 2

    INNER JOIN `server_meta` ON `server_meta`.`server_id` = {$server->id} AND `server_meta`.`user_id` = `server_role`.`user_id` AND `server_meta`.`meta_key` = 'status' AND (`server_meta`.`meta_value` = 'dead' or `server_meta`.`meta_value` = 'killed')"

            );

            $count_city   = (int) $link->get_var(

                "SELECT COUNT(*) FROM `role` 

                                                INNER JOIN `server_role` ON 

                                                    `server_role`.`server_id` = {$server->id} AND 

                                                    `server_role`.`role_id` = `role`.`id` AND 

                                                    `role`.`group_id` = 1"

            );

            $count_city_2 = (int) $link->get_var(

                "SELECT COUNT(*) FROM `role` 

    INNER JOIN `server_role` ON `server_role`.`server_id` = {$server->id} AND `server_role`.`role_id` = `role`.`id` AND `role`.`group_id` = 1

    INNER JOIN `server_meta` ON `server_meta`.`server_id` = {$server->id} AND `server_meta`.`user_id` = `server_role`.`user_id` AND `server_meta`.`meta_key` = 'status' AND (`server_meta`.`meta_value` = 'dead' or `server_meta`.`meta_value` = 'killed')"

            );

            if ( $count_city == $count_city_2 )

            {

                $group = 2;

            }

            if ( $count_terror == $count_terror_2 )

            {

                $group = 1;

            }

            if ( $count_city == $count_city_2 || $count_terror == $count_terror_2 )

            {

                add_server_meta( $server->id, '-point', 2 );

                return result_server( $server, $group, $prefix_body );

            }

        }

    }

    return false;

}

/**

 * @param object|\helper\Server $server

 * @param int $group

 * @param string $prefix_body

 * @return bool

 * @throws Exception

 */

function result_server( object $server, int $group, string $prefix_body = '' ) : bool

{

    global $chat_id, $chatid, $link, $telegram;
    // $chatid = $chatid ?? $chat_id;
    $user_id = $chat_id ?? $chatid;

    $message = '🏁 بازی تمام شد.' . "\n";

    switch ( $group )

    {

        case 1:

            $message .= '🟢 شهروندان برنده بازی شدند!' . "\n \n";

            break;

        case 2:

            $message .= '🔴 گروه مافیا برنده بازی شدند!' . "\n \n";

            break;

        case 3:

            $message .= '🟡 مستقل برنده بازی شد!' . "\n \n";

            break;

        case 4:

            $message .= '🟣 شگفت انگیر برنده بازی شد!' . "\n \n";

            break;

        default:

            $message .= '⚪️ بازی مساوی شد!' . "\n \n";

            break;

    }

    if ( $prefix_body !== '' )

    {

        $message .= $prefix_body . "\n \n";

    }

    $users_server = get_users_by_server( $server->id );

    $bazmandeh    = get_role_by_user( $server->id, ROLE_Bazmandeh );

    $dead_body    = (int) $link->get_var( "SELECT COUNT(*) FROM `server_meta` WHERE `server_id` = {$server->id} AND `meta_key` = 'status' AND (`meta_value` = 'dead' OR `meta_value` = 'killed') AND `user_id` != 0" );

    $server_count = count( $users_server );

    $negative_point = (int) get_server_meta( $server->id, '-point' );

    $Server = new Server( $server->id );
    $point_easy = 6;
    $point_hard = 11;
    $point_vip = 18;
    $point_super = 45;
    $kllen_kill = 0;

    if ( in_array($group, [1,2])  && $Server->getCountDeadCity() == 0 || $Server->getCountDeadTerror() == 0 )
    {
        switch ( $server->league_id ) {
            case 2:
                $kllen_kill = 3;
                break;
            case 4:
                $kllen_kill = 7;
                break;
            default:
                $kllen_kill = 2;
                break;
        }

        
         

    }

    if ( in_array($group, [3,4])  )

    {

        switch ( $server->league_id ) {
            case 2:
                $kllen_kill = 4;
                break;
            case 4:
                $kllen_kill = 12;
                break;
            default:
                $kllen_kill = 3;
                break;
        }

    }

    foreach ( $users_server as $id => $item )

    {

        $User = new \library\User( $item->user_id, $server->id );

        $prefix = '';

        if ( is_server_meta( $server->id, 'friend', $item->user_id ) )

        {

            $prefix = get_emoji_for_friendly( get_server_meta( $server->id, 'friend', $item->user_id ) );

        }

        $point  = 0;

        $status = get_server_meta( $server->id, 'status', $item->user_id );

        $role   = $User->get_role();

        if ( $group == $role->group_id )

        {

            $User->addWinGame();

        }

        else

        {

            $User->addLostGame();

        }

        if ( isset( $group ) && $group == $role->group_id )

        {

            switch ( $role->group_id )

            {

                case 3:

                    // if ( ($status != 'dead' AND $status != 'killed' ) && ! in_array( $server->league_id, [ LEAGUE_MOSTAGHEL ] ) && ( $dead_body == $server_count - 2 || $dead_body == $server_count - 1 ) )

                    if ( $status == 'live'  && ! in_array( $server->league_id, [ LEAGUE_MOSTAGHEL ] ) && ( $dead_body == $server_count - 2 || $dead_body == $server_count - 1 ) )

                    {

                        if ( $server->type != 'private' && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

                        {

                            switch ( $server->league_id )

                            {

                                case 1:

                                    $point = $point_easy + $kllen_kill;

                                    //$point = 13;

                                    break;

                                case 2:

                                case 3:

                                    $point = $point_hard + $kllen_kill;

                                    //$point = 17;

                                    break;

                                case 4:

                                    $point = $point_vip + $kllen_kill;

                                    //$point = 17;

                                    break;

                                case 5:

                                    $point = $point_super + $kllen_kill;

                                    //$point = 17;

                                    break;

                            }

                        }

                        $message .= "<b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . $User->get_name() . '🟡' . " " . $role->icon . " +" . $point . " امتیاز 🌟" . "\n";

                        $User->add_point( $point );

                        continue 2;

                    }

                    break;

            }

            if ( $status == 'dead' OR $status == 'killed')

            {

                if ( ! is_server_meta( $server->id, 'exit', $item->user_id ) )

                {

                    if ( $server->type != 'private' && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

                    {

                        switch ( $server->league_id )

                        {

                            case 1:

                                $point = 1 + $kllen_kill;

                                //$point = 4;

                                break;

                            case 2:

                            case 3:

                                $point = 5 - $negative_point + $kllen_kill;

                                //$point = 7 - $negative_point;

                                if ( $role->id == ROLE_Fadaii && $group == 1 && get_server_meta( $server->id, 'fadaii' ) == 'use' )

                                {

                                    $point = 8 - $negative_point + $kllen_kill;

                                }

                                if ( $role->id == ROLE_Bodygard && $group == 1 && get_server_meta( $server->id, 'bodygard' ) == 'use' )

                                {

                                    $point = 8 - $negative_point + $kllen_kill;

                                }

                                if ( $role->id == ROLE_Yakoza && get_server_meta( $server->id, 'yakoza' ) == 'use' )

                                {

                                    $point = 8 - $negative_point + $kllen_kill;

                                }

                                break;

                            case 4:

                                $point = 4 - $negative_point + $kllen_kill;

                                //$point = 7 - $negative_point;

                                if ( $role->id == ROLE_Fadaii && $group == 1 && get_server_meta( $server->id, 'fadaii' ) == 'use' )

                                {

                                    $point = 14 - $negative_point + $kllen_kill;

                                }

                                if ( $role->id == ROLE_Bodygard && $group == 1 && get_server_meta( $server->id, 'bodygard' ) == 'use' )

                                {

                                    $point = 14 - $negative_point + $kllen_kill;

                                }

                                if ( $role->id == ROLE_Yakoza && get_server_meta( $server->id, 'yakoza' ) == 'use' )

                                {

                                    $point = 14 - $negative_point + $kllen_kill;

                                }

                                break;

                            case 5:

                                $point = 12 - $negative_point + $kllen_kill;

                                //$point = 7 - $negative_point;

                                if ( $role->id == ROLE_Fadaii && $group == 1 && get_server_meta( $server->id, 'fadaii' ) == 'use' )

                                {

                                    $point = 30 - $negative_point + $kllen_kill;

                                }

                                if ( $role->id == ROLE_Joker && $group == 3 && get_server_meta( $server->id, 'joker' ) == 'yes' )

                                {

                                    $point = 45 + $kllen_kill;

                                }

                                if ( $role->id == ROLE_Yakoza && get_server_meta( $server->id, 'yakoza' ) == 'use' )

                                {

                                    $point = 30 - $negative_point + $kllen_kill;

                                }

                                if ( $role->id == ROLE_Bodygard && $group == 1 && get_server_meta( $server->id, 'bodygard' ) == 'use' )

                                {

                                    $point = 30 - $negative_point + $kllen_kill;

                                }

                                break;

                        }

                    }

                    $message .= "<s><b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . $User->get_name() . " " . ( emoji_group( $role->group_id ) ) . " " . $role->icon . "</s> +" . $point . " امتیاز 🌟" . "\n";

                }

                else

                {

                    $message .= "<s><b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . $User->get_name() . " " . ( emoji_group( $role->group_id ) ) . " " . $role->icon . "</s>" . "\n";

                }

            }

            else

            {

                if ( $server->type != 'private' && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

                {

                    switch ( $server->league_id )

                    {

                        case 1:

                            $point = $point_easy + $kllen_kill;

                            //$point = 10;

                            break;

                        case 2:

                        case 3:

                            $point = $point_hard - $negative_point + $kllen_kill;

                            break;

                        case 4:

                            $point = $point_vip - $negative_point + $kllen_kill;

                            break;

                        case 5:

                            $point = $point_super - $negative_point + $kllen_kill;

                            break;

                    }

                    $message .= "<b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . $User->get_name() . ( emoji_group( $role->group_id ) ) . " " . $role->icon . " +" . $point . " امتیاز 🌟" . "\n";

                }

                else

                {

                    $message .= "<b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . $User->get_name() . ( emoji_group( $role->group_id ) ) . " " . $role->icon . "\n";

                }

            }

        }

        elseif ( $role->id == ROLE_Joker && get_server_meta( $server->id, 'joker' ) == 'yes' && $role->group_id == 3 )

        {

            if ( $server->type != 'private' && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

            {

                switch ( $server->league_id )

                {

                    case 1:

                        $point = 5;

                        //$point = 10;

                        break;

                    case 2:

                    case 3:

                        $point = 8;

                        //$point = 15;

                        break;

                    case 4:

                        $point = 14;

                        break;

                    case 5:

                        $point = 30;

                        break;

                }

            }

            $message .= "<s><b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . " " . $User->get_name() . " " . '🟡' . " " . $role->icon . "</s> +" . $point . " امتیاز 🌟" . "\n";

        }

        elseif ( $role->id == ROLE_Jalad && $User->is_user_in_game() && get_server_meta( $server->id, 'power', ROLE_Jalad ) == 10 )

        {

            if ( $server->type != 'private' && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

            {

                switch ( $server->league_id )

                {

                    case 1:

                        $point = 5;

                        break;

                    case 2:

                    case 3:

                        $point = 8;

                        break;

                    case 4:

                        $point = 16;

                        break;

                    case 5:

                        $point = 30;

                        break;

                }

            }

            $message .= "<s><b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . " " . $User->get_name() . " " . '🟡' . " " . $role->icon . "</s> +" . $point . " امتیاز 🌟" . "\n";

        }

        elseif ( $server->league_id != LEAGUE_MOSTAGHEL && isset( $bazmandeh ) && ! dead( $server->id, $bazmandeh ) && $role->group_id == 3 )

        {

            if ( $server->type != 'private' && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

            {

                $point = 8 + $kllen_kill;

            }

            $message .= "<b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . " " . $User->get_name() . " " . '🟡' . " " . $role->icon . " +" . $point . " امتیاز 🌟" . "\n";

            /*if ( $getPoint )

            {

                $User->addWinGame();

            }*/

        }

        else

        {

            // if (( $status != 'dead' && $status != 'killed') && $server->type != 'private' && $group != 0 && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

            if (( $status == 'live' ) && $server->type != 'private' && $group != 0 && ! is_server_meta( $server->id, 'get-point', $item->user_id ) )

            {

                switch ( $server->league_id )

                {

                    case 1:

                        $point = 1 + $kllen_kill;

                        break;

                    case 2:

                    case 3:

                        $point = 3 - $negative_point + $kllen_kill;

                        break;

                    case 4:

                        $point = 4 - $negative_point + $kllen_kill;

                        break;

                    case 5:

                        $point = 12 - $negative_point + $kllen_kill;

                        break;

                }

                $message .= "<s><b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . " " . $User->get_name() . " " . ( emoji_group( $role->group_id ) ) . " " . $role->icon . "</s> +" . $point . " امتیاز 🌟" . "\n";

            }

            else

            {

                $message .= "<s><b>" . ( $id + 1 ) . ".</b> " . $prefix . ' ' . $User->get_name() . ( emoji_group( $role->group_id ) ) . " " . $role->icon . "</s>" . "\n";

            }

        }

        if ( $point > 0 )

        {

            add_point( $server->id, $item->user_id, $point );

        }

        $User->genderChange( $message )->changeStatusLastGame();

    }

    $message .= "\n" . 'خسته نباشید 🌷' . "<a href='tg://user?id=" . $server->id . "'> </a>" . "\n \n" . 'کل کل بعد بازی 👈 ⏱ ۳۰ ثانیه' . "\n \n" . 'کانال رسمی : @iranimafia';
    sleep(5);
    if (in_array($server->league_id, [2,4])) {
        $keyboard = [];
        
        $Server_2 = new Server( $server->id );
        
        $i = 0;
        $i2 = 0;
        $users_server2       = $Server_2->users();

        // $fopen = fopen(BASE_DIR  . '/best_player.txt', 'a');
        // $fwrite = "best_player \n";
        // $fwrite .= "chatid {$chatid}\n";
        // $fwrite .= "chat_id {$chat_id}\n";
        // $fwrite .= "user_id {$user_id}\n";
    

        foreach ( $users_server2 as $item )
        {
            // $fwrite .= "item {$item->getUserId()}\n";
            // $fwrite .= "getRoleId {$item->getRoleId()}\n";
           
            $keyboard[$i][] = $telegram->buildInlineKeyboardButton( '⭐️ ' . $item->get_name(), '', '/best_player-' . $server->id . '-' . $item->getUserId() . '-' . $item->getRoleId()  );
            $i2++;
            if ($i2 % 2  === 0)
                $i++;
            
        }
        // fwrite($fopen, $fwrite);
        // fclose($fopen);
    }

    foreach ( $users_server as $item )

    {

        if ( is_user_in_game( $server->id, $item->user_id ) )

        {   

            $result = SendMessage( $item->user_id, $message, KEY_GAME_END_MENU, null, 'html' );

            if ( empty( $result->message_id ) )

            {

                $message_2 = '⛔️ در اعلام نتیجه مشکلی رخ داده است.' . "\n \n" . 'کل کل بعد بازی 👈 ⏱ ۳۰ ثانیه' . "\n \n" . 'کانال رسمی : @iranimafia';

                SendMessage( $item->user_id, $message_2, KEY_GAME_END_MENU, null, 'html' );
                
            }
            if (in_array($server->league_id, [2,4])) {
                SendMessage( $item->user_id, "🔅 بنظرت کدوم پلیر این دست بهتر بازی کرد ؟ \n🔻 اینجا میتونی بهش رای بدی :", $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );
            }
            update_status( 'last_chat', $item->user_id );

        }

    }
    $keyboard = [];
    $Server->charge( 30 )->setStatus( 'chatting' );

    return true;

}