<?php

defined('BASE_DIR') || die('NO ACCESS');

function backup_tables($dir_save = '', $tables = '*')
{
    $link = mysqli_connect(HOST, USERNAME, PASSWORD, DB_NAME);

    // Check connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
    }

    mysqli_query($link, "SET NAMES 'utf8'");

    //get all of the tables
    if ($tables == '*') {
        $tables = array();
        $result = mysqli_query($link, 'SHOW TABLES');
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }

    $return = '';
    //cycle through
    foreach ($tables as $table) {
        $result = mysqli_query($link, 'SELECT * FROM ' . $table);
        $num_fields = mysqli_num_fields($result);
        $num_rows = mysqli_num_rows($result);

        $return .= 'DROP TABLE IF EXISTS ' . $table . ';';
        $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . $table));
        $return .= "\n\n" . $row2[1] . ";\n\n";
        $counter = 1;

        //Over tables
        for ($i = 0; $i < $num_fields; $i++) {   //Over rows
            while ($row = mysqli_fetch_row($result)) {
                if ($counter == 1) {
                    $return .= 'INSERT INTO ' . $table . ' VALUES(';
                } else {
                    $return .= '(';
                }

                //Over fields
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $return .= '"' . $row[$j] . '"';
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return .= ',';
                    }
                }

                if ($num_rows == $counter) {
                    $return .= ");\n";
                } else {
                    $return .= "),\n";
                }
                ++$counter;
            }
        }
        $return .= "\n\n\n";
    }

    //save file
    $table = implode(',', $tables);
    if ($dir_save == '')
        $fileName = FILES_DIR . '/backup-' . time() . '.sql';
    else
        $fileName = $dir_save . '/backup-' . time() . '.sql';
    $handle = fopen($fileName, 'w+');
    fwrite($handle, $return);
    if (fclose($handle)) {
        return true;
    }
    return false;
}