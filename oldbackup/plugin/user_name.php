<?php


/**
 * @param int $server_id
 * @param int $user_id
 * @return bool
 */
function name_exists( int $server_id, int $user_id ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT * FROM `user_name` WHERE `server_id` = {$server_id} AND `user_id` = {$user_id} LIMIT 1");
}

/**
 * @param  $server_id
 * @param int $user_id
 * @return int
 */
function get_name_server( $server_id, int $user_id ) : int
{
    global $link;
    return (int) $link->get_var("SELECT `name_id` FROM `user_name` WHERE `server_id` = {$server_id} AND `user_id` = {$user_id} ORDER BY `id` DESC LIMIT 1");
}

/**
 * @param int $server_id
 * @param int $user_id
 * @return false|int
 * @throws \Exception
 */
function add_name_user( int $server_id, int $user_id )
{
    if ( !name_exists($server_id, $user_id) )
    {
        try
        {
            global $link;
            $name_id = (int) $link->get_var("SELECT `name_id` FROM `user_name` WHERE `server_id` = {$server_id} ORDER BY `name_id` DESC LIMIT 1");
            $link->insert('user_name', [
                'server_id' => $server_id,
                'user_id'   => $user_id,
                'name_id'   => $name_id + 1
            ]);
            return $name_id + 1;
        } catch ( Exception $e )
        {
            throw new Exception("ERROR ON ADD NEW NAME USER : " . $user_id);
        }
    }
    return false;
}


/**
 * @param int $server_id
 * @param int $user_id
 * @return bool
 * @throws Exception
 */
function delete_name_user( int $server_id, int $user_id ) : bool
{
    try
    {
        global $link;
        $link->where('server_id', $server_id)->where('user_id', $user_id)->delete('user_name');
//        $link->where('server_id', $server_id)->where('user_id', $user_id)->delete('names');
        return true;
    } catch ( Exception $e )
    {
        throw new Exception("ERROR ON ADD NEW NAME USER : " . $user_id);
    }
    return false;
}


add_filter('filter_user', function( $user ) {
    /* @var $user \helper\Users */
    if ( $user->user_id > 0 && is_user_row_in_game($user->user_id) )
    {

        if ( !empty($user->name) )
        {

            $server = is_user_in_which_server($user->user_id);
            $name = name($user->user_id, $server->id);

            if ( empty($name) )
            {

                $names = [
                    '',
                    'زولا',
                    'استار',
                    'سالید',
                    'کیدو',
                    'هیتر',
                    'کاتر',
                    'هملوک',
                    'رولر',
                    'هیدرا',
                    'ایرونس',
                    'فرایت',
                    'جاولین',
                    'جزبل',
                    'کفکا',
                    'کنو',
                    'کولار',
                    'کارکن',
                    'لانس',
                    'لیلو',
                    'فاری',
                    'فریک',
                    'فندر',
                    'واردن',
                ];

                $name_id = get_name_server($server->id, $user->user_id);

                if ( $name_id > 0 )
                {

                    $user->name = $user->name . ' ' . $names[$name_id];

                }


            }
            else
            {

                $user->name = $name;

            }

        }
        else
        {

            $user->name = 'بینام';

        }

    }

    return $user;

});
