<?php

/**
 * @param int $server_id
 * @return array|false
 */
function get_votes_by_server(int $server_id)
{
    global $link;
    return $link->get_result("SELECT * FROM `server_meta` INNER JOIN `users` ON `server_meta`.`server_id` = {$server_id} AND `server_meta`.`meta_key` = 'vote' AND `users`.`user_id` = `server_meta`.`user_id` ORDER BY `server_meta`.`id` ASC");
}