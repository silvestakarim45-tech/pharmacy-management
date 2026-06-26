<?php
include("config.php");

echo "<h3>Adding Inventory Management Columns</h3>";

// Add reorder_point column
$sql = "ALTER TABLE medicines ADD COLUMN reorder_point INT DEFAULT 10 AFTER quantity";
if(mysqli_query($conn, $sql)){
    echo "✅ reorder_point column added to medicines table<br>";
} else {
    echo "ℹ️ reorder_point column already exists or error: " . mysqli_error($conn) . "<br>";
}

// Add supplier_id column
$sql = "ALTER TABLE medicines ADD COLUMN supplier_id INT AFTER reorder_point";
if(mysqli_query($conn, $sql)){
    echo "✅ supplier_id column added to medicines table<br>";
} else {
    echo "ℹ️ supplier_id column already exists or error: " . mysqli_error($conn) . "<br>";
}

// Add last_restocked column
$sql = "ALTER TABLE medicines ADD COLUMN last_restocked DATE AFTER supplier_id";
if(mysqli_query($conn, $sql)){
    echo "✅ last_restocked column added to medicines table<br>";
} else {
    echo "ℹ️ last_restocked column already exists or error: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>✅ Inventory columns added successfully!</strong><br>";
echo "<a href='inventory_management.php'>Go to Inventory Management</a>";

mysqli_close($conn);
?>
