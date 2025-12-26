<?php
$host = 'localhost';
$db = 'iranimaf_main';
$username = 'iranimaf_black';
$password = 'F{e.087U@QXH&;}?';
$mysqli_con = mysqli_connect($host, $username, $password, $db);
if (!$mysqli_con) {
    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}
$tables = ['users', 'user_meta', 'users_names', 'user_name'];
$time_tabel = 'log';
$duration = 10;



function create_tabels_files($mysqli_con, $tables = [], $time_tabel, $duration, $userId = false) {
    $query = "SELECT DISTINCT user_id,created_at
            FROM $time_tabel
            WHERE created_at > NOW() - INTERVAL $duration DAY
            GROUP BY user_id;";
    $result = mysqli_query($mysqli_con, $query);
    if (!$result) {
        return ('Query Error: ' . mysqli_error($mysqli_con));
    }
    $i = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $indexes[] = $row['user_id'];
        $i++;
    }
    if ($userId) {
        $indexes[] = $userId;
    }
    echo '<br> number of active users: ' . $i;

    $directory = __DIR__;
    $directory = $directory . '/tabel_files';
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true); // Create the directory recursively
    }
    $filePath = $directory . "/indexes.txt";
    $indexesString = implode(PHP_EOL, $indexes);
    file_put_contents($filePath, $indexesString);
    echo "<br> User IDs have been stored in the text file: $filePath";


    if ($userId) {
        $indexes = [$userId];
    }

    foreach ($tables as $table) {
        $user_ids_string = '(' . implode(', ', $indexes) . ')';

        $query = "SELECT *
        FROM $table
        WHERE user_id in $user_ids_string;";
        $result = mysqli_query($mysqli_con, $query);

        if ($result) {
            // Create a new table if it doesn't exist
            $create_table_query = "CREATE TABLE IF NOT EXISTS small_$table LIKE $table;";
            if (mysqli_query($mysqli_con, $create_table_query)) {
                // Delete old data from the new table
                $delete_query = "DELETE FROM small_$table;";
                if (mysqli_query($mysqli_con, $delete_query)) {
                    // Insert new data into the new table
                    while ($row = mysqli_fetch_assoc($result)) {
                        $columns = array_keys($row);
                        $values = array_map(function ($value) use ($mysqli_con) {
                            return "'" . mysqli_real_escape_string($mysqli_con, $value) . "'";
                        }, array_values($row));

                        $insert_query = "INSERT INTO small_$table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");";
                        if (!mysqli_query($mysqli_con, $insert_query)) {
                            echo "<br> Error inserting data into table 'small_$table': " . mysqli_error($mysqli_con) . "<br>";
                        }
                    }
                    echo "<br> Data for table '$table' has been successfully stored in the new table: small_$table<br>";
                } else {
                    echo "<br> Error deleting old data from table 'small_$table': " . mysqli_error($mysqli_con) . "<br>";
                }
            } else {
                echo "<br> Error creating table 'small_$table': " . mysqli_error($mysqli_con) . "<br>";
            }
        } else {
            echo "<br> Error retrieving data from table '$table': " . mysqli_error($mysqli_con) . "<br>";
        }
    }



    return 'files created successfuly';

};


$files_result = create_tabels_files($mysqli_con, $tables, $time_tabel, $duration)
?>
