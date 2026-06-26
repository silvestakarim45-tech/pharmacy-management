<?php
include("config.php");

echo "<h3>Adding seller_id Column to Sales Table</h3>";

// Add seller_id column to sales table if it doesn't exist
$sql = "ALTER TABLE sales ADD COLUMN seller_id INT AFTER customer_id";

if(mysqli_query($conn, $sql)){
    echo "✅ seller_id column added successfully to sales table";
} else {
    echo "❌ Error adding seller_id column: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
