<?php


/**
 * @param int $user_id
 * @return string
 */
function hash_user_id(int $user_id = 0): string
{
    global $chat_id, $chatid;
    if ($user_id == 0) {
        $user_id = !empty($chat_id) ? $chat_id : $chatid;
    }
    return string_encode($user_id);
}