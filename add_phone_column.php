<?php
include("config.php");

echo "<h3>Adding Phone Column to Users Table</h3>";

// Add phone column to users table if it doesn't exist
$sql = "ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email";

if(mysqli_query($conn, $sql)){
    echo "✅ Phone column added successfully to users table";
} else {
    echo "❌ Error adding phone column: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
