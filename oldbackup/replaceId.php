<?php
// Start the session to store messages
session_start();

// Define database credentials
const HOST = 'localhost'; // localhost
    define( "USERNAME", 'iranimaf_black');
    define('PASSWORD', 'F{e.087U@QXH&;}?');

// Define database names
define('IRANIMAF_DELETED_INFO_DB', 'iranimaf_deleted_info'); // Deleted info DB
define('IRANIMAF_MAIN_DB', 'iranimaf_main'); // Main DB

// Define the tables to update in iranimaf_deleted_info
$tables = ['user_meta', 'users', 'users_names', 'point_daily'];

// Initialize variables for messages
$success = '';
$error = '';

// Function to update user_id in a specific database
function updateUserId($conn, $dbName, $tables, $old_user_id, $new_user_id, &$successMessages) {
    // Select the database
    if (!$conn->select_db($dbName)) {
        throw new Exception("Cannot select database $dbName: " . $conn->error);
    }

    // Iterate over each table and update user_id
    foreach ($tables as $table) {
        // Prepare the UPDATE statement
        $stmt = $conn->prepare("UPDATE `$table` SET user_id = ? WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for table `$table` in database `$dbName`: " . $conn->error);
        }
        $stmt->bind_param("ii", $new_user_id, $old_user_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for table `$table` in database `$dbName`: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            $successMessages .= "Updated user_id in `$dbName`.`$table` table.<br>";
        } else {
            $successMessages .= "No records updated in `$dbName`.`$table` table.<br>";
        }

        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $old_user_id = isset($_POST['old_user_id']) ? trim($_POST['old_user_id']) : '';
    $new_user_id = isset($_POST['new_user_id']) ? trim($_POST['new_user_id']) : '';

    // Basic validation
    if (empty($old_user_id) || empty($new_user_id)) {
        $error = 'Both Old User ID and New User ID are required.';
    } elseif (!ctype_digit($old_user_id) || !ctype_digit($new_user_id)) {
        $error = 'User IDs must be numeric.';
    } else {
        // Proceed with database operations
        // Create a new mysqli connection
        $conn = new mysqli(HOST, USERNAME, PASSWORD);

        // Check connection
        if ($conn->connect_error) {
            $error = 'Database connection failed: ' . $conn->connect_error;
        } else {
            // Set charset to utf8mb4
            if (!$conn->set_charset("utf8mb4")) {
                $error = "Error loading character set utf8mb4: " . $conn->error;
            } else {
                // Begin transaction
                $conn->begin_transaction();

                try {
                    // Select the iranimaf_deleted_info database
                    if (!$conn->select_db(IRANIMAF_DELETED_INFO_DB)) {
                        throw new Exception("Cannot select database " . IRANIMAF_DELETED_INFO_DB . ": " . $conn->error);
                    }

                    // Prepare statement to check if old_user_id exists in iranimaf_deleted_info.users
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param("i", $old_user_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }
                    $stmt->bind_result($count_deleted);
                    $stmt->fetch();
                    $stmt->close();

                    if ($count_deleted == 0) {
                        throw new Exception("Old User ID {$old_user_id} does not exist in " . IRANIMAF_DELETED_INFO_DB . ".users.");
                    }

                    // Now, check in iranimaf_main.users if new_user_id exists
                    if (!$conn->select_db(IRANIMAF_MAIN_DB)) {
                        throw new Exception("Cannot select database " . IRANIMAF_MAIN_DB . ": " . $conn->error);
                    }

                    // Prepare statement to check if new_user_id exists in iranimaf_main.users
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param("i", $new_user_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }
                    $stmt->bind_result($count_main);
                    $stmt->fetch();
                    $stmt->close();

                    if ($count_main > 0) {
                        // Delete the row with new_user_id in iranimaf_main.users
                        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $new_user_id);
                        if (!$stmt->execute()) {
                            throw new Exception("Execute failed: " . $stmt->error);
                        }
                        if ($stmt->affected_rows > 0) {
                            $success .= "Deleted existing New User ID {$new_user_id} from " . IRANIMAF_MAIN_DB . ".users.<br>";
                        }
                        // Close the statement regardless of deletion
                        $stmt->close();
                    }
                    // If new_user_id does not exist, do not throw any error or message

                    // Now, perform the replacement in iranimaf_deleted_info
                    // Re-select the iranimaf_deleted_info database
                    if (!$conn->select_db(IRANIMAF_DELETED_INFO_DB)) {
                        throw new Exception("Cannot re-select database " . IRANIMAF_DELETED_INFO_DB . ": " . $conn->error);
                    }

                    // Update user_id in the specified tables
                    updateUserId($conn, IRANIMAF_DELETED_INFO_DB, $tables, $old_user_id, $new_user_id, $success);

                    // Commit the transaction
                    $conn->commit();

                    if (empty($success)) {
                        $success = "No changes were made.";
                    }
                } catch (Exception $e) {
                    // Rollback the transaction on error
                    $conn->rollback();
                    $error = "Error: " . $e->getMessage();
                }

                // Close the connection
                $conn->close();
            }
        }
    }

    // Store messages in session to display after redirect
    $_SESSION['success'] = $success;
    $_SESSION['error'] = $error;

    // Redirect to the same page to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Retrieve messages from session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User ID Replacement Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px 30px 30px 30px;
            border-radius: 8px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 6px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #ddffdd;
            border: 1px solid #4CAF50;
            color: #333;
        }
        .error {
            background-color: #ffdddd;
            border: 1px solid #f44336;
            color: #a94442;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>User ID Replacement</h2>

    <?php if (!empty($success)): ?>
        <div class="message success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="message error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="old_user_id">Old User ID:</label>
        <input type="number" id="old_user_id" name="old_user_id" required>

        <label for="new_user_id">New User ID:</label>
        <input type="number" id="new_user_id" name="new_user_id" required>

        <input type="submit" value="Replace User ID">
    </form>
</div>

</body>
</html>
