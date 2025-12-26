<?php


defined('BASE_DIR') || die('NO ACCESS');

function is_admin()
{
    global $chat_id;
    $admin = file_get_contents(BASE_DIR . '/admins.txt');
    $admins = explode(',', $admin);
    if (isset($chat_id) && in_array($chat_id, $admins)) {
        return true;
    }
    return false;
}