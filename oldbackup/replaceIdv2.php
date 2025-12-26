<?php
// Start the session to store messages
session_start();

// Define database credentials
const HOST = 'localhost'; // localhost
    define( "USERNAME", 'iranimaf_black');
    define('PASSWORD', 'F{e.087U@QXH&;}?');

// Define the target database
define('TARGET_DB', 'iranimaf_main'); // Main DB

// Define the tables to update in iranimaf_main
$tablesToUpdate = ['user_league', 'user_coupon', 'subscriptions', 'friends'];

// Initialize variables for messages
$success = '';
$error = '';

// Function to update user_id in specified tables
function updateUserIdInTables($conn, $tables, $old_user_id, $new_user_id, &$successMessages) {
    foreach ($tables as $table) {
        // Prepare the UPDATE statement
        $stmt = $conn->prepare("UPDATE `$table` SET user_id = ? WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Preparation failed for table `$table`: " . $conn->error);
        }

        // Bind parameters as strings
        $stmt->bind_param("ss", $new_user_id, $old_user_id);

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execution failed for table `$table`: " . $stmt->error);
        }

        // Check if any rows were updated
        if ($stmt->affected_rows > 0) {
            $successMessages .= "Successfully updated `user_id` in `<strong>$table</strong>`.<br>";
        } else {
            $successMessages .= "No records found to update in `<strong>$table</strong>`.<br>";
        }

        // Close the statement
        $stmt->close();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $old_user_id = isset($_POST['old_user_id']) ? trim($_POST['old_user_id']) : '';
    $new_user_id = isset($_POST['new_user_id']) ? trim($_POST['new_user_id']) : '';

    // Basic validation
    if (empty($old_user_id) || empty($new_user_id)) {
        $error = 'Both Old User ID and New User ID are required.';
    } else {
        // Prevent replacing a user ID with itself
        if ($old_user_id === $new_user_id) {
            $error = 'Old User ID and New User ID cannot be the same.';
        } else {
            // Proceed with database operations
            // Create a new mysqli connection
            $conn = new mysqli(HOST, USERNAME, PASSWORD, TARGET_DB);

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
                        // Step 1: Replace old_user_id with new_user_id in specified tables
                        updateUserIdInTables($conn, $tablesToUpdate, $old_user_id, $new_user_id, $success);

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
        input[type="text"] {
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
        <input type="text" id="old_user_id" name="old_user_id" required>

        <label for="new_user_id">New User ID:</label>
        <input type="text" id="new_user_id" name="new_user_id" required>

        <input type="submit" value="Replace User ID">
    </form>
</div>

</body>
</html>
