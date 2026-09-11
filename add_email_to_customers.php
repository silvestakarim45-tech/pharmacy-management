<?php
$conn = mysqli_connect("localhost", "root", "", "pharmacy_management");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Add email column to customers table
$sql = "ALTER TABLE customers ADD COLUMN IF NOT EXISTS email VARCHAR(100) DEFAULT NULL";

if(mysqli_query($conn, $sql)){
    echo "✅ Column 'email' added successfully to customers table<br>";
} else {
    echo "❌ Error adding column: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
