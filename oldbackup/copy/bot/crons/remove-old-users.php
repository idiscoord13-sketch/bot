<?php
// Include the configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

try {
    // Create a PDO instance using the credentials from config.php
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB_NAME, USERNAME, PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "ok";
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

    // Define the directories
    $filesDirectory = realpath(__DIR__ . '/../files');
    $chatsDirectory = realpath(__DIR__ . '/../../chats');

// Call the function for the 'files' directory
    deleteOldFilesAndEmptyDirectories($filesDirectory, 1);

// Call the function for the 'chats' directory
    deleteOldFilesAndEmptyDirectories($chatsDirectory, 1);


    // Call the function for the 'private_chat' table
    deleteOldRecords($pdo, 'private_chat', 2);

    // You can add more calls to deleteOldRecords() for other tables
    // deleteOldRecords($pdo, 'another_table', 2);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
