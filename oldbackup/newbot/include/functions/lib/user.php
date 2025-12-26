<?php




function status($user_id = '')
{
    global $link, $chat_id, $chatid;
    if ($user_id === '') {
        $user_id = $chat_id ?? $chatid;
    }
    return $link->get_var("SELECT `status` FROM `users` WHERE `user_id` = {$user_id}");
}

function update_status($status, $user_id = '') : bool
{
    global $link, $chat_id, $chatid;
    if ($user_id === '') {
        $user_id = $chat_id ?? $chatid;
    }
    if (status($user_id) == 'send_note_report' && $status != '') return true;
//    $last_status = status($user_id);
//    do_action('before_update_status_user', $user_id, $last_status, $status);
    $link->where('user_id', $user_id)->update('users', [
        'status' => $status
    ]);
//    do_action('after_update_status_user', $user_id, $last_status, $status);
    return true;
}

function update_data($data, $user_id = '')
{
    global $link, $chat_id, $chatid;
    if ($user_id === '') {
        $user_id = $chat_id ?? $chatid;
    }
    $link->where('user_id', $user_id)->update('users', [
        'data' => $data
    ]);
    return true;
}

function data($user_id = '')
{
    global $link, $chat_id, $chatid;
    if ($user_id === '') {
        $user_id = $chat_id ?? $chatid;
    }
    return $link->get_var("SELECT `data` FROM `users` WHERE `user_id` = {$user_id}");
}

/**
 * @param int $user_id
 * @return false|User
 */
function user(int $user_id = 0)
{
    global $link, $chat_id, $chatid;
    if ($user_id === 0) {
        $user_id = $chat_id ?? $chatid;
    }
    return apply_filters('filter_user', $link->get_row("SELECT * FROM `users` WHERE `user_id` = {$user_id}"));
}

/**
 * @param array $data
 * @param string $user_id
 * @return bool
 * @throws Exception
 */
function update_user(array $data, $user_id = '')
{
    global $link, $chat_id, $chatid;
    if ($user_id === '') {
        $user_id = $chat_id ?? $chatid;
    }
    $link->where('user_id', $user_id)->update('users', $data);
    return true;
}