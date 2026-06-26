<?php
include("config.php");

echo "<h3>Creating Suppliers Table</h3>";

// Create suppliers table
$sql = "CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50) DEFAULT 'Tanzania',
    notes TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $sql)){
    echo "✅ suppliers table created successfully<br>";
} else {
    echo "❌ Error creating suppliers table: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>✅ Suppliers table setup completed!</strong><br>";
echo "<a href='suppliers.php'>Manage Suppliers</a>";

mysqli_close($conn);
?>
