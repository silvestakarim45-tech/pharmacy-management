<?php
$conn = mysqli_connect("localhost", "root", "", "pharmacy_management");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Add reset_token and reset_expiry columns to users table
$sql1 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) DEFAULT NULL";
$sql2 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_expiry DATETIME DEFAULT NULL";

if(mysqli_query($conn, $sql1)){
    echo "✅ Column 'reset_token' added successfully to users table<br>";
} else {
    echo "❌ Error adding reset_token column: " . mysqli_error($conn) . "<br>";
}

if(mysqli_query($conn, $sql2)){
    echo "✅ Column 'reset_expiry' added successfully to users table<br>";
} else {
    echo "❌ Error adding reset_expiry column: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
