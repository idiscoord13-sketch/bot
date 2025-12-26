<?php


/**
 * @param int $user_id
 * @return string
 */
function hash_user_id(int $user_id = 0): string
{
//    global $chat_id, $chatid;
//    if ($user_id == 0) {
//        $user_id = !empty($chat_id) ? $chat_id : $chatid;
//    }
//    return string_encode($user_id);


    global $chat_id, $chatid;

    // Use the global chat ID if no user ID is provided
    if ($user_id == 0) {
        $user_id = !empty($chat_id) ? $chat_id : $chatid;
    }

    // Get the current time in seconds since Unix epoch
    $current_time = time();

    // Pad the current time to a fixed length (e.g., 10 digits)
    $current_time_str = str_pad($current_time, 10, '0', STR_PAD_LEFT);
    $user_id_str = (string)$user_id;

    // Combine the current time and user ID
    $data = $current_time_str . $user_id_str;

    // Encode the data
    return string_encode($data);
}