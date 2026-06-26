<?php
include("config.php");

echo "<h3>Checking Users in Database</h3>";

$sql = "SELECT user_id, username, password, fullname, role FROM users";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>User ID</th><th>Username</th><th>Password (MD5)</th><th>Fullname</th><th>Role</th></tr>";
    
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['password'] . "</td>";
        echo "<td>" . $row['fullname'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h3>Test Password Hashes:</h3>";
    echo "MD5 of 'admin123': " . md5('admin123') . "<br>";
    echo "MD5 of 'seller123': " . md5('seller123') . "<br>";
    echo "MD5 of 'customer123': " . md5('customer123') . "<br>";
} else {
    echo "No users found in database!";
}

mysqli_close($conn);
?>
