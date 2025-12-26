<?php


use library\Server;


/**
 * @return false|int|string
 */
function get_cron_number()
{
    $filename = BASE_DIR . '/server.txt';
    // $count = (count(scan_dir(BASE_DIR . '/temp/')) - 1);
    $count = 7;
    if ( file_exists( $filename ) )
    {
        $server = file_get_contents( $filename );
        if ( $server <= $count && $server > 0 )
        {
            if ( $server == $count )
            {
                file_put_contents( $filename, 1 );
            }
            else
            {
                file_put_contents( $filename, $server + 1 );
            }
            return $server;
        }
    }
    file_put_contents( $filename, 1 );
    return 1;
}

/**
 * @param int $league_id
 * @return int
 * @throws Exception
 */
function get_server_by_league( int $league_id ) : int
{
    global $link;
    $league = get_league( $league_id );
    if ( ! $league ) return false;
    $server = $link->get_row( "SELECT * FROM `server` WHERE `league_id` = {$league_id} AND `count` < {$league->count} AND `type` = 'public' AND `status` = 'opened' ORDER BY `id` ASC LIMIT 1" );
    if ( ! $server )
    {
        return create_server( $league_id );
    }
    return $server->id;
}

/**
 * @param int $league_id
 * @return int
 * @throws \Exception
 */
function create_server( int $league_id ) : int
{
    global $chat_id, $chatid, $link;
    if ( $user_id == 0 )
    {
        $user_id = $chat_id ?? $chatid;
    }

    if ( $league_id <= 0 ) throw new Exception( 'خطا در شماره لیگ بازی' );
    $link->insert( 'server', [
        'league_id'  => $league_id,
        'count'      => 0,
        'status'     => 'opened',
        'user_id'    => $user_id,
        'cron'       => get_cron_number(),
        'bot'        => $_GET[ 'bot' ] ?? 0,
        'created_at' => date( 'Y-m-d H:i:s' )
    ] );
    return $link->getInsertId();
}

/**
 * @param int $server_id
 * @return false|object|null
 */
function get_server( int $server_id )
{
    global $link;
    return $link->get_row( "SELECT * FROM `server` WHERE `id` = {$server_id}" );
}

function get_random_mafia( int $server_id )
{
    global $link;
	$result = $link->get_result( "SELECT sr.user_id
FROM server_role sr
INNER JOIN role r ON sr.role_id = r.id
WHERE sr.server_id = $server_id
  AND r.group_id = 2;" );
	$randomKey = array_rand($result);
	
    return $result[$randomKey];
}

/**
 * @param int $server_id
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_server( int $server_id, array $data ) : bool
{
    global $link;
    if ( ! is_numeric( $server_id ) ) return false;
    if ( isset( $data[ 'status' ] ) && $data[ 'status' ] == 'closed' )
    {
        $link->where( 'id', $server_id )->delete( 'server' );
        $link->where( 'server_id', $server_id )->delete( 'server_meta' );
        $link->where( 'server_id', $server_id )->delete( 'server_role' );
        return true;
    }
    return $link->where( 'id', $server_id )->update( 'server', $data );
}

/**
 * @param int $server_id
 * @param int $user_id
 * @param int|null $level
 * @return bool
 * @throws Exception
 */
function add_user_to_server( int $server_id, int $user_id = 0, ?int $level = null ) : bool
{
    global $chat_id, $chatid, $link;
    if ( $user_id == 0 )
    {
        $user_id = $chat_id ?? $chatid;
    }

    if ( is_user_row_in_game( $user_id ) ) return false;

    $server         = get_server( $server_id );
    $league         = get_league( $level ?? $server->league_id );
    $members_server = get_count_member_on_server( $server_id ); // Edit Sam
    // $members_server = $server->count;

    if ( $members_server < $league->count )
    {

        if (add_game( $user_id, $server_id )) { // Edit Sam
            // $members_server = get_count_member_on_server( $server_id ); // Edit Sam
            $link->where( 'id', $server_id )->update( 'server', [

                'count' => $members_server + 1

            ] );

            return true;
        }
        else { // Edit Sam
            return false;
            // $server_id = get_server_by_league( $server->league_id );
            // return add_user_to_server( $server_id, $user_id );
        }

    }
    else
    {
        return false;

        // $server_id = get_server_by_league( $server->league_id );
        // return add_user_to_server( $server_id, $user_id );

    }

}

/**
 * @param int $user_id
 * @param int $server_id
 * @return bool
 */
function is_role_to_user_for_server( int $user_id, int $server_id ) : bool
{
    global $link;
    return (bool) $link->get_row( "SELECT * FROM `server_role` WHERE `user_id` = {$user_id} AND `server_id` = {$server_id}" );
}

/**
 * @param int $user_id
 * @param int $server_id
 * @param int $role_id
 * @return bool
 * @throws \Exception
 */
function add_role_to_user_for_server( int $user_id, int $server_id, int $role_id ) : bool
{
    global $link;

    if ( ! is_role_to_user_for_server( $user_id, $server_id ) && ! role_exists( $role_id, $server_id ) )
    {

        try
        {

            $link->insert( 'server_role', [
                'user_id'   => $user_id,
                'server_id' => $server_id,
                'role_id'   => $role_id,
                'type'   => 'random',
            ] );

        }
        catch ( Exception $e )
        {

            throw new Exception( "ERROR ON INSERT ROLE USER " . $user_id . ' ON SERVER ' . $server_id . ' MESSAGE ' . $e->getMessage() );

        }

    }

    return true;
}

/**
 * @param int $user_id
 * @param int $server_id
 * @param int $role_id
 * @return bool|mixed
 * @throws Exception
 */
function update_role_to_user_for_server( int $user_id, int $server_id, int $role_id )
{
    global $link;
    if ( is_role_to_user_for_server( $user_id, $server_id ) )
    {
        $link->where( 'user_id', $user_id )->where( 'server_id', $server_id )->update( 'server_role', [
            'user_id'   => $user_id,
            'server_id' => $server_id,
            'role_id'   => $role_id
        ] );
        return true;
    }
    return add_role_to_user_for_server( $user_id, $server_id, $role_id );
}

/**
 * @param int $user_id
 * @param int $server_id
 * @return false|object|null
 */
function get_role_user_in_server( int $user_id, int $server_id )
{
    global $link;
    return $link->get_row( "SELECT * FROM `server_role` WHERE `user_id` = {$user_id} AND `server_id` = {$server_id}" );
}

/**
 * @param int $server_id
 * @param string $key
 * @param int $user_id
 * @return bool
 */
function is_server_meta( int $server_id, string $key, int $user_id = 0 ) : bool
{
    global $link;
    return (bool) $link->get_row( "SELECT * FROM `server_meta` WHERE `server_id` = {$server_id} AND `user_id` = {$user_id} AND `meta_key` = '{$key}'" );
}

/**
 * @param int $server_id
 * @param string $key
 * @param string $value
 * @param int $user_id
 * @return bool|mixed
 * @throws Exception
 */
function add_server_meta( int $server_id, string $key, string $value, int $user_id = 0 )
{
    global $link;
    if ( ! is_server_meta( $server_id, $key, $user_id ) )
    {
        $link->insert( 'server_meta', [
            'user_id'    => $user_id,
            'server_id'  => $server_id,
            'meta_key'   => $key,
            'meta_value' => $value
        ] );
        return true;
    }
    return update_server_meta( $server_id, $key, $value, $user_id );
}

/**
 * @param int $server_id
 * @param string $key
 * @param string $value
 * @param int $user_id
 * @return bool|mixed
 * @throws Exception
 */
function update_server_meta( int $server_id, string $key, string $value, int $user_id = 0 )
{
    global $link;
    if ( is_server_meta( $server_id, $key, $user_id ) )
    {
        $link->where( 'server_id', $server_id )->where( 'user_id', $user_id )->where( 'meta_key', $key )->update( 'server_meta', [
            'user_id'    => $user_id,
            'server_id'  => $server_id,
            'meta_key'   => $key,
            'meta_value' => $value
        ] );
        return true;
    }
    return add_server_meta( $server_id, $key, $value, $user_id );
}

/**
 * @param int $server_id
 * @param string $key
 * @param int $user_id
 * @return bool
 * @throws Exception
 */
function delete_server_meta( int $server_id, string $key, int $user_id = 0 ) : bool
{
    global $link;
    if ( is_server_meta( $server_id, $key, $user_id ) )
    {
        $link->where( 'server_id', $server_id )->where( 'user_id', $user_id )->where( 'meta_key', $key )->delete( 'server_meta' );
        return true;
    }
    return false;
}



function get_server_meta_user( int $server_id, string $key,  $meta_value  )
{
    global $link;
    return $link->get_var( "SELECT `user_id` FROM `server_meta` WHERE `server_id` = {$server_id} AND `meta_value` = {$meta_value} AND `meta_key` = '{$key}'" );
}

/**
 * @param int $server_id
 * @param string $key
 * @param int $user_id
 * @return false|object|null
 */
function get_server_meta( int $server_id, string $key, int $user_id = 0 )
{
    global $link;
    return $link->get_var( "SELECT `meta_value` FROM `server_meta` WHERE `server_id` = {$server_id} AND `user_id` = {$user_id} AND `meta_key` = '{$key}'" );
}

/**
 * @param int $server_id
 * @param int $user_id
 * @return \helper\Role
 */
function get_role_user_server( int $server_id, int $user_id )
{
    global $link;
    return $link->get_row( "SELECT `role`.* FROM `server_role` INNER JOIN `role` ON `server_role`.server_id = {$server_id} AND `server_role`.user_id = {$user_id} AND `server_role`.role_id = `role`.id" );
}

/**
 * @param int $server_id
 * @param int $role_id
 * @return false|mixed
 */
function get_role_by_user( int $server_id, int $role_id )
{
    global $link;
    return $link->get_var( "SELECT `user_id` FROM `server_role` WHERE `server_id` = {$server_id} AND `role_id` = {$role_id}" );
}

/**
 * @param int $user_id
 * @return null|\helper\Server
 */
function is_user_in_which_server( int $user_id )
{
    global $link;
    return $link->get_row( "SELECT `server`.* FROM `server` INNER JOIN `user_game` ON `server`.`id` = `user_game`.`server_id` AND `user_game`.`user_id` = {$user_id} LIMIT 1" );
}

/**
 * @param int $user_id
 * @return bool
 * @throws Exception
 */
function logout_server( int $user_id ) : bool
{
    global $link;
    if ( is_user_row_in_game( $user_id ) )
    {

        $game = get_game( $user_id );
        if ( empty( $game->server_id ) ) return true;
        $server = get_server( $game->server_id );
        //todo  this line is log
        if (is_null($server)){
            $mmm='null server:'.$user_id.'-:'.$game->server_id;
            SendMessage( 56288741, $mmm, KEY_GAME_ON_MENU, null, 'html' );
            return true;
        }
        //todo  this line is log
        if(is_null(get_league( $server->league_id ))){
            $league = 9;
        }else{
            $league = get_league( $server->league_id );
        }
        $count  = get_count_member_on_server( $server->id );

        if ( $league->count > $count || get_server_meta( $server->id, 'status' ) == 'chatting' )
        {

            $link->where( 'user_id', $user_id )->delete( 'user_game' );

            if ( $server->status != 'started' )
            {
                $link->where( 'user_id', $user_id )->where( 'server_id', $server->id )->delete( 'server_role', 1 );
            }

            delete_name_user( $game->server_id, $user_id );

            $link->where( 'id', $game->server_id )->update( 'server', [
                'count' => $count - 1
            ] );

            return true;

        }

    }
    return false;
}

/**
 * @param int $user_id
 * @return bool
 * @throws \Exception
 */
function leave_server( int $user_id ) : bool
{
    global $link;
    if ( is_user_row_in_game( $user_id ) )
    {

        $game   = get_game( $user_id );
        $server = get_server( $game->server_id );
        if ( $server->status == 'opened' )
        {
            $link->where( 'id', $game->server_id )->update( 'server', [
                'count' => $server->count - 1
            ] );
        }
        add_server_meta( $game->server_id, 'exit', 'yes', $user_id );
        update_status( '', $user_id );
        delete_name_user( $game->server_id, $user_id );
        return $link->where( 'user_id', $user_id )->delete( 'user_game' );

    }
    return false;
}

/**
 * @param int $user_id
 * @param int|null $league_id
 * @return int
 * @throws Exception
 */
function add_user_server( int $user_id, ?int $league_id = null ) : int
{
    global $chat_id, $chatid, $link;
    if ( $league_id === null )
    {
        $user_league = get_league_user( $user_id );
        $league_id   = $user_league->id;
    }
    if ( $user_id == 0 )
    {
        $user_id = $chat_id ?? $chatid;
    }
    $link->insert( 'server', [
        'user_id'    => $user_id,
        'league_id'  => $league_id,
        'cron'       => get_cron_number(),
        'bot'        => $_GET[ 'bot' ] ?? 0,
        'created_at' => date( 'Y-m-d H:i:s' )
    ] );
    $id = $link->getInsertId();
    add_user_to_server( $id, $user_id, $league_id );
    return $id;
}

/**
 * @return false|object|null
 */
function find_server( int $level )
{
    global $link;
    return $link->get_row( "SELECT * FROM `server` WHERE `type` = 'public' AND `status` = 'opened' AND `league_id` = {$level} ORDER BY `id` ASC LIMIT 1" );
}

/**
 * @param int $server_id
 * @param int $user_id
 * @return bool
 */
function dead( int $server_id, int $user_id ) : bool
{
    $dead = false;
    if ( $server_id > 0 && $user_id > 0 ) {
        if (get_server_meta( $server_id, 'status', $user_id ) == 'dead' OR get_server_meta( $server_id, 'status', $user_id ) == 'killed')
            $dead = true;
    };
    return $dead;
}
function killed( int $server_id, int $user_id ) : bool
{
    $dead = false;
    if ( $server_id > 0 && $user_id > 0 ) {
        if (get_server_meta( $server_id, 'status', $user_id ) == 'killed')
            $dead = true;
    };
    return $dead;
}

/**
 * @param int $server_id
 * @param int $user_id
 * @throws Exception
 */
function kill( int $server_id, int $user_id, string $type = 'dead')
{
    if ( $server_id > 0 && $user_id > 0 ){
        update_server_meta( $server_id, 'status', $type, $user_id );
        $day = 1 ;
        if(get_server_meta($server_id,"day",$user_id) != null
            && get_server_meta($server_id,"day",$user_id) != false
        ){
            $day = get_server_meta($server_id,"day",$user_id) ;
        }
        update_server_meta( $server_id, 'day_of_kill',$day, $user_id );
    }
}

/**
 * @param int $server_id
 * @return string
 */
function get_emoji_for_friendly( int $server_id ) : string
{
    global $emoji_number;
    if ( ! isset( $emoji_number[ $server_id ] ) )
    {
        $emoji_number[ $server_id ] = count( $emoji_number ) + 1;
    }
    switch ( $emoji_number[ $server_id ] )
    {
        case 1:
            return '🔸';
        case 2:
            return '🔹';
        case 3:
            return '🔻';
        case 4:
            return '◽️';
        case 5:
            return '◾️';
        default:
            return '';
    }
}

/**
 * @param int $server_id
 * @return bool|mixed
 * @throws Exception
 */
function add_emoji_for_friendly( int $server_id )
{
    $emoji_number = get_server_meta( $server_id, 'emoji-number' );
    return update_server_meta( $server_id, 'emoji-number', $emoji_number + 1 );
}

/**
 * @param int $server_id
 * @return int
 */
function get_count_member_on_server( int $server_id ) : int
{
    global $link;
    return (int) $link->get_var( "SELECT COUNT(*) FROM `user_game` WHERE `server_id` = {$server_id}" );
}

/**
 * @param int $chat_id
 * @param string $group_id
 * @param string $role_id
 * @param int $server_id
 * @param bool $sender
 * @return void
 * @throws \Throwable
 */
function add_player_to_server( int $chat_id, string $group_id = 'random', string $role_id = 'random', int $server_id = 0, bool $sender = true )
{
    global $link;
    global $telegram;
    $number_emojis = [
        1 => '۱',
        2 => '۲',
        3 => '۳',
        4 => '۴',
        5 => '۵',
        6 => '۶',
        7 => '۷',
        8 => '۸',
        9 => '۹',
        10 => '۱۰',
        11 => '۱۱',
        12 => '۱۲',
        13 => '۱۳',
        14 => '۱۴',
        15 => '۱۵',
        16 => '۱۶',
        17 => '۱۷',
        18 => '۱۸',
        19 => '۱۹',
        20 => '۲۰',
    ];

    ( new \library\User( $chat_id, 0 ) )->checkBaned()->checkLeagueWithPoint( $server_id )->checkTimeServerLeague( $server_id )->changeStatusLastGame();


    // usleep(rand(1, 3000) * 1000);

    try
    {

        $user_league = get_league_user( $chat_id );
        $league_id = $user_league->id; // Edit Sam
        if ( $server_id == 0 )
        {

            add_player_to_server( $chat_id, $group_id, $role_id, get_server_by_league( $user_league->id ), $sender );
            return;

        }

        if ( $role_id > 0  && $role_id != 'random')
        {

            if ( ! add_role_to_server( $server_id, $role_id, $chat_id ) )
            {

                $server = get_server( $server_id );

                $old_server = 0;
                do
                {

                    $new_server = get_server( get_server_by_league( $server->league_id ) );

                    if ( $old_server == $new_server->id )
                    {

                        $link->insert( 'server', [
                            'league_id'  => $server->league_id,
                            'count'      => 0,
                            'status'     => 'opened',
                            'user_id'     => $user_id,
                            'cron'       => get_cron_number(),
                            'bot'        => $_GET[ 'bot' ] ?? 0,
                            'created_at' => date( 'Y-m-d H:i:s' )
                        ] );

                        $new_server = get_server( $link->getInsertId() );

                    }
                    else
                    {

                        $old_server = $new_server->id;

                    }

                } while ( ! add_role_to_server( $new_server->id, $role_id, $chat_id ) );

                $server_id = $new_server->id;

            }

        }

        // دریافت سرور
        if ( is_numeric( $server_id ) && $server_id > 0 )
        {

            // $user_add_to_server = add_user_to_server( $server_id, $chat_id );
            // اضافه کردن کاربر به سرور یافت شده
            if ( add_user_to_server( $server_id, $chat_id ) )
            {

                $server         = is_user_in_which_server( $chat_id );
                $server_id      = $server->id;
                $server_league  = get_league( $server->league_id );

                if ( $sender )
                {

                    // نمایش اعضا داخل سرور
                    $user         = new \library\User( $chat_id );
                    $user__league = $user->get_league();

                    $message = '🎲 درحال جستجوی بازیکن برای شروع ...' . "\n";
                    $message .= '🔸 نوع بازی :  ' . $server_league->icon . ' ، ' . tr_num( $server_league->count, 'fa' ) . ' نفره' . "\n \n";
                    $message .= '👥 لیست افراد در صف انتظار' . "\n";

                    /*$message      = '📍 شما به بازی نوع ' . "<u>" . $server_league->icon . '،' . $server_league->count . ' نفره' . "</u>" . ' پیوستید .' . "\n";
                    $message      .= '🔰 در حال جستجوی بازیکن آنلاین ... لطفا منتظر بمانید .' . "\n \n";
                    $message      .= 'اعضای بازی : ' . "\n";*/

                    $users_server = get_users_by_server( $server_id );
                    $keyboard = [];
                    $i = 0;
                    $i2 = 0;
                    $id=1;

                    foreach ( $users_server as $item )
                    {

                        $user_game = new \library\User( $item->user_id );
                        $prefix    = '';

                        if ( is_server_meta( $server_id, 'friend', $item->user_id ) )
                        {

                            $prefix = get_emoji_for_friendly( get_server_meta( $server_id, 'friend', $item->user_id ) );

                        }

//                        $message .= $i . '- ' . $prefix . ' ' . $user_game->get_league()->emoji . ' ' . $item->name . "\n";
                        $keyboard[$i][$i2] = $telegram->buildInlineKeyboardButton($number_emojis[$id] . '-'. $prefix . ' ' . $user_game->get_league()->emoji . ' ' . $item->name , '', '/');
                        $id=$id+1;
                        $i2++;
                        if ($i2 % 2 === 0) {
                            $i++;
                            $i2=0;
                        }

                    }


                    while ($id<=$server_league->count){
//                        $message .= '-'.$i;
                        $keyboard[$i][$i2] = $telegram->buildInlineKeyboardButton(''.$number_emojis[$id], '', '/');
                        $id=$id+1;
                        $i2++;
                        if ($i2 % 2 === 0) {
                            $i++;
                            $i2=0;
                        }
                    }
                    $keyboard2=$keyboard;
                    foreach ($keyboard as $key => $values) {
                        if (count($keyboard)%2===0 || (count($keyboard)%2!==0) && $key!==(count($keyboard)-1)){
                            $temp = $keyboard[$key][0];
                            $keyboard[$key][0] = $keyboard[$key][1];
                            $keyboard[$key][1] = $temp;
                        }

                    }

                    SendMessage( $chat_id,'🎲 درحال شروع بازی ...' , KEY_GAME_ON_MENU, null, 'html' );
//                    SendMessage( $chat_id, $message, KEY_GAME_ON_MENU, null, 'html' );
                    //قسمتی که باید این پیام گرفته بشود
                    $mess=$message;
                    $response =SendMessage( $chat_id,$message, $telegram->buildInlineKeyBoard( $keyboard ),  null, 'html' );
                    $mess_id = $response->message_id;
//                    $updateSql = "UPDATE `user_game`
//                  SET `first_chat_id` = ".$mess_id."
//                  WHERE `user_id` = '$chat_id' AND WHERE `server_id` = '$server_id'";
//                    $link->query($updateSql);
                    $link->where('user_id', $chat_id)->where('server_id', $server_id)->update("user_game", [
                        'first_chat_id' => $mess_id
                    ]);

//                    SendMessage( $chat_id, $message.$mess_id, KEY_GAME_ON_MENU, null, 'html' );
//                    $mess_id = $response->message_id;

                    if ( get_count_member_on_server( $server->id ) <= $server_league->count )
                    {
//


//                        if ( is_server_meta( $server_id, 'friend', $item->user_id ) )
//                        {
//
//                            $prefix = get_emoji_for_friendly( get_server_meta( $server_id, 'friend', $user->getUserId() ) );
//
//                        }
                        $counter=0;
                        foreach ($keyboard2 as $key2=>$val2){
                            foreach ($val2 as $key3=>$val3){
                                if ($counter==(count( $users_server )-1)){
                                    $keyboard2[$key2][$key3]=$telegram->buildInlineKeyboardButton($number_emojis[$counter+1] . '-' . $user__league->emoji . ' ' . $user->user()->name , '', '/');
                                }
                                $counter++;
                            }
                        }

                        $keyboard=$keyboard2;
                        foreach ($keyboard as $key => $values) {
                            if (count($keyboard)%2===0 || (count($keyboard)%2!==0) && $key!==(count($keyboard)-1)){
                                $temp = $keyboard[$key][0];
                                $keyboard[$key][0] = $keyboard[$key][1];
                                $keyboard[$key][1] = $temp;
                            }

                        }

                        $message = "<b>" . ".</b>" . "<u><b>" . $user__league->emoji . $user->user()->name . "</b></u>" . 'به این بازی پیوست.';
                        foreach ( $users_server as $item )
                        {
//                            $message=$message.'-'.$item->first_chat_id."\n";
                            if ( $chat_id != $item->user_id )
                            {
//                                $newKeyboard = [
//                                    [
//                                        ['text' => 'دکمه 1', 'callback_data' => 'button1'],
//                                        ['text' => 'دکمه 2', 'callback_data' => 'button2']
//                                    ],
//                                    [
//                                        ['text' => 'تست 🔙', 'callback_data' => 'back']
//                                    ]
//                                ];

//                                EditKeyboard($item->user_id, $message_id, $telegram->buildInlineKeyBoard( $keyboard ), null, 'html');
//                                EditMessageText( $item->user_id,$_SESSION[md5(md5($server_id.'_'.$item->user_id))],'fsdfsdfsdfs', null, null, 'html' );
//                                EditMessageText( $item->user_id,$item->first_chat_id,$mess , $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );
                                EditMessageText( $item->user_id,get_game($item->user_id)->first_chat_id,$mess , $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );
                                if ( is_server_meta( $server_id, 'friend', $item->user_id ) ) {
                                    SendMessage($item->user_id, $message, null, null, 'html');
                                }


                            }

                        }

                    }
                    else
                    {

                        $last_user = end( $users_server );
                        leave_server( $last_user->user_id );
                        return;

                    }

                }

                // منتظر ماندن برای حد نساب رسیدن افراد داخل سرور
                update_status( 'get_users_server', $chat_id );

                check_server_members( $server );

            }
            else
            {

                $message = '⚙️ متاسفانه در اضافه کردن شما به سرور مشکلی رخ داد یا شما قبلا به یک سرور پیوسته اید!' . "\n \n";
                Message();

            }

        }
        else
        {

            $message = '⚙️ متاسفانه سروری یافت نشد.' . "\n \n";
            $message .= '💢 لطفا با پشتیبانی گزارش دهید!';
            Message();

        }

    }
    catch ( Exception | Throwable $e )
    {

        $message = "<b>🔴 WARNING ERROR ON FILE SERVER WHEN ADD PLAYER TO SERVER 🔴</b>" . "\n";
        $message .= "<b>👉 Error File : { " . $e->getFile() . ':' . "<code>" . $e->getLine() . "</code>" . " }</b>" . "\n";
        $message .= "<b>👾 Error Content:</b>" . "\n \n";
        $message .= "<b><code>" . $e->getMessage() . "</code></b>";
        SendMessage( ADMIN_LOG, $message, null, null, 'html' );

    }

}

/**
 * @param \helper\Server $server
 * @throws \Exception|\Throwable
 */
function check_server_members( $server )
{
    global $telegram;
    /** @var \helper\Server $server */
    if ( empty( $server->id ) || $server->id <= 0 ) return;
    $member_count  = get_count_member_on_server( $server->id );
    $server_league = get_league( $server->league_id );
    $server        = get_server( $server->id );

    if ( isset( $server_league->count ) && $member_count == $server_league->count && $server->status == 'opened' )
    {

        $roles_users  = set_role_user_by_server( $server->id, $server->league_id );
        $users_server = get_users_by_server( $server->id );

        // foreach(array_keys($roles_users) as $roles_user){
        //     if(!in_array($roles_user, array_keys($users_server))){
        //         unset($roles_users[$roles_user]);
        //     }
        // }
        while ( count( $roles_users ) != count( $users_server ) ){
            $roles_users = set_role_user_by_server( $server->id, $server->league_id );
            $users_server = get_users_by_server( $server->id );

        }

        update_server_meta( $server->id, 'day', 1 ); // روز چندم
        update_server_meta( $server->id, 'is', 'on' );
        // شروع بازی
        update_server( $server->id, [ 'status' => 'started'/*, 'count' => $member_count*/ ] );

        // دریافت نقش ها
        $admin_message = 'بازی جدید تکمیل شد. ' . "<code>" . $server->id . "</code> " . "<b>بازی " . $server_league->icon . "</b>" . "\n";
        $admin_message .= 'زمان شروع : ' . jdate( 'Y-m-d H:i' ) . "\n \n";

        $id    = 1;
        $is_me = true;

        try
        {
            // $_server = new Server($server->id);
            // $users_server = $_server->users();
            foreach ( $roles_users as $user_id => $role_id )
            {
                if ( $user_id == ADMIN_LOG ) $is_me = false;

                $role   = get_role( (int) $role_id );
                $prefix = '';

                if ( is_server_meta( $server->id, 'friend', $user_id ) )
                {

                    $prefix = get_emoji_for_friendly( get_server_meta( $server->id, 'friend', $user_id ) );

                }

//                SendMessage("6645079982" , "id : $user_id, serverid: $server->id, role :$role->id" . "role users: " . var_export($roles_users,true) . "user servers: ." . var_export($users_server,true) . "role : " . var_export($role,true));
                error_log("id : $user_id, serverid: $server->id, role :$role->id" . "role users: " . var_export($roles_users,true) . "user servers: ." . var_export($users_server,true) . "role : " . var_export($role,true));
                $admin_message .= "<b>" . $id ++ . ".</b> " . $prefix . user( $user_id )->name . ( emoji_group( $role->group_id ) ) . " " . $role->icon . '  <code>' . $user_id . '</code>' . "\n";
                if ( add_role_to_user_for_server( $user_id, $server->id, $role->id ) )
                {


                    // $role = get_role_user_server($server->id, $user_id);
                    $message = '🔔 بازی شروع شد.' . "\n";
                    $message .= '🌞 #روز اول :' . "\n";
                    $message .= ' ۲۵ ثانیه وقت داری به بقیه سلام کنی و با اعضای بازی آشنا بشی ' . "\n \n";
                    $message .= '♨️ نقش شما : ' . $role->icon . "\n";
                    $message .= '🔘 گروه : ' . group_name( $role->group_id ) . "\n";
                    $message .= '🔖 توضیحات نقش : ' . "\n" . $role->detail . "\n \n";
                    $message .= '📚 راهنمای بازی : ' . "\n" . '/help' . "\n" . '💬 چت : فعال ' . "\n";
                    $message .= 'مدت زمان : ⏱ ۲۵ ثانیه';


                    if ( ! is_server_meta( $server->id, 'message-sended', $user_id ) )
                    {

                        switch ( $role_id )
                        {
							case ROLE_Shahzadeh:
								if(!empty(get_server_meta( $server->id, 'isoldkilled', $user_id )))
								{
									add_server_meta( $server->id, 'isoldkilled', '0', $user_id );
								}
								
								
								if(!empty(get_server_meta( $server->id, 'power-shahzadeh', $user_id )))
								{
									add_server_meta( $server->id, 'power-shahzadeh', '0', $user_id );
								}
								
								if(!empty(get_server_meta( $server->id, 'select', $user_id )))
								{
									add_server_meta( $server->id, 'select', '0', $user_id );
								}
								
								$keyboard = [];
								$result = SendMessage(
                                    $user_id,
                                    $message,
                                    $telegram->buildInlineKeyBoard( $keyboard ),
                                    null,
                                    'html'
                                );
							break;

                            case ROLE_Bazpors:

                                $keyboard = [];
                                foreach ( $users_server as $user )
                                {

                                    if ( $user->user_id != $user_id )
                                    {

                                        $keyboard[] = [
                                            $telegram->buildInlineKeyboardButton( '🔗 ' . user( $user->user_id )->name, '', '1/server-' . $server->league_id . '-question-' . $server->id . '-' . $user->user_id )
                                        ];

                                    }

                                }

                                $result = SendMessage( $user_id, $message, $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );

                                if ( empty( $result->message_id ) )
                                {

                                    $random_role = get_random_role( $server->id, ' `role`.`id` != ' . ROLE_Bazpors . ' AND ' );
                                    add_server_meta( $server->id, 'select', ( get_role_by_user( $server->id, ( $random_role->id ?? 0 ) ) ?? 0 ), $role_id );

                                }

                                break;

                            case ROLE_Gambeler:

                                $keyboard = [];
                                foreach ( $users_server as $user )
                                {

                                    if ( $user->user_id != $user_id )
                                    {

                                        $keyboard[] = [
                                            $telegram->buildInlineKeyboardButton( '🎮 ' . user( $user->user_id )->name, '', '1/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->id . '-' . $user->user_id )
                                        ];

                                    }

                                }

                                $result = SendMessage( $user_id, $message, $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );

                                if ( empty( $result->message_id ) )
                                {

                                    $random_role = get_random_role( $server->id, ' `role`.`id` != ' . ROLE_Gambeler . ' AND ' );
                                    add_server_meta( $server->id, 'select', ( get_role_by_user( $server->id, ( $random_role->id ?? 0 ) ) ?? 0 ), $role_id );

                                }

                                break;
                            case ROLE_MineLayer:

                                $keyboard = [];
                                foreach ( $users_server as $user )
                                {

                                    if ( $user->user_id != $user_id )
                                    {

//                                        $keyboard[] = [
//                                            $telegram->buildInlineKeyboardButton(
//                                                '💣 ' . user( $user->user_id )->name,
//                                                '',
//                                                '1/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->id . '-' . $user->user_id
//                                            )
//                                        ];

                                    }

                                }

                                $result = SendMessage(
                                    $user_id,
                                    $message,
                                    $telegram->buildInlineKeyBoard( $keyboard ),
                                    null,
                                    'html'
                                );

                                if ( empty( $result->message_id ) )
                                {

                                    $random_role = get_random_role( $server->id, ' `role`.`id` != ' . ROLE_MineLayer . ' AND ' );
                                    add_server_meta(
                                        $server->id,
                                        'select',
                                        ( get_role_by_user( $server->id, ( $random_role->id ?? 0 ) ) ?? 0 ),
                                        $role_id
                                    );

                                }

                                break;
                            case ROLE_MineLayerMafia:

                                $keyboard = [];
                                foreach ( $users_server as $user )
                                {

                                    $role = get_role_user_server($server->id, $user->user_id);
                                    error_log("server");
                                    error_log(var_export($role,true));
                                    //&& $role['group_id'] != 2
                                    if ( $user->user_id != $user_id && ($role->group_id != 2  ))
                                    {

//                                        $keyboard[] = [
//                                            $telegram->buildInlineKeyboardButton(
//                                                '💣 ' . user( $user->user_id )->name,
//                                                '',
//                                                '1/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->id . '-' . $user->user_id
//                                            )
//                                        ];

                                    }

                                }

                                $result = SendMessage(
                                    $user_id,
                                    $message,
                                    $telegram->buildInlineKeyBoard( $keyboard ),
                                    null,
                                    'html'
                                );

                                if ( empty( $result->message_id ) )
                                {

                                    $random_role = get_random_role( $server->id, ' `role`.`id` != ' . ROLE_MineLayerMafia . ' AND ' );
                                    add_server_meta(
                                        $server->id,
                                        'select',
                                        ( get_role_by_user( $server->id, ( $random_role->id ?? 0 ) ) ?? 0 ),
                                        $role_id
                                    );

                                }

                                break;


                            default:

                                $result = SendMessage( $user_id, $message, KEY_GAME_ON_MENU, null, 'html' );

                                break;
                        }

                        if ( isset( $result->message_id ) )
                        {

                            add_server_meta( $server->id, 'message-sended', 'sended', $user_id );

                        }


                        update_status( 'game_started', $user_id );

                        update_user_meta( $user_id, 'game-count', (int) get_user_meta( $user_id, 'game-count' ) + 1 );
                        add_server_meta( $server->id, 'is-online', 'no', $user_id );
                        add_server_meta( $server->id, 'status', 'live', $user_id );
                        // update_user_meta( $user_id, 'count-game', '0' );
                        add_name( $user_id, $server->id );


                    }

                }

            }


            // if ( $is_me )
            do_action( 'report_start_game', $admin_message, $server );


            // داده های سرور
            update_server_meta( $server->id, 'time', time() ); // تاریخ شروع شدن بازی
            update_server_meta( $server->id, 'next-time', time() + 25 ); // تاریخ باز شدن
            update_server_meta( $server->id, 'status', 'night' ); // وضعیت روز
            delete_server_meta( $server->id, 'is' );

            if ( $server->user_id == ADMIN_ID )
            {
                update_server( $server->id, [
                    'type' => 'public'
                ] );
            }

        }
        catch ( Exception | Throwable $exception )
        {
            error_log($exception);
            global $telegram;
            $telegram->sendMessage( [
                'chat_id'    => '202910544',
                'text'       => json_encode([
                    $exception
                ]),
                'parse_mode' => 'html'
            ] );
            $message = '⛔️ باعرض پوزش، این سرور دچار نقص فنی شد.';
            foreach ( $users_server as $item )
            {
                SendMessage( $item->user_id, $message );
            }


            ( new Server( $server->id ) )->close() && tun_off_server( $server->id );

            SendMessage( ADMIN_LOG, 'role ' . json_encode( $role ), null, null, 'html' );
            SendMessage( ADMIN_LOG, 'server ' . json_encode( $server ), null, null, 'html' );
            SendMessage( ADMIN_LOG, 'role_id ' . json_encode( $role_id ), null, null, 'html' );

            throw new Exception( $exception->getMessage() );

        }

    }
    elseif ( $member_count > $server_league->count )
    {

        $users_server = get_users_by_server( $server->id );
        tun_off_server( $server->id );
        $message = '🚫 خطایی رخ داد! سرور بسته شد.';
        foreach ( $users_server as $item )
        {

            SendMessage( $item->user_id, $message );

        }

    }

}

/**
 * @param int $server_id
 * @return bool
 * @throws Exception
 */
function tun_off_server( int $server_id ) : bool
{
    global $link;
    $link->where( 'id', $server_id )->update( "server", [
        'status' => 'closed',
        'count'  => 0
    ] );
    $users = $link->get_result( "SELECT * FROM `user_game` WHERE `server_id` = {$server_id}" );
    foreach ( $users as $user )
    {
        $link->where( 'user_id', $user->user_id )->update( "users", [
            'status' => null
        ] );
        $link->where( 'user_id', $user->user_id )->delete( "user_game" );
    }
    return true;
}

/**
 * @param int $bot_id
 * @return int
 */
function get_count_members_bots( int $bot_id ) : int
{
    global $link;
    return (int) $link->get_var( "SELECT COUNT(*) FROM `user_game` WHERE `bot` = {$bot_id}" );
}

/**
 * @param int $count_members
 * @return string
 */
function get_status_servers_bots( int $count_members ) : string
{
    if ( $count_members <= 15 )
    {
        return '🟢';
    }
    elseif ( $count_members <= 30 )
    {
        return '🟡';
    }
    elseif ( $count_members <= 38 )
    {
        return '🟠';
    }
    else
    {
        return '🔴';
    }
}