<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pharmacy_management";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

echo "<h3>Force Password Fix</h3>";

// First, make sure status column exists and set all users to active
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active'";
mysqli_query($conn, $sql);

$sql = "UPDATE users SET status='active' WHERE status IS NULL OR status=''";
mysqli_query($conn, $sql);

// Reset admin password
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$admin_pass', status='active' WHERE username='admin'";
if(mysqli_query($conn, $sql)){
    echo "✅ Admin password reset to: admin123, status set to active<br>";
} else {
    echo "❌ Error updating admin: " . mysqli_error($conn) . "<br>";
}

// Reset seller password
$seller_pass = password_hash('seller123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$seller_pass', status='active' WHERE username='seller'";
if(mysqli_query($conn, $sql)){
    echo "✅ Seller password reset to: seller123, status set to active<br>";
} else {
    echo "❌ Error updating seller: " . mysqli_error($conn) . "<br>";
}

// Reset customer password
$customer_pass = password_hash('customer123', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$customer_pass', status='active' WHERE username='customer'";
if(mysqli_query($conn, $sql)){
    echo "✅ Customer password reset to: customer123, status set to active<br>";
} else {
    echo "❌ Error updating customer: " . mysqli_error($conn) . "<br>";
}

// Verify the update
echo "<br><h3>Verification:</h3>";
$sql = "SELECT username, password, status FROM users WHERE username IN ('admin', 'seller', 'customer')";
$result = mysqli_query($conn, $sql);

echo "<table border='1'>";
echo "<tr><th>Username</th><th>Password Hash (first 50 chars)</th><th>Status</th></tr>";

while($row = mysqli_fetch_assoc($result)){
    echo "<tr>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . substr($row['password'], 0, 50) . "...</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><strong>✅ Done! Try login now.</strong><br>";
echo "<a href='login.php'>Go to Login</a>";

mysqli_close($conn);
?>
