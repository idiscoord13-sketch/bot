<?php

/**
 * @param int $user_id
 * @return false|\helper\Users
 */
function get_user( int $user_id = 0 )
{
    global $link, $chat_id, $chatid;
    if ( $user_id === 0 )
    {
        $user_id = $chat_id ?? $chatid;
    }
    return $link->get_row("SELECT * FROM `users` WHERE `user_id` = {$user_id}");
}

/**
 * @return bool
 */
function is_user_register() : bool
{
    global $chat_id, $chatid;
    $chat_id = $chat_id ?? $chatid;
    if ( empty(user()->name) ) return false;
    return true;
}

/**
 * @param int $user_id
 * @return bool
 */
function user_exists( int $user_id ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT `id` FROM `users` WHERE `user_id` = {$user_id}");
}

add_action('registration_users', function () {
    global $chat_id, $chatid, $link, $type;
    $chat_id = $chat_id ?? $chatid;
    if ( isset($chat_id) && !user_exists($chat_id) && $type == 'private' )
    {
        try
        {
            $link->insert('users', [
                'user_id' => $chat_id
            ]);
            /*$link->insert('points', [
                'user_id' => $chat_id,
                'point'   => 0,
            ]);*/
        }
        catch ( Exception $e )
        {
            SendMessage(ADMIN_LOG, 'INSERT USER : ' . $chat_id . ' HAVE ERROR.');
        }
    }
});

add_action('start', function () {
    global $chat_id, $chatid, $telegram;
    $chat_id = $chat_id ?? $chatid;

    function recoverUserIfNeeded($pdo, $pdoBackup, $user_id) {
        // Only proceed if the user_id is "6645079982" (for testing)
//        if ($user_id != "6645079982") {
//            return;
//        }

        try {
            // Set PDO to throw exceptions
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdoBackup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if the user exists in the active database
            $stmtActive = $pdo->prepare("SELECT * FROM `users` WHERE `user_id` = :user_id");
            $stmtActive->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmtActive->execute();

            $user = $stmtActive->fetch(PDO::FETCH_ASSOC);

            // Check if the user is not found or has an empty name field
            if (!$user || empty($user['name'])) {
                // Attempt to recover from backup DB
                $stmtBackup = $pdoBackup->prepare("SELECT * FROM users WHERE user_id = :user_id");
                $stmtBackup->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmtBackup->execute();

                if ($stmtBackup->rowCount() > 0) {
                    // User found in backup DB, recover their data
                    $tables = ['user_meta', 'users', 'users_names', 'point_daily'];

                    foreach ($tables as $table) {
                        // First, delete any existing data for the user in the active DB
                        $deleteStmt = $pdo->prepare("DELETE FROM $table WHERE user_id = :user_id");
                        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                        $deleteStmt->execute();

                        // Now, fetch data from the backup DB
                        $stmtBackupTable = $pdoBackup->prepare("SELECT * FROM $table WHERE user_id = :user_id");
                        $stmtBackupTable->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                        $stmtBackupTable->execute();
                        $data = $stmtBackupTable->fetchAll(PDO::FETCH_ASSOC);

                        error_log(var_export($data, true) . " we are at line 101", 0);

                        // Insert the data into the active DB, excluding the 'id' field
                        foreach ($data as $row) {
                            // Remove the 'id' field from the row data
                            unset($row['id']);

                            $columns = implode(", ", array_keys($row));
                            $placeholders = ":" . implode(", :", array_keys($row));
                            $stmtActiveInsert = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");

                            foreach ($row as $key => $value) {
                                $stmtActiveInsert->bindValue(":$key", $value);
                            }

                            // Log the query being executed
                            error_log("Executing query: INSERT INTO $table ($columns) VALUES (" . implode(", ", array_map(fn($k) => ":$k", array_keys($row))) . ")", 0);

                            // Execute and check for errors
                            if ($stmtActiveInsert->execute()) {
                                error_log("Insert successful at line 111", 0);
                            } else {
                                error_log("Insert failed at line 111", 0);
                            }
                        }
                    }

                    // Optionally, delete the recovered data from the backup DB
                    foreach ($tables as $table) {
                        $deleteBackupStmt = $pdoBackup->prepare("DELETE FROM $table WHERE user_id = :user_id");
                        $deleteBackupStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                        $deleteBackupStmt->execute();
                    }

                    echo "Recovered and reinserted user data for user_id: $user_id.\n";
                } else {
                    echo "No data found for user_id: $user_id in the backup DB.\n";
                }
            }
        } catch (PDOException $e) {
            // Log any exceptions
            error_log("PDOException: " . $e->getMessage(), 0);
        } catch (Exception $e) {
            // Log general exceptions
            error_log("Exception: " . $e->getMessage(), 0);
        }
    }



    try {
//        require_once '../config.php';

//       $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB_NAME, USERNAME, PASSWORD);
//        $pdoBackup = new PDO("mysql:host=" . HOST . ";dbname=iranimaf_deleted_info", USERNAME, PASSWORD);
        // First connection (Primary Database)
        $pdo = new PDO(
            "mysql:host=" . HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            USERNAME,
            PASSWORD
        );

        // Second connection (Backup Database)
        $pdoBackup = new PDO(
            "mysql:host=" . HOST . ";dbname=iranimaf_deleted_info;charset=utf8mb4",
            USERNAME,
            PASSWORD
        );
//// Set the PDO error mode to exception
//        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//        $pdoBackup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        error_log("dude we are here with $chat_id");
        recoverUserIfNeeded($pdo, $pdoBackup, $chat_id);

    } catch (Throwable $e)
    {
        SendMessage("6645079982", $e);
    }
    if ( is_user_register() )
    {
        $name    = user()->name;
        $message = '💢 ' . $name . ' عزیز ، ' . ' به ربات ایرانی مافیا خوش آمدید 🌷' . "\n \n";
        $message .= '⚙️ جهت شروع بازی بر روی دکمه شروع بازی کلیک نمایید.';
        SendMessage($chat_id, $message, KEY_START_MENU);
    }
    else
    {
        $message = '💢 سلام
 به ربات ایرانی مافیا خوش آمدید 🌷

🔸 در ابتدا لازم است یک نام مستعار برای خود انتخاب کنید .

❓شرایط نام انتخابی :

❕فقط حروف فارسی مجاز است .
❕نام شما کمتر از ۳ کلمه و بیشتر از ۱۵ کلمه نباشد .
❕استفاده از اعراب مجاز است .

🔅 نام خود را ارسال کنید :';
        SendMessage($chat_id, $message, $telegram->buildKeyBoardHide());
        update_status('get_name');
        exit();
    }
});

/*add_action('after_update_status_user', function ($user_id, $last_status, $new_status) {
    global $link;
    if (is_user_meta($user_id, 'last_status') && $new_status == 'reset') {
        $link->where('user_id', $user_id)->update('users', [
            'status' => get_user_meta($user_id, 'last_status')
        ]);
        delete_user_meta($user_id, 'last_status');
    } elseif ($last_status == 'get_send_message') {
        $link->where('user_id', $user_id)->update('users', [
            'status' => $last_status
        ]);
        update_user_meta($user_id, 'last_status', $new_status);
    }
}, 10, 3);*/


/**
 * @param string $key
 * @param int $user_id
 * @return bool
 */
function is_user_meta( int $user_id, string $key ) : bool
{
    global $link;
    return (bool) $link->get_row("SELECT * FROM `user_meta` WHERE `user_id` = {$user_id} AND `meta_key` = '{$key}'");
}

/**
 * @param int $user_id
 * @param string $key
 * @param string $value
 * @return bool|mixed
 * @throws Exception
 */
function add_user_meta( int $user_id, string $key, string $value )
{
    global $link;
    if ( !is_user_meta($user_id, $key) )
    {
        $link->insert('user_meta', [
            'user_id'    => $user_id,
            'meta_key'   => $key,
            'meta_value' => $value
        ]);
        return true;
    }
    return update_user_meta($user_id, $key, $value);
}

/**
 * @param int $user_id
 * @param string $key
 * @param string $value
 * @return bool|mixed
 * @throws Exception
 */
function update_user_meta( int $user_id, string $key, string $value )
{
    global $link;
    if ( is_user_meta($user_id, $key) )
    {
        $link->where('user_id', $user_id)->where('meta_key', $key)->update('user_meta', [
            'user_id'    => $user_id,
            'meta_key'   => $key,
            'meta_value' => $value
        ]);
        return true;
    }
    return add_user_meta($user_id, $key, $value);
}

/**
 * @param int $user_id
 * @param string $key
 * @return bool
 * @throws Exception
 */
function delete_user_meta( int $user_id, string $key ) : bool
{
    global $link;
    if ( is_user_meta($user_id, $key) )
    {
        $link->where('user_id', $user_id)->where('meta_key', $key)->delete('user_meta');
        return true;
    }
    return false;
}

/**
 * @param int $user_id
 * @param string $key
 * @return false|object|null
 */
function get_user_meta( int $user_id, string $key )
{
    global $link;
    return $link->get_var("SELECT `meta_value` FROM `user_meta` WHERE `user_id` = {$user_id} AND `meta_key` = '{$key}'");
}

/**
 * @param int $user_id
 * @return array
 * @throws Exception
 */
function reset_user( int $user_id ) : array
{
    global $link;
    $log  = get_log($user_id, 'reset');
    $user = user($user_id);
    if ( empty($log) )
    {
        $point           = get_point($user_id);
        $coin            = $user->coin;
        $user_vip_league = get_vip_league_user($user_id);
        add_log(
            'reset', serialize([
            'user'   => $user,
            'point'  => $point,
            'coin'   => $coin,
            'league' => $user_vip_league
        ]), $user_id
        );

//        $link->where('user_id', $user_id)->delete('points');
        $link->where('user_id', $user_id)->update('users',[
            'point' => 0
        ]);
        add_point(0, $user_id, 0);
        update_user([
            'coin'   => 0,
            'league' => null
        ], $user_id);
        delete_all_vip_league_user($user_id);
        return [
            'status'  => 200,
            'message' => 'کاربر با موفقیت ریست شد.'
        ];
    }
    else
    {

        $data = unserialize($log);

//        $link->where('user_id', $user_id)->delete('points');
        $link->where('user_id', $user_id)->update('users',[
            'point' => 0
        ]);
        /*$link->insert('points', [
            'user_id' => $user_id,
            'point'   => $data['point'],
        ]);*/
        $link->where('user_id', $user_id)->update('users',[
            'point' => $data['point']
        ]);

//        add_point(0, $user_id, $data['point']);
        update_user([
            'coin' => $data['coin']
        ], $user_id);

        if ( isset($data['league']) && count($data['league']) > 0 )
        {
            foreach ( $data['league'] as $datum )
            {
                if ( isset($datum->emoji) )
                {
                    $link->insert('user_league', [
                        'user_id' => $user_id,
                        'emoji'   => $datum->emoji,
                        'name'    => $datum->name ?? ''
                    ]);
                }
            }
        }
        delete_log($user_id, 'reset');
        return [
            'status'  => 200,
            'message' => 'کاربر با موفقیت بازیابی شد.'
        ];
    }
}