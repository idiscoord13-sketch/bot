<?php


/*add_action('new_point_user', function (int $server_id, int $user_id, int $point) {
    global $link;
    $user_point = get_point($user_id);
    try {
        $link->insert('point_log', [
            'user_id' => $user_id,
            'server_id' => $server_id ?? 0,
            'point' => ($user_point ?? 0) + $point,
            'last_point' => ($user_point ?? 0)
        ]);
    } catch (Exception $e) {
        throw new Exception("ERROR TO INSERT POINT USER " . $user_id);
    }
}, 10, 3);*/


add_action( 'new_point_user', function ( int $server_id, int $user_id, int $point ) {

    global $link;
    try
    {

        $today = date( 'Y-m-d' );
        $user_point = $link->get_row( "SELECT * FROM `point_daily` WHERE `user_id` = {$user_id} AND `created_at` = '{$today}'" );
        if ( isset( $user_point ) )
        {

            $link
                ->where( 'user_id', $user_id )
                ->where( 'created_at', $today )
                ->update( 'point_daily', [
                    'point' => ( $user_point->point ?? 0 ) + $point,
                ] )
            ;

        }
        else
        {

            $link->insert( 'point_daily', [
                'user_id'    => $user_id,
                'point'      => $point,
                'created_at' => $today
            ] );

        }

    }
    catch ( Exception $e )
    {

        throw new Exception( "ERROR TO INSERT POINT DAILY USER " . $user_id );

    }

}, 11, 3 );


add_action( 'new_point_user', function ( int $server_id, int $user_id ) {

    $user = new \library\User( $user_id, $server_id );

    $league_user = $user->user()->league;

    if ( $league_user == 0 && !is_null( $league_user ) )
    {

        if ( $user->get_rank_week() > 10 || $user->get_rank_week() <= 0 )
        {

            $user->update_user( [

                'league' => null

            ] );

        }

    }


}, 12, 2 );

/*add_action('new_point_user', function (int $server_id, int $user_id,int $point) {

    $user = new \library\User($user_id, $server_id);

    $point_user = $user->get_point();
    if ($point_user >= 150 && $point_user - $point <= 150){

        $message = 'تبریک '.$user->user()->name.' عزیز 🥳🎉';
        $message .= 'شما امتیاز لازم برای ورود به بازی سخت را کسب کردید و از این پس میتوانید در کنار کاربران آنلاین در بازی نوع سخت به رقابت بپردازید .';

    }

}, 13, 3);*/


/**
 * @param int $server_id
 * @param int $user_id
 * @param int $point
 * @return bool
 * @throws Exception
 */
function add_point( int $server_id, int $user_id, int $point ) : bool
{
    global $link;
    if ( $point <= 0 ) return false;
    $user_point = get_point( $user_id );
    try
    {
        /*$link
            ->where( 'user_id', $user_id )
            ->update( 'points', [
                'user_id' => $user_id,
                'point'   => ( $user_point ?? 0 ) + $point,
            ] );*/
        $link->where( 'user_id', $user_id )->update( 'users', [
            'point' => $user_point + $point
        ] );
        do_action( 'new_point_user', $server_id, $user_id, $point );
        return true;
    }
    catch ( Exception $e )
    {
        throw new Exception( "ERROR TO INSERT POINT USER " . $user_id );
    }
}

/**
 * @param int $user_id
 * @return false|object|null
 */
function get_point( int $user_id )
{
    global $link;
//    return (int) $link->get_var( "SELECT `point` FROM `points` WHERE `user_id` = {$user_id} ORDER BY `point` DESC LIMIT 1" );
    return (int) $link->get_var( "SELECT `point` FROM `users` WHERE `user_id` = {$user_id} ORDER BY `point` DESC LIMIT 1" );
}

/**
 * @param int $user_id
 * @return false|int|string
 */
function get_rank_user( int $user_id )
{
    global $link;
    $points = (array) $link->get_result( "SELECT * FROM `points` WHERE `point` > 0 GROUP BY `user_id` DESC ORDER BY `point` DESC" );
    return array_search( $user_id, array_column( $points, 'user_id' ) ) + 1;
}

/**
 * @return array|false
 */
function get_points()
{
    global $link;
    return $link->get_result( "SELECT * FROM `points` GROUP BY `user_id` DESC ORDER BY `point` DESC" );
}

/**
 * @param int $point
 * @param int $maxPoint
 * @param string $sort
 * @param int $limit
 * @return array|false
 */
function get_rank_up_user( int $point, int $maxPoint, string $sort = "DESC", int $limit = 5 )
{
    global $link;
    if ( $limit > 0 )
    {
        $limit = "LIMIT " . $limit;
    }
    else
    {
        $limit = '';
    }
//    return $link->get_result( "SELECT * FROM `points` WHERE `point` > {$point} AND `point` <= {$maxPoint} ORDER BY `point` {$sort} {$limit}" );
    return $link->get_result( "SELECT * FROM `users` WHERE `point` > {$point} AND `point` <= {$maxPoint} ORDER BY `point` {$sort} {$limit}" );
}

/**
 * @param int $point
 * @param array $users
 * @param int $limit
 * @param string $sort
 * @return array|false
 */
function get_rank_down_user( int $point = 0, array $users = [], int $limit = 5, string $sort = "DESC" )
{
    global $link;
    $where = '';
    foreach ( $users as $user )
    {
        $where .= 'AND `user_id` != ' . $user . ' ';
    }
    if ( $limit > 0 )
    {
        $limit = "LIMIT " . $limit;
    }
    else
    {
        $limit = '';
    }
//    return $link->get_result( "SELECT * FROM `points` WHERE `point` <= {$point} {$where} ORDER BY `point` {$sort} ,`id` DESC {$limit}" );
    return $link->get_result( "SELECT * FROM `users` WHERE `point` <= {$point} {$where} ORDER BY `point` {$sort} ,`id` DESC {$limit}" );
}

/**
 * @param int $user_id
 * @return false|int
 */
function get_rank_user_in_league( int $user_id )
{
    global $link;
    $league      = get__league_user( $user_id );
    $next_league = get__league( $league->id + 1 );
    if ( isset( $next_league ) )
    {

        $point   = get_point( $user_id );
        $results = $link->get_result( "SELECT * FROM `users` WHERE `point` <= {$next_league->point} AND `point` >= {$point} ORDER BY `point` DESC, `id` ASC" );
//        $results = $link->get_result( "SELECT * FROM `points` WHERE `point` <= {$next_league->point} AND `point` >= {$point} ORDER BY `point` DESC, `id` ASC" );
        return (int) array_search( $user_id, array_column( $results, 'user_id' ) ) + 1;

    }
    return 1;
}

/**
 * @param int $user_id
 * @return false|int|string
 */
function get_rank_user_in_global( int $user_id )
{
    global $link;
    $point = get_point( $user_id );
//    $rank  = $link->get_var( "SELECT COUNT(*) FROM `points` WHERE `point` > {$point} ORDER BY `point` DESC, `id` ASC" );
    $rank = $link->get_var( "SELECT COUNT(*) FROM `users` WHERE `point` > {$point} ORDER BY `point` DESC, `id` ASC" );
    return $rank + 1;
}

/**
 * @param int $limit
 * @return array
 */
function get_top_rank_points( int $limit = 10 ) : array
{
    global $link;
//    $users      = $link->get_result( "SELECT `user_id`,`point` FROM `points` ORDER BY `point` DESC" );
    $users      = $link->get_result( "SELECT `user_id`,`point` FROM `users` ORDER BY `point` DESC" );
    $i          = 0;
    $users_list = [];
    $data       = [];
    foreach ( $users as $user )
    {
        if ( !in_array( $user->user_id, $users_list ) )
        {
            $data[$i]->point   = $user->point;
            $data[$i]->user_id = $user->user_id;
            $data[$i]->user    = user( $user->user_id );
            $i ++;
            $users_list[] = $user->user_id;
        }
        if ( count( $data ) == $limit )
            break;
    }
    return $data;
}

/**
 * @param int $limit
 * @return \library\User[]
 */
function get_top_rank_points_week( int $limit = 10 ) : ?array
{
    global $link;
//    $option_week_day = get_option('week-day');
//    $day_of_week = date('Y-m-d', strtotime('-' . $option_week_day . ' day'));
//    if ($day_of_week == 0) return get_top_rank_points_today();

    $today       = date( 'Y-m-d' );
    $day_of_week = date( 'Y-m-d', strtotime( '-6 day' ) );
    $users       = $link->get_result( "SELECT SUM(`point`) AS `point`,`user_id` FROM `point_daily` WHERE `created_at` <= '{$today}' AND `created_at` >= '{$day_of_week}' GROUP BY `user_id`  ORDER BY `point` DESC" );
    $users_list  = [];
    // SendMessage( 56288741, (string)PHP_INT_MIN, KEY_GAME_ON_MENU, null, 'html' );
    foreach ( $users as $user )
    {

        if ( !in_array( $user->user_id, $users_list ) )
        {

            $data[$i] = new \library\User( $user->user_id );
            $i ++;
            $users_list[] = $user->user_id;

        }
        if ( count( $data ) == $limit )
            break;
    }
    return $data;
}

/**
 * @param int $limit
 * @return null|\library\User[]
 */
function get_top_rank_points_today( int $limit = 10 ) : ?array
{
    global $link;

    $today      = date('Y-m-d');
    $users      = $link->get_result( "SELECT * FROM `point_daily` WHERE `created_at` = '{$today}' ORDER BY `point` DESC" );
    $i          = 0;
    $users_list = [];
    foreach ( $users as $user )
    {
        if ( !in_array( $user->user_id, $users_list ) )
        {
            $data[$i] = new \library\User( $user->user_id );
            $i ++;
            $users_list[] = $user->user_id;
        }
        if ( count( $data ) == $limit )
            break;
    }
    return $data;
}

/**
 * @param int $user_id
 * @param string $date
 * @param string $opration
 * @return false|mixed
 */
function get_point_user_day( int $user_id, string $date, string $opration = '>=' )
{
    global $link;
    return $link->get_var( "SELECT `point` FROM `point_daily` WHERE `user_id` = $user_id AND `created_at` {$opration} '{$date}'" );
}

/**
 * @param int $user_id
 * @return false|int|string
 */
function get_rank_user_today( int $user_id )
{
    global $link;
    $today = date( 'Y-m-d' );
    if ( get_point_user_day( $user_id, $today, '=' ) )
    {
        $users = $link->get_result( "SELECT * FROM `point_daily` WHERE `created_at` = '{$today}' ORDER BY `point` DESC" );
        return array_search( $user_id, array_column( $users, 'user_id' ) ) + 1;
    }
    return false;
}

/**
 * @param int $user_id
 * @return false|mixed
 */
function get_point_user_week( int $user_id )
{
    global $link;
    $today = date( 'Y-m-d' );
//    $option_week_day = get_option('week-day');
//    $day_of_week = date('Y-m-d', strtotime('-' . $option_week_day . ' day'));
    $day_of_week = date( 'Y-m-d', strtotime( '-6 day' ) );
    if ( $day_of_week == 0 ) return get_point_user_day( $user_id, $today, '=' );
    return $link->get_var( "SELECT sum(`point`) FROM `point_daily` WHERE `user_id` = {$user_id} AND `created_at` <= '{$today}' AND `created_at` >= '{$day_of_week}'" );
}

/**
 * @param int $user_id
 * @return int|string
 */
function get_rank_user_week( int $user_id )
{
    global $link;
    $today = date( 'Y-m-d' );
//    $option_week_day = get_option('week-day');
//    $day_of_week = date('Y-m-d', strtotime('-' . $option_week_day . ' day'));
    $day_of_week = date( 'Y-m-d', strtotime( '-6 day' ) );
//    if ($day_of_week == 0) return get_rank_user_today($user_id);
    $users = $link->get_result( "SELECT SUM(`point`) AS `point`,`user_id` FROM `point_daily` WHERE `created_at` <= '{$today}' AND `created_at` >= '{$day_of_week}' GROUP BY `user_id` ORDER BY `point` DESC" );

    if ( $users )
    {

        $result = array_search( $user_id, array_column( $users, 'user_id' ) );
        return $result === false ? 0 : $result + 1;

    }

    return 0;
}