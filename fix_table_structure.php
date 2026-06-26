<?php
include("config.php");

echo "<h3>Fixing Table Structure</h3>";

// Add status column to medicines table
$sql = "ALTER TABLE medicines ADD COLUMN status ENUM('available', 'unavailable') DEFAULT 'available' AFTER expiry_date";
if(mysqli_query($conn, $sql)){
    echo "✅ status column added to medicines table<br>";
} else {
    echo "ℹ️ status column already exists or error: " . mysqli_error($conn) . "<br>";
}

// Add seller_id column to sales table
$sql = "ALTER TABLE sales ADD COLUMN seller_id INT AFTER customer_id";
if(mysqli_query($conn, $sql)){
    echo "✅ seller_id column added to sales table<br>";
} else {
    echo "ℹ️ seller_id column already exists or error: " . mysqli_error($conn) . "<br>";
}

// Add customer_id column to sales table if it doesn't exist
$sql = "ALTER TABLE sales ADD COLUMN customer_id INT AFTER medicine_id";
if(mysqli_query($conn, $sql)){
    echo "✅ customer_id column added to sales table<br>";
} else {
    echo "ℹ️ customer_id column already exists or error: " . mysqli_error($conn) . "<br>";
}

// Add created_at column to customers table
$sql = "ALTER TABLE customers ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER address";
if(mysqli_query($conn, $sql)){
    echo "✅ created_at column added to customers table<br>";
} else {
    echo "ℹ️ created_at column already exists or error: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>✅ Table structure fixing completed!</strong><br>";
echo "<a href='seller_medicines.php'>Go to Seller Medicines</a>";

mysqli_close($conn);
?>
