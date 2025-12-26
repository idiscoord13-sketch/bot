<?php

/**
 * @param int $user_id
 * @return false|\helper\Users
 */
function get_user( int $user_id = 0 )
{
    global $link, $chat_id, $chatid;
    if ( $user_id === 0 )
    {
        $user_id = $chat_id ?? $chatid;
    }
    return $link->get_row("SELECT * FROM `users` WHERE `user_id` = {$user_id}");
}

/**
 * @return bool
 */
function is_user_register() : bool
{
    global $chat_id, $chatid;
    $chat_id = $chat_id ?? $chatid;
    if ( empty(user()->name) ) return false;
    return true;
}

/**
 * @param int $user_id
 * @return bool
 */
function user_exists( int $user_id ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT `id` FROM `users` WHERE `user_id` = {$user_id}");
}

add_action('registration_users', function () {
    global $chat_id, $chatid, $link, $type;
    $chat_id = $chat_id ?? $chatid;
    if ( isset($chat_id) && !user_exists($chat_id) && $type == 'private' )
    {
        try
        {
            $link->insert('users', [
                'user_id' => $chat_id
            ]);
            /*$link->insert('points', [
                'user_id' => $chat_id,
                'point'   => 0,
            ]);*/
        }
        catch ( Exception $e )
        {
            SendMessage(ADMIN_LOG, 'INSERT USER : ' . $chat_id . ' HAVE ERROR.');
        }
    }
});

add_action('start', function () {
    global $chat_id, $chatid, $telegram;
    $chat_id = $chat_id ?? $chatid;
    if ( is_user_register() )
    {
        $name    = user()->name;
        $message = '💢 ' . $name . ' عزیز ، ' . ' به ربات ایرانی مافیا خوش آمدید 🌷' . "\n \n";
        $message .= '⚙️ جهت شروع بازی بر روی دکمه شروع بازی کلیک نمایید.';
        SendMessage($chat_id, $message, KEY_START_MENU);
    }
    else
    {
        $message = '💢 سلام
 به ربات ایرانی مافیا خوش آمدید 🌷

🔸 در ابتدا لازم است یک نام مستعار برای خود انتخاب کنید .

❓شرایط نام انتخابی :

❕فقط حروف فارسی مجاز است .
❕نام شما کمتر از ۳ کلمه و بیشتر از ۱۵ کلمه نباشد .
❕استفاده از اعراب مجاز است .

🔅 نام خود را ارسال کنید :';
        SendMessage($chat_id, $message, $telegram->buildKeyBoardHide());
        update_status('get_name');
        exit();
    }
});

/*add_action('after_update_status_user', function ($user_id, $last_status, $new_status) {
    global $link;
    if (is_user_meta($user_id, 'last_status') && $new_status == 'reset') {
        $link->where('user_id', $user_id)->update('users', [
            'status' => get_user_meta($user_id, 'last_status')
        ]);
        delete_user_meta($user_id, 'last_status');
    } elseif ($last_status == 'get_send_message') {
        $link->where('user_id', $user_id)->update('users', [
            'status' => $last_status
        ]);
        update_user_meta($user_id, 'last_status', $new_status);
    }
}, 10, 3);*/


/**
 * @param string $key
 * @param int $user_id
 * @return bool
 */
function is_user_meta( int $user_id, string $key ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT * FROM `user_meta` WHERE `user_id` = {$user_id} AND `meta_key` = '{$key}'");
}

/**
 * @param int $user_id
 * @param string $key
 * @param string $value
 * @return bool|mixed
 * @throws Exception
 */
function add_user_meta( int $user_id, string $key, string $value )
{
    global $link;
    if ( !is_user_meta($user_id, $key) )
    {
        $link->insert('user_meta', [
            'user_id'    => $user_id,
            'meta_key'   => $key,
            'meta_value' => $value
        ]);
        return true;
    }
    return update_user_meta($user_id, $key, $value);
}

/**
 * @param int $user_id
 * @param string $key
 * @param string $value
 * @return bool|mixed
 * @throws Exception
 */
function update_user_meta( int $user_id, string $key, string $value )
{
    global $link;
    if ( is_user_meta($user_id, $key) )
    {
        $link->where('user_id', $user_id)->where('meta_key', $key)->update('user_meta', [
            'user_id'    => $user_id,
            'meta_key'   => $key,
            'meta_value' => $value
        ]);
        return true;
    }
    return add_user_meta($user_id, $key, $value);
}

/**
 * @param int $user_id
 * @param string $key
 * @return bool
 * @throws Exception
 */
function delete_user_meta( int $user_id, string $key ) : bool
{
    global $link;
    if ( is_user_meta($user_id, $key) )
    {
        $link->where('user_id', $user_id)->where('meta_key', $key)->delete('user_meta');
        return true;
    }
    return false;
}

/**
 * @param int $user_id
 * @param string $key
 * @return false|object|null
 */
function get_user_meta( int $user_id, string $key )
{
    global $link;
    return $link->get_var("SELECT `meta_value` FROM `user_meta` WHERE `user_id` = {$user_id} AND `meta_key` = '{$key}'");
}

/**
 * @param int $user_id
 * @return array
 * @throws Exception
 */
function reset_user( int $user_id ) : array
{
    global $link;
    $log  = get_log($user_id, 'reset');
    $user = user($user_id);
    if ( empty($log) )
    {
        $point           = get_point($user_id);
        $coin            = $user->coin;
        $user_vip_league = get_vip_league_user($user_id);
        add_log(
            'reset', serialize([
            'user'   => $user,
            'point'  => $point,
            'coin'   => $coin,
            'league' => $user_vip_league
        ]), $user_id
        );

//        $link->where('user_id', $user_id)->delete('points');
        $link->where('user_id', $user_id)->update('users',[
            'point' => 0
        ]);
        add_point(0, $user_id, 0);
        update_user([
            'coin'   => 0,
            'league' => null
        ], $user_id);
        delete_all_vip_league_user($user_id);
        return [
            'status'  => 200,
            'message' => 'کاربر با موفقیت ریست شد.'
        ];
    }
    else
    {

        $data = unserialize($log);

//        $link->where('user_id', $user_id)->delete('points');
        $link->where('user_id', $user_id)->update('users',[
            'point' => 0
        ]);
        /*$link->insert('points', [
            'user_id' => $user_id,
            'point'   => $data['point'],
        ]);*/
        $link->where('user_id', $user_id)->update('users',[
            'point' => $data['point']
        ]);

//        add_point(0, $user_id, $data['point']);
        update_user([
            'coin' => $data['coin']
        ], $user_id);

        if ( isset($data['league']) && count($data['league']) > 0 )
        {
            foreach ( $data['league'] as $datum )
            {
                if ( isset($datum->emoji) )
                {
                    $link->insert('user_league', [
                        'user_id' => $user_id,
                        'emoji'   => $datum->emoji,
                        'name'    => $datum->name ?? ''
                    ]);
                }
            }
        }
        delete_log($user_id, 'reset');
        return [
            'status'  => 200,
            'message' => 'کاربر با موفقیت بازیابی شد.'
        ];
    }
}