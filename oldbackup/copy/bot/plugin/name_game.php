<?php

/**
 * @param int $user_id
 * @return false|object|null
 */
function get_league_user(int $user_id = 0){
    global $chat_id, $chatid, $link;
    if ($user_id == 0){
        $user_id = $chat_id ?? $chatid;
    }
    $point = get_point($user_id);
    return $link->get_row( "SELECT * FROM `name_game` WHERE `name_game`.`point` <= {$point} AND `id` != 5 AND (`start_time` <= " . date( 'H' ) . " OR `start_time` IS NULL) AND (`end_time` >= " . date( 'H' ) . " OR `end_time` iS NULL) ORDER By `point` DESC LIMIT 1 " );
}

/**
 * @param int $id
 * @return false|object|null
 */
function get_league(int $id){
    global $link;
    return $link->get_row("SELECT * FROM `name_game` WHERE `id` = {$id}");
}

/**
 * @return false|object|null
 */
function get_cards( ){
    global $link;
    return $link->get_result("SELECT * FROM `cards` WHERE `is_active` = 1");
}
/**
 * @param int $id
 * @return false|object|null
 */
function get_card(int $id){
    global $link;
    return $link->get_row("SELECT * FROM `cards` WHERE `id` = {$id} AND  `is_active` = 1");
}