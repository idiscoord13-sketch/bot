<?php


/**
 * @param int $user_id
 * @param int $coin
 * @return bool
 */
function add_coin(int $user_id, int $coin = 0)
{
    return update_user([
        'coin' => user($user_id)->coin + $coin
    ], $user_id);
}

/**
 * @param int $user_id
 * @param int $coin
 * @return bool
 */
function demote_coin(int $user_id, int $coin = 0)
{
    if (has_coin($user_id, $coin)) {
        return update_user([
            'coin' => user($user_id)->coin - $coin
        ], $user_id);
    }
    return false;
}

/**
 * @param int $user_id
 * @param int $coin
 * @return bool
 */
function has_coin(int $user_id, $coin = 0)
{
    if (user($user_id)->coin >= $coin) return true;
    return false;
}