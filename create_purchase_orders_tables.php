<?php
$conn = mysqli_connect("localhost", "root", "", "pharmacy_management");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Create purchase_orders table
$sql1 = "CREATE TABLE IF NOT EXISTS purchase_orders (
    purchase_order_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expected_delivery_date DATE,
    total_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'ordered', 'received', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
)";

if(mysqli_query($conn, $sql1)){
    echo "✅ Table 'purchase_orders' created successfully<br>";
} else {
    echo "❌ Error creating purchase_orders table: " . mysqli_error($conn) . "<br>";
}

// Create purchase_order_items table
$sql2 = "CREATE TABLE IF NOT EXISTS purchase_order_items (
    purchase_order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id INT,
    medicine_id INT,
    quantity INT NOT NULL,
    buying_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    received_quantity INT DEFAULT 0,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(purchase_order_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE CASCADE
)";

if(mysqli_query($conn, $sql2)){
    echo "✅ Table 'purchase_order_items' created successfully<br>";
} else {
    echo "❌ Error creating purchase_order_items table: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
