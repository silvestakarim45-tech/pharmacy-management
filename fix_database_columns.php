<?php
include("config.php");

echo "<h3>Fixing Database Columns</h3>";

// Function to check if column exists
function column_exists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

// Add email column to users table if it doesn't exist
if(!column_exists($conn, 'users', 'email')){
    $sql = "ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER fullname";
    if(mysqli_query($conn, $sql)){
        echo "✅ Email column added to users table<br>";
    } else {
        echo "❌ Error adding email column: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "ℹ️ Email column already exists in users table<br>";
}

// Add phone column to users table if it doesn't exist
if(!column_exists($conn, 'users', 'phone')){
    $sql = "ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email";
    if(mysqli_query($conn, $sql)){
        echo "✅ Phone column added to users table<br>";
    } else {
        echo "❌ Error adding phone column: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "ℹ️ Phone column already exists in users table<br>";
}

// Add user_id column to customers table if it doesn't exist
if(!column_exists($conn, 'customers', 'user_id')){
    $sql = "ALTER TABLE customers ADD COLUMN user_id INT AFTER customer_id";
    if(mysqli_query($conn, $sql)){
        echo "✅ user_id column added to customers table<br>";
    } else {
        echo "❌ Error adding user_id column: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "ℹ️ user_id column already exists in customers table<br>";
}

// Add status column to users table if it doesn't exist
if(!column_exists($conn, 'users', 'status')){
    $sql = "ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'";
    if(mysqli_query($conn, $sql)){
        echo "✅ Status column added to users table<br>";
    } else {
        echo "❌ Error adding status column: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "ℹ️ Status column already exists in users table<br>";
}

echo "<br><strong>✅ Database columns check completed!</strong><br>";
echo "<a href='login.php'>Go to Login Page</a>";

mysqli_close($conn);
?>
