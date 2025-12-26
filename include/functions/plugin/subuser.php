<?php

add_action('sub_user', function () {
    global $text, $chat_id, $o_users;
    $data = explode(' ', $text);
    if ($data[0] == '/start' && isset($data[1])) {
        if (!in_array($chat_id, $o_users) && $chat_id != $data[1]) {
            update_user([
                'sub_user' => $data[1]
            ]);
            SendMessage(
                apply_filters('chat_id', $chat_id),
                apply_filters('message_start'),
                apply_filters('keyboard_start'),
                apply_filters('reply_start', null),
                apply_filters('mode_start', 'html')
            );
            exit();
        }
    }
});