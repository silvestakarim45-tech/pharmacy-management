<?php
include("config.php");

echo "<h3>Adding Email Column to Users Table</h3>";

// Add email column to users table if it doesn't exist
$sql = "ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER fullname";

if(mysqli_query($conn, $sql)){
    echo "✅ Email column added successfully to users table";
} else {
    echo "❌ Error adding email column: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
