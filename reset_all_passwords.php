<?php
include("config.php");

echo "<h3>Resetting All Passwords to Bcrypt</h3>";

// Update admin password
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$admin_password' WHERE username='admin'";
if(mysqli_query($conn, $sql)){
    echo "✅ Admin password updated<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Update seller password
$seller_password = password_hash('seller123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$seller_password' WHERE username='seller'";
if(mysqli_query($conn, $sql)){
    echo "✅ Seller password updated<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Update customer password
$customer_password = password_hash('customer123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$customer_password' WHERE username='customer'";
if(mysqli_query($conn, $sql)){
    echo "✅ Customer password updated<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>✅ All passwords reset successfully!</strong><br>";
echo "Login credentials:<br>";
echo "Admin: admin / admin123<br>";
echo "Seller: seller / seller123<br>";
echo "Customer: customer / customer123<br>";
echo "<br><a href='login.php'>Go to Login</a>";

mysqli_close($conn);
?>
