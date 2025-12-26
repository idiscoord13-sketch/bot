<?php


function wallet($user_id, $type = 'add', $amount = 0, $detail = ''): string
{
    global $link;
    if (!in_array($type, ['add', 'delete'])) {
        return 'نوع عملیات اشتباه است.';
    }
    if ($amount <= 0) {
        return 'مبلغ نمیتواند کمتر 0 باشد.';
    }

    $wallet = $link->get_row("SELECT * FROM `wallet` WHERE `user_id` = {$user_id} ORDER BY `id` DESC");
    $balance = $wallet->balance ?? 0;
    $bal = $balance;
    if ($type == 'add')
        $bal += $amount;
    elseif ($amount < $balance)
        $bal -= $amount;
    else
        return 'شما موجودی کافی برای برداشت ندارید.';
    $link->insert('wallet', [
        'user_id' => $user_id,
        'type' => $type,
        'amount' => $amount,
        'balance' => $bal,
        'detail' => $detail
    ]);
    return 'عملیات با موفقیت انجام شد.';
}

function get_wallet($user_id)
{
    global $link;
    $wallet = $link->get_row("SELECT * FROM `wallet` WHERE `user_id` = {$user_id} ORDER BY `id` DESC");
    return $wallet->balance ?? 0;
}