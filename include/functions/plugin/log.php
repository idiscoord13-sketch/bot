<?php

/**
 * @param string $type
 * @param string $value
 * @param int|null $user_id
 * @return int
 * @throws Exception
 */
function add_log(string $type, string $value, int $user_id = null)
{
    global $link;
    $link->insert('log', [
        'type' => $type,
        'value' => $value,
        'user_id' => $user_id
    ]);
    return $link->getInsertId();
}

/**
 * @param int $user_id
 * @param string $type
 * @param bool $is_arr
 * @return array|false|mixed
 */
function get_log(int $user_id, string $type, bool $is_arr = false)
{
    global $link;
    if (is_null($user_id)) {
        $where = 'IS NULL';
    } else {
        $where = ' = ' . $user_id;
    }
    if ($is_arr) {
        $result = $link->get_result("SELECT `value` FROM `log` WHERE `type` = '{$type}' AND `user_id` {$where}");
    } else {
        $result = $link->get_var("SELECT `value` FROM `log` WHERE `type` = '{$type}' AND `user_id` {$where} ORDER BY `id` DESC");
    }
    return $result;
}

/**
 * @param int $user_id
 * @param string $type
 * @return bool
 * @throws Exception
 */
function delete_log(int $user_id, string $type)
{
    global $link;
    try {
        $link->where('user_id', $user_id)->where('type', $type)->delete('log', 1);
        return true;
    } catch (Exception $e) {
        throw new Exception("ERROR ON DELETE LOG USER : " . $user_id);
    }
}