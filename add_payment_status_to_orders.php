<?php
$conn = mysqli_connect("localhost", "root", "", "pharmacy_management");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Add payment_status column to orders table
$sql = "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid'";

if(mysqli_query($conn, $sql)){
    echo "✅ Column 'payment_status' added successfully to orders table<br>";
} else {
    echo "❌ Error adding column: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
