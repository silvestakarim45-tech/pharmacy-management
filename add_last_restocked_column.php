<?php
$conn = mysqli_connect("localhost", "root", "", "pharmacy_management");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Add last_restocked column to medicines table
$sql = "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS last_restocked DATE DEFAULT NULL";

if(mysqli_query($conn, $sql)){
    echo "✅ Column 'last_restocked' added successfully to medicines table<br>";
} else {
    echo "❌ Error adding column: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
