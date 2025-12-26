<?php
// Database credentials
const HOST = 'localhost';
define("USERNAME", 'iranimaf_black');
const PASSWORD = 'F{e.087U@QXH&;}?';
const DB_NAME = 'iranimaf_main';

// Create a connection
$conn = new mysqli(HOST, USERNAME, PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Backup function for current runtime variables
function backupVariables($conn) {
    $variables = [
        'innodb_buffer_pool_size',
        'innodb_log_file_size',
        'innodb_log_buffer_size',
        'innodb_flush_log_at_trx_commit',
        'innodb_file_per_table',
        'innodb_flush_method',
        'join_buffer_size',
        'thread_cache_size',
        'table_open_cache',
        'performance_schema',
        'skip_name_resolve'
    ];

    $backup = [];
    foreach ($variables as $variable) {
        $sql = "SHOW VARIABLES LIKE '$variable'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $backup[$variable] = $row['Value'];
        }
    }
    return $backup;
}

// Restore function for runtime variables
function restoreVariables($conn, $backup) {
    foreach ($backup as $key => $value) {
        $sql = "SET GLOBAL $key = '$value'";
        if ($conn->query($sql) === TRUE) {
            echo "Variable $key restored to $value successfully.<br>";
        } else {
            echo "Error restoring variable $key: " . $conn->error . "<br>";
        }
    }
}

// Optimize all tables in the database
function optimizeTables($conn) {
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $table = $row[0];
            $optimizeSql = "OPTIMIZE TABLE $table";
            if ($conn->query($optimizeSql) === TRUE) {
                echo "Table $table optimized successfully.<br>";
            } else {
                echo "Error optimizing table $table: " . $conn->error . "<br>";
            }
        }
    } else {
        echo "No tables found in the database.<br>";
    }
}

// Adjust runtime variables
function adjustRuntimeVariables($conn) {
    $variables = [
        'innodb_buffer_pool_size' => '4G',
        'innodb_log_file_size' => '1G',
        'innodb_log_buffer_size' => '32M',
        'innodb_flush_log_at_trx_commit' => '1',
        'innodb_file_per_table' => '1',
        'innodb_flush_method' => 'O_DIRECT',
        'join_buffer_size' => '512K',
        'thread_cache_size' => '8',
        'table_open_cache' => '1024',
        'performance_schema' => 'ON',
        'skip_name_resolve' => 'ON'
    ];

    foreach ($variables as $key => $value) {
        $sql = "SET GLOBAL $key = '$value'";
        if ($conn->query($sql) === TRUE) {
            echo "Variable $key set to $value successfully.<br>";
        } else {
            echo "Error setting variable $key: " . $conn->error . "<br>";
        }
    }
}

// Run optimization and adjust variables
$backup = backupVariables($conn);
optimizeTables($conn);
echo "<br><strong>Adjusting MySQL Runtime Variables:</strong><br>";
adjustRuntimeVariables($conn);

// If you need to revert changes, uncomment the following line
// restoreVariables($conn, $backup);

// Close the connection
$conn->close();
?>
