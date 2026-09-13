<?php
$conn = mysqli_connect("localhost", "root", "", "pharmacy_management");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Add supplier_id column to medicines table
$sql = "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS supplier_id INT DEFAULT NULL";

if(mysqli_query($conn, $sql)){
    echo "✅ Column 'supplier_id' added successfully to medicines table<br>";
} else {
    echo "❌ Error adding column: " . mysqli_error($conn) . "<br>";
}

// Add foreign key constraint
$sql_fk = "ALTER TABLE medicines ADD CONSTRAINT fk_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL";

if(mysqli_query($conn, $sql_fk)){
    echo "✅ Foreign key constraint added successfully<br>";
} else {
    echo "❌ Error adding foreign key (may already exist): " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
