<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pharmacy_management";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

echo "<h3>Debug Login Information</h3>";

// Check users in database
$sql = "SELECT user_id, username, password, role, status FROM users";
$result = mysqli_query($conn, $sql);

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Username</th><th>Password Hash</th><th>Role</th><th>Status</th></tr>";

while($row = mysqli_fetch_assoc($result)){
    echo "<tr>";
    echo "<td>" . $row['user_id'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . substr($row['password'], 0, 50) . "...</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h3>Test Password Verification:</h3>";

// Test admin password
$sql = "SELECT password FROM users WHERE username='admin'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$stored_hash = $row['password'];

$test_password = 'admin123';
if(password_verify($test_password, $stored_hash)){
    echo "✅ Admin password 'admin123' matches stored hash<br>";
} else {
    echo "❌ Admin password 'admin123' does NOT match stored hash<br>";
    echo "Stored hash: " . $stored_hash . "<br>";
    echo "New hash for 'admin123': " . password_hash('admin123', PASSWORD_DEFAULT) . "<br>";
}

mysqli_close($conn);
?>
