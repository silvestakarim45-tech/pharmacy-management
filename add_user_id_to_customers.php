<?php
include("config.php");

echo "<h3>Adding user_id Column to Customers Table</h3>";

// Add user_id column to customers table if it doesn't exist
$sql = "ALTER TABLE customers ADD COLUMN user_id INT AFTER customer_id";

if(mysqli_query($conn, $sql)){
    echo "✅ user_id column added successfully to customers table";
} else {
    echo "❌ Error adding user_id column: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
