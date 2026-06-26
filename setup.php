<?php
$conn = mysqli_connect("localhost", "root", "", "pharmacy_management");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Create users table with enhanced fields
$sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role VARCHAR(20) NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $sql)){
    echo "✅ Users table created or already exists<br>";
} else {
    echo "❌ Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Create medicines table
$sql = "CREATE TABLE IF NOT EXISTS medicines (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    quantity INT NOT NULL DEFAULT 0,
    buying_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    expiry_date DATE NOT NULL,
    image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $sql)){
    echo "✅ Medicines table created or already exists<br>";
} else {
    echo "❌ Error creating medicines table: " . mysqli_error($conn) . "<br>";
}

// Create customers table
$sql = "CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if(mysqli_query($conn, $sql)){
    echo "✅ Customers table created or already exists<br>";
} else {
    echo "❌ Error creating customers table: " . mysqli_error($conn) . "<br>";
}

// Create sales table
$sql = "CREATE TABLE IF NOT EXISTS sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    customer_id INT,
    seller_id INT,
    quantity_sold INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id)
)";

if(mysqli_query($conn, $sql)){
    echo "✅ Sales table created or already exists<br>";
} else {
    echo "❌ Error creating sales table: " . mysqli_error($conn) . "<br>";
}

// Create orders table for online ordering
$sql = "CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    phone VARCHAR(20),
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
)";

if(mysqli_query($conn, $sql)){
    echo "✅ Orders table created or already exists<br>";
} else {
    echo "❌ Error creating orders table: " . mysqli_error($conn) . "<br>";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id)
)";

if(mysqli_query($conn, $sql)){
    echo "✅ Order items table created or already exists<br>";
} else {
    echo "❌ Error creating order items table: " . mysqli_error($conn) . "<br>";
}

// Check and create users with different roles
$users_to_create = [
    ['admin', 'admin123', 'System Administrator', 'admin@example.com', '+255123456789', 'admin'],
    ['seller', 'seller123', 'Pharmacy Seller', 'seller@example.com', '+255123456788', 'seller'],
    ['customer', 'customer123', 'Test Customer', 'customer@example.com', '+255123456787', 'customer']
];

foreach($users_to_create as $user_info){
    $check_user = "SELECT * FROM users WHERE username='$user_info[0]'";
    $result = mysqli_query($conn, $check_user);

    if(mysqli_num_rows($result) == 0){
        $password = password_hash($user_info[1], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, fullname, email, phone, role)
                VALUES ('$user_info[0]', '$password', '$user_info[2]', '$user_info[3]', '$user_info[4]', '$user_info[5]')";

        if(mysqli_query($conn, $sql)){
            echo "✅ $user_info[5] user '$user_info[0]' created successfully<br>";
        } else {
            echo "❌ Error creating $user_info[5] user: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "ℹ️ $user_info[5] user '$user_info[0]' already exists<br>";
    }
}

// Insert sample customer
$check_customer = "SELECT * FROM customers WHERE customer_name='Test Customer'";
$cust_result = mysqli_query($conn, $check_customer);

if(mysqli_num_rows($cust_result) == 0){
    $get_user_id = "SELECT user_id FROM users WHERE username='customer'";
    $user_result = mysqli_query($conn, $get_user_id);
    $user_row = mysqli_fetch_assoc($user_result);
    
    $sql = "INSERT INTO customers (user_id, customer_name, phone, email, address) 
            VALUES ('".$user_row['user_id']."', 'Test Customer', '+255123456787', 'customer@example.com', 'Dar es Salaam, Tanzania')";
    
    if(mysqli_query($conn, $sql)){
        echo "✅ Sample customer created<br>";
    }
}

// Insert some sample medicines if none exist
$check_medicines = "SELECT * FROM medicines";
$med_result = mysqli_query($conn, $check_medicines);

if(mysqli_num_rows($med_result) == 0){
    $sample_medicines = [
        ['Panadol Extra', 'Pain Relief', 'Effective pain relief for headaches and body aches', 50, 500, 700, '2025-12-31'],
        ['Amoxicillin 500mg', 'Antibiotics', 'Antibiotic for bacterial infections', 30, 2000, 2500, '2025-06-30'],
        ['Vitamin C', 'Vitamins', 'Immune system booster', 100, 800, 1200, '2026-01-15'],
        ['Cough Syrup', 'Cough Relief', 'Relief for cough and sore throat', 25, 1500, 2000, '2025-08-20'],
        ['Aspirin', 'Pain Relief', 'Pain reliever and anti-inflammatory', 40, 300, 500, '2025-11-15'],
        ['Paracetamol', 'Pain Relief', 'Fever reducer and pain reliever', 60, 400, 600, '2026-03-20'],
        ['Ibuprofen', 'Pain Relief', 'Anti-inflammatory pain medication', 35, 600, 900, '2025-09-10'],
        ['Antacid', 'Digestive', 'Relief for heartburn and indigestion', 45, 700, 1000, '2026-02-28']
    ];
    
    foreach($sample_medicines as $medicine){
        $sql = "INSERT INTO medicines (medicine_name, category, description, quantity, buying_price, selling_price, expiry_date) 
                VALUES ('$medicine[0]', '$medicine[1]', '$medicine[2]', $medicine[3], $medicine[4], $medicine[5], '$medicine[6]')";
        mysqli_query($conn, $sql);
    }
    echo "✅ Sample medicines inserted<br>";
} else {
    echo "ℹ️ Medicines already exist in database<br>";
}

echo "<br><strong>✅ Setup completed successfully!</strong><br><br>";
echo "<strong>📋 Login Credentials:</strong><br>";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
echo "<strong>👑 ADMIN (Boss):</strong><br>";
echo "Username: admin<br>";
echo "Password: admin123<br><br>";
echo "<strong>🛒 SELLER (Muuzaji):</strong><br>";
echo "Username: seller<br>";
echo "Password: seller123<br><br>";
echo "<strong>👤 CUSTOMER (Mteja):</strong><br>";
echo "Username: customer<br>";
echo "Password: customer123<br>";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>";
echo "<a href='index.php'>Go to Login Page</a> | ";
echo "<a href='customer_login.php'>Customer Login</a> | ";
echo "<a href='customer_register.php'>Customer Registration</a>";

mysqli_close($conn);
?>
