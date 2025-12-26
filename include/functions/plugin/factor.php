<?php

/**
 * @param string $auth
 * @param int $id
 * @return bool
 */
function is_factor( string $auth, int $id = 0 ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT * FROM `orders` WHERE `auth` = '{$auth}' OR `id` = {$id}");
}

/**
 * @param int $user_id
 * @param int $amount
 * @param string $auth
 * @param int $coin
 * @return bool
 * @throws \Exception
 */
function add_factor( int $user_id, int $amount, string $auth, int $coin = 0 ) : bool
{
    global $link;
    $link->insert('orders', [
        'user_id' => $user_id,
        'amount'  => $amount,
        'auth'    => $auth,
        'data'    => $coin
    ]);
    return true;
}

/**
 * @param string $auth
 * @param int $ref_id
 * @return bool
 * @throws Exception
 */
function update_factor( string $auth, int $ref_id ) : bool
{
    global $link;
    $link->where('auth', $auth)->update('orders', [
        'ref_id' => $ref_id,
    ]);
    return true;
}

/**
 * @param string $auth
 * @param int $id
 * @return false|object|null
 */
function get_factor( string $auth, int $id = 0 )
{
    global $link;
    return $link->get_row("SELECT * FROM `orders` WHERE `auth` = '{$auth}' OR `id` = {$id}");
}