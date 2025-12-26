<?php


/**
 * @param string $coupon
 * @return bool
 */
function is_coupon_exists(string $coupon)
{
    global $link;
    return (bool)$link->get_row("SELECT * FROM `coupon` WHERE  `name` = '{$coupon}' AND `status` = 'open' ORDER BY `id` DESC LIMIT 1");
}

/**
 * @param string $coupon
 * @param int $coin
 * @param int $user_id
 * @param int $point
 * @param int $user
 * @param int|null $time
 * @return bool
 * @throws Exception
 */
function add_coupon(string $coupon, int $coin, int $user_id, int $point, int $user = 1, int $time = null)
{
    global $link;
    if (!is_coupon_exists($coupon)) {
        try {
            $link->insert('coupon', [
                'name' => $coupon,
                'coin' => $coin,
                'user_id' => $user_id,
                'user' => $user,
                'rang' => $point,
                'time' => $time,
            ]);
        } catch (Exception $e) {
            throw new Exception("ERROR TO ADD NEW COUPON " . $coupon);
        }
        return true;
    }
    return false;
}

/**
 * @param string $coupon
 * @return bool
 * @throws Exception
 */
function delete_coupon(string $coupon)
{
    global $link;
    if (is_coupon_exists($coupon)) {
        $link->where('name', $coupon)->delete('coupon');
        $link->where('coupon', $coupon)->delete('user_coupon');
        return true;
    }
    return false;
}

/**
 * @param string $coupon
 * @return false|object|null
 */
function get_coupon(string $coupon)
{
    global $link;
    return $link->get_row("SELECT * FROM `coupon` WHERE  `name` = '{$coupon}' AND `status` = 'open' ORDER BY `id` DESC LIMIT 1");
}

/**
 * @param string $coupon
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_coupon(string $coupon, array $data)
{
    global $link;
    if (is_coupon_exists($coupon)) {
        $link->
        where('name', $coupon)->
        where('status', 'open')->
        update('coupon', $data);
        return true;
    }
    return false;
}

/**
 * @param int $user_id
 * @param string $coupon
 * @return bool
 * @throws Exception
 */
function add_used_coupon(int $user_id, string $coupon)
{
    global $link;
    if (is_coupon_exists($coupon)) {
        $link->insert('user_coupon', [
            'coupon' => $coupon,
            'user_id' => $user_id
        ]);
        return true;
    }
    return false;
}

/**
 * @param int $user_id
 * @param string $coupon
 * @return bool
 */
function is_user_used_coupon(int $user_id, string $coupon)
{
    global $link;
    return (bool)$link->get_row("SELECT * FROM `user_coupon` WHERE  `coupon` = '{$coupon}' AND `user_id` = {$user_id} ORDER BY `id` DESC LIMIT 1");
}