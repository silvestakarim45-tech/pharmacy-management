<?php
include("config.php");

echo "<h3>Adding description Column to Medicines Table</h3>";

// Add description column if it doesn't exist
$sql = "ALTER TABLE medicines ADD COLUMN description TEXT AFTER category";

if(mysqli_query($conn, $sql)){
    echo "✅ description column added successfully to medicines table";
} else {
    echo "❌ Error adding description column: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
