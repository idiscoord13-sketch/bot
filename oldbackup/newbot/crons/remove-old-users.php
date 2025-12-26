<?php
// Include the configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

try {
    // Create a PDO instance using the credentials from config.php
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB_NAME, USERNAME, PASSWORD);
    $pdoBackup = new PDO("mysql:host=" . HOST . ";dbname=iranimaf_deleted_info", USERNAME, PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoBackup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to delete records older than a specified number of days from a table
    function deleteOldRecords($pdo, $tableName, $days) {
        // Calculate the cutoff time as Unix timestamp
        $cutoffTime = time() - ($days * 24 * 60 * 60);

        // Prepare the SQL query to delete records older than the cutoff time
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE created_at < :cutoffTime");

        // Bind the cutoff time parameter
        $stmt->bindParam(':cutoffTime', $cutoffTime, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Output the number of rows affected
        echo "Deleted " . $stmt->rowCount() . " records from $tableName older than $days days.\n";
    }

    function deleteOldRecordsYmhd($pdo, $tableName, $days) {
        // Calculate the cutoff date as a formatted datetime string
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));

        // Prepare the SQL query to delete records older than the cutoff date
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE created_at < :cutoffDate");

        // Bind the cutoff date parameter
        $stmt->bindParam(':cutoffDate', $cutoffDate);

        // Execute the query and check for errors
        if ($stmt->execute()) {
            // Output the number of rows affected
            echo "Deleted " . $stmt->rowCount() . " records from $tableName older than $days days.\n";
        } else {
            // Log an error message if the query fails
            error_log("Failed to delete records from $tableName where created_at < $cutoffDate", 0);
        }
    }




    function deleteOldFilesAndEmptyDirectories($directory, $days) {
        $currentTime = time();
        $cutoffTime = $currentTime - ($days * 24 * 60 * 60);

        // Iterate over each Telegram ID directory inside the specified directory
        foreach (new DirectoryIterator($directory) as $subDir) {
            if ($subDir->isDir() && !$subDir->isDot()) { // Only process directories, skip '.' and '..'
                $subDirPath = $subDir->getRealPath();

                // Iterate over each file inside the Telegram ID directory
                $files = new DirectoryIterator($subDirPath);
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        // Check if the file was modified more than the cutoff time
                        if ($file->getMTime() < $cutoffTime) {
                            // Delete the file
                            unlink($file->getRealPath());
                            echo "Deleted file: " . $file->getRealPath() . "\n";
                        }
                    }
                }

                // Check if the directory is empty after file deletion
                if (!(new FilesystemIterator($subDirPath))->valid()) {
                    // Delete the directory
                    rmdir($subDirPath);
                    echo "Deleted empty directory: " . $subDirPath . "\n";
                }
            }
        }
    }

    // Function to backup user-related data before deletion with batching
    function getLastProcessedOffset() {
        if (file_exists('last_offset.txt')) {
            return (int)file_get_contents('last_offset.txt');
        }
        return 0;
    }

    // Function to save the last processed offset
    function saveLastProcessedOffset($offset) {
        file_put_contents('last_offset.txt', $offset);
    }

    // Function to backup user-related data before deletion with batching
    function backupAndRemoveInactiveUsers($pdoActive, $pdoBackup, $days, $batchSize = 100) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));

        // Count the total number of inactive users
        $totalStmt = $pdoActive->prepare("SELECT COUNT(*) FROM last_game_user WHERE last_time < :cutoffDate");
        $totalStmt->bindParam(':cutoffDate', $cutoffDate);
        $totalStmt->execute();
        $totalUsers = $totalStmt->fetchColumn();

        if ($totalUsers > 0) {
            $offset = getLastProcessedOffset();
            while ($offset < $totalUsers) {
                // Select a batch of users
                $stmt = $pdoActive->prepare("SELECT * FROM last_game_user WHERE last_time < :cutoffDate LIMIT :limit OFFSET :offset");
                $stmt->bindParam(':cutoffDate', $cutoffDate);
                $stmt->bindValue(':limit', $batchSize, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $user) {
                    $user_id = $user['user_id'];

                    // Backup data from active DB to backup DB
                    $tables = ['user_meta', 'users', 'users_names', 'point_daily'];
                    foreach ($tables as $table) {
                        $stmtActive = $pdoActive->prepare("SELECT * FROM $table WHERE user_id = :user_id");
                        $stmtActive->bindParam(':user_id', $user_id);
                        $stmtActive->execute();
                        $data = $stmtActive->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($data as $row) {
                            // Prepare insert statement for backup DB
                            $columns = implode(", ", array_keys($row));
                            $placeholders = ":" . implode(", :", array_keys($row));
                            $stmtBackup = $pdoBackup->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
                            foreach ($row as $key => $value) {
                                $stmtBackup->bindValue(":$key", $value);
                            }
                            $stmtBackup->execute();
                        }

                        // Delete from active DB after backup
                        $deleteStmt = $pdoActive->prepare("DELETE FROM $table WHERE user_id = :user_id");
                        $deleteStmt->bindParam(':user_id', $user_id);
                        $deleteStmt->execute();
                    }

                    // Finally, delete from last_game_user in active DB after backup
                    $deleteLastGameUserStmt = $pdoActive->prepare("DELETE FROM last_game_user WHERE user_id = :user_id");
                    $deleteLastGameUserStmt->bindParam(':user_id', $user_id);
                    $deleteLastGameUserStmt->execute();
                }

                // Save the last processed offset
                saveLastProcessedOffset($offset + $batchSize);

                // Move to the next batch
                $offset += $batchSize;

                // Output progress
                echo "Processed batch: Offset $offset, Batch size $batchSize.\n";
            }
            echo "Backed up and deleted inactive users.\n";
            // Reset the offset after completion
            saveLastProcessedOffset(0);
        } else {
            echo "No inactive users found.\n";
        }
    }


    // Example usage: Backup and remove inactive users in batches
    backupAndRemoveInactiveUsers($pdo, $pdoBackup, 15, 100);

    function handleUserRecovery($pdoActive, $pdoBackup, $user_id) {
        // Check if the user exists in the active database
        $stmt = $pdoActive->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User exists in active DB, no recovery needed
            return;
        } else {
            // User does not exist, check the backup DB
            $stmtBackup = $pdoBackup->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stmtBackup->bindParam(':user_id', $user_id);
            $stmtBackup->execute();

            if ($stmtBackup->rowCount() > 0) {
                // User found in backup DB, recover their data
                $tables = ['user_meta', 'users', 'users_names', 'point_daily'];
                foreach ($tables as $table) {
                    $stmtBackupTable = $pdoBackup->prepare("SELECT * FROM $table WHERE user_id = :user_id");
                    $stmtBackupTable->bindParam(':user_id', $user_id);
                    $stmtBackupTable->execute();
                    $data = $stmtBackupTable->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($data as $row) {
                        // Prepare insert statement for active DB
                        $columns = implode(", ", array_keys($row));
                        $placeholders = ":" . implode(", :", array_keys($row));
                        $stmtActiveInsert = $pdoActive->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
                        foreach ($row as $key => $value) {
                            $stmtActiveInsert->bindValue(":$key", $value);
                        }
                        $stmtActiveInsert->execute();
                    }

                    // Optionally, delete from the backup DB after recovery
                    $deleteBackupStmt = $pdoBackup->prepare("DELETE FROM $table WHERE user_id = :user_id");
                    $deleteBackupStmt->bindParam(':user_id', $user_id);
                    $deleteBackupStmt->execute();
                }

                echo "Recovered user data for user_id: $user_id.\n";
            } else {
                echo "No data found for user_id: $user_id in the backup DB.\n";
            }
        }
    }

    function deleteExpiredBans($pdo) {
        // Get the current Unix timestamp
        $currentTime = time();

        // Prepare the SQL query to delete rows where end_time is less than the current time
        $stmt = $pdo->prepare("DELETE FROM bans WHERE end_time < :currentTime");

        // Bind the current time parameter
        $stmt->bindParam(':currentTime', $currentTime, PDO::PARAM_INT);

        // Execute the query and check for errors
        if ($stmt->execute()) {
            // Output the number of rows affected
            echo "Deleted " . $stmt->rowCount() . " expired bans.\n";
        } else {
            // Log an error message if the query fails
            error_log("Failed to delete expired bans where end_time < $currentTime", 0);
        }
    }



    // Define the directories
    $filesDirectory = realpath(__DIR__ . '/../files');
    $chatsDirectory = realpath(__DIR__ . '/../../chats');

// Call the function for the 'files' directory
    deleteOldFilesAndEmptyDirectories($filesDirectory, 1);

// Call the function for the 'chats' directory
    deleteOldFilesAndEmptyDirectories($chatsDirectory, 1);

    backupAndRemoveInactiveUsers($pdo, $pdoBackup, 5);

    // Call the function for the 'private_chat' table
    deleteOldRecords($pdo, 'private_chat', 2);
    deleteOldRecordsYmhd($pdo, 'log', 2);
    deleteExpiredBans($pdo);


    // You can add more calls to deleteOldRecords() for other tables
    // deleteOldRecords($pdo, 'another_table', 2);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
