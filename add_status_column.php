<?php
include("config.php");

// Add status column to users table if it doesn't exist
$sql = "ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'";

if(mysqli_query($conn, $sql)){
    echo "✅ Status column added successfully to users table";
} else {
    echo "❌ Error adding status column: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
