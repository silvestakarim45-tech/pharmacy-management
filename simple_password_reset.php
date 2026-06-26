<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pharmacy_management";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

echo "<h3>Simple Password Reset</h3>";

// Reset admin password
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$admin_pass' WHERE username='admin'";
if(mysqli_query($conn, $sql)){
    echo "✅ Admin password reset to: admin123<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Reset seller password
$seller_pass = password_hash('seller123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$seller_pass' WHERE username='seller'";
if(mysqli_query($conn, $sql)){
    echo "✅ Seller password reset to: seller123<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Reset customer password
$customer_pass = password_hash('customer123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$customer_pass' WHERE username='customer'";
if(mysqli_query($conn, $sql)){
    echo "✅ Customer password reset to: customer123<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>✅ Done! You can now login.</strong><br>";
echo "<a href='login.php'>Go to Login</a>";

mysqli_close($conn);
?>
