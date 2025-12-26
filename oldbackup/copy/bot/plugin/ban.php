<?php

/**
 * @param int $user_id
 * @param int $start_time
 * @param int $end_time
 * @param int $report_id
 * @return int
 * @throws Exception
 */
function add_ban(int $user_id, int $start_time, int $end_time, int $report_id): int
{
    global $link;
    try {
        $link->insert('bans', [
            'user_id' => $user_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'report_id' => $report_id,
        ]);
        return $link->getInsertId();
    } catch (Exception $e) {
        throw new Exception('Error To Add Ban In Database');
    }
}

/**
 * @param int $user_id
 * @return int
 * @throws Exception
 */
function unban(int $user_id): int
{
    global $link;
    try {
        $link->where('user_id', $user_id)->update('bans', [
            'status' => 2
        ]);
        return $link->getInsertId();
    } catch (Exception $e) {
        throw new Exception('Error To Update Ban In Database');
    }
}

/**
 * @param int $user_id
 * @return false|object|null
 */
function get_ban(int $user_id)
{
    global $link;
    return $link->get_row("SELECT * FROM `bans` WHERE `user_id` = {$user_id} ORDER BY `id` DESC LIMIT 1");
}

/**
 * @param $end_timestamp
 * @return string|null
 * @throws Exception
 */
function time_to_string($end_timestamp)
{
    $now = new DateTime();
    $future_date = new DateTime(date('Y-m-d H:i:s', $end_timestamp));

    $interval = $future_date->diff($now);

    if (!empty($interval->format("%m"))) {
        return $interval->format("%m ماه");
    } elseif (!empty($interval->format("%d"))) {
        return $interval->format("%d روز");
    } elseif (!empty($interval->format("%h"))) {
        return $interval->format("%h ساعت");
    } elseif (!empty($interval->format("%i"))) {
        return $interval->format("%i دقیقه");
    } elseif (!empty($interval->format("%s"))) {
        return "1 دقیقه";
    }
    return null;
}


add_action('check_ban', function () {

    global $chat_id, $chatid, $link;

    $chat_id = $chat_id ?? $chatid;

    $status = $link->get_row("SELECT * FROM `bans` WHERE `user_id` = {$chat_id} AND `status` = 1 ORDER BY `id` DESC LIMIT 1");

    if (isset($status->end_time)) {


        if ($status->end_time >= time() && apply_filters('filter_user_baned', true)) {

            $date = time_to_string($status->end_time);
            $message = '📛 اکانت شما تا ' . '<u>' . $date . '</u>' . ' دیگر مسدود است .';
            SendMessage($chat_id, $message, null, null, 'html');
            exit();

        }

    }

});

/**
 * @param int $user_id
 * @return bool
 * @throws Exception
 */
function check_ban(int $user_id): bool
{
    global $link;
    $status = $link->get_row("SELECT * FROM `bans` WHERE `user_id` = {$user_id} AND `status` = 1 ORDER BY `id` DESC LIMIT 1");
    if (isset($status->end_time)) {

        if ($status->end_time >= time() && apply_filters('filter_user_baned', true)) {

            return false;

        }

    }

    return true;
}