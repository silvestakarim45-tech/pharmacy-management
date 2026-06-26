<?php
include("config.php");

echo "<h3>Updating Existing Passwords to Bcrypt</h3>";

// Update admin password
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$admin_password' WHERE username='admin'";
if(mysqli_query($conn, $sql)){
    echo "✅ Admin password updated to bcrypt<br>";
} else {
    echo "❌ Error updating admin password: " . mysqli_error($conn) . "<br>";
}

// Update seller password
$seller_password = password_hash('seller123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$seller_password' WHERE username='seller'";
if(mysqli_query($conn, $sql)){
    echo "✅ Seller password updated to bcrypt<br>";
} else {
    echo "❌ Error updating seller password: " . mysqli_error($conn) . "<br>";
}

// Update customer password
$customer_password = password_hash('customer123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$customer_password' WHERE username='customer'";
if(mysqli_query($conn, $sql)){
    echo "✅ Customer password updated to bcrypt<br>";
} else {
    echo "❌ Error updating customer password: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>✅ All passwords updated successfully!</strong><br>";
echo "<a href='login.php'>Go to Login Page</a>";

mysqli_close($conn);
?>
