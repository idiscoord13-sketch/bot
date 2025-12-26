<?php

/**
 * @param int $server_id
 * @return bool
 */
function is_note_by_server( int $server_id ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT * FROM `report` WHERE `server_id` = {$server_id} AND `note` IS NOT NULL");
}

/**
 * @param int $server_id
 * @param int $user_id
 * @return bool
 */
function is_note_for_user_by_server( int $server_id, int $user_id ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT * FROM `report` WHERE `server_id` = {$server_id} AND `user_reported` = {$user_id} AND `note` IS NOT NULL");
}

/**
 * @param int $server_id
 * @return array|false
 */
function get_notes_by_server( int $server_id )
{
    global $link;
    return $link->get_result("SELECT * FROM `report` WHERE `server_id` = {$server_id} AND `note` IS NOT NULL");
}

/**
 * @param int $server_id
 * @param int $user_id
 * @return array|false
 */
function get_notes_for_user_by_server( int $server_id, int $user_id )
{
    global $link;
    return $link->get_result("SELECT * FROM `report` WHERE `server_id` = {$server_id} AND `user_reported` = {$user_id} AND `note` IS NOT NULL");
}