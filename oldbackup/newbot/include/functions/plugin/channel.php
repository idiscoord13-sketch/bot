<?php


add_action('join_channel', function () {
    global $chat_id, $chatid, $type, $message;
    $chat_id = $chat_id ?? $chatid;
    if ($type == 'private' && isset($message->from) && ADMIN_LOG != $chat_id) {
        $result = bot('getChatMember', [
            'chat_id' => CHNNEL_ID,
            'user_id' => $chat_id,
        ]);
        if (isset($result->status) && $result->status == 'left' || $result->status == 'ban') {
            SendMessage(
                apply_filters('chat_id', $chat_id),
                apply_filters('message_join_channel'),
                apply_filters('keyboard_join_channel'),
                apply_filters('reply_join_channel', null),
                apply_filters('mode_join_channel', 'html')
            );
            exit();
        }
    }
});