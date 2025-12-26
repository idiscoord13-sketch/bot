<?php
require_once('../config.php');

$host = 'localhost';
$conn = mysqli_connect($host, USERNAME, PASSWORD, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SHOW TRIGGERS";
$result = mysqli_query($conn, $sql);

$found = false;
while ($row = mysqli_fetch_assoc($result)) {
    $trigger_name = $row['Trigger'];
    $sql = "DROP TRIGGER IF EXISTS $trigger_name";
    echo "کد مخرب $trigger_name حذف شد.<br>";
    mysqli_query($conn, $sql);
    $found = true;
}

if(!$found){
    echo "کد مخربی پیدا نشد!";
}

mysqli_close($conn);
?>