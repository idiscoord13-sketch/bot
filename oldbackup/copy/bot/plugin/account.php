<?php


/**
 * @param int $from_user_id
 * @param int $to_user_id
 * @throws Exception
 */
function move_account(int $from_user_id, int $to_user_id)
{
    global $link;
    $db_name = "Tables_in_" . DB_NAME;

    $fwrite[] =  "from_user_id : {$from_user_id} \n";
    $fwrite[] =  "to_user_id : {$to_user_id} \n";
    $fwrite[] =  "db_name : {$db_name} \n";
   

    $tables  = ['bans', 'challenge', 'chat', 'coupon', 'friends', 'hadith', 'last_game_user', 'log', 'media', 'names', 'orders'
        , 'points' , 'point_daily' , 'point_log' , 'private_chat', 'report', 'request_friends', 'server', 'server_meta',
        'server_role','subscriptions','token',
        'users', 'users_names', 'user_coupon', 'user_game', 'user_league', 'user_meta', 'user_name', 'vip_league', 'votes'
    ];
   

    foreach ($tables as $table) {
        $fwrite[] =  "table : {$table} \n";
        $link->where('user_id', $to_user_id)->delete($table);
        $link->where('user_id', $from_user_id)->update($table, [
            'user_id' => $to_user_id
        ]);
        
    }
    file_put_contents(BASE_DIR . '/move_account.txt', implode("\n", $fwrite));
    
}
function move_account_old(int $from_user_id, int $to_user_id)
{
    global $link;
    $tables  = $link->get_result("SHOW TABLES");
    $db_name = "Tables_in_" . DB_NAME;


    $fwrite[] =  "from_user_id : {$from_user_id} \n";
    $fwrite[] =  "to_user_id : {$to_user_id} \n";
    $fwrite[] =  "db_name : {$db_name} \n";
    $fwrite[] =  "tables : {$tables} \n";
    // $fwrite[] =  "tables : ". implode("|", $tables) ." \n";


    foreach ($tables as $table) {
        $table_name = $table->$db_name;
        // $fwrite[] =  "table : {$table} \n";
        $fwrite[] =  "table_name : {$table_name} \n";
        $columns = $link->get_row("SHOW COLUMNS FROM `" . $table_name . "` LIKE 'user_id'");
        if (!empty($columns) && $table_name != 'users') {
            $link->where('user_id', $to_user_id)->delete($table_name, 1);
            $link->where('user_id', $from_user_id)->update($table_name, [
                'user_id' => $to_user_id
            ]);
        }
    }
    file_put_contents(BASE_DIR . '/move_account.txt', implode("\n", $fwrite));

    $user_file = file_get_contents(BASE_DIR . '/users.txt');
    $users     = explode(',', $user_file);
    if (in_array($from_user_id, $users)) {
        $key = array_search($from_user_id, $users);
        unset($users[$key]);
        file_put_contents(BASE_DIR . '/users.txt', implode(',', $users));
    }
}

/**
 * @param int $user_id
 * @return string
 * @throws Exception
 */
function token_security_user(int $user_id): string
{
    global $link;
    $today     = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-2 day'));
    $token     = $link->get_row("SELECT * FROM `token` WHERE `user_id` = {$user_id} AND `created_at` <= '{$today}' AND `created_at` >= '{$yesterday}'");
    if (empty($token)) {
        $token = md5($user_id);
        $link->insert('token', [
            'user_id'    => $user_id,
            'token'      => $token,
            'created_at' => $today
        ]);
        return $token;
    }
    return $token->token;
}

/**
 * @param string $token
 * @return false|int
 */
function get_token_security_user(string $token)
{
    global $link;
    $today     = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-2 day'));
    return $link->get_var("SELECT `user_id` FROM `token` WHERE `token` = '{$token}' AND `created_at` <= '{$today}' AND `created_at` >= '{$yesterday}'");
}
