<?php
include("config.php");

echo "<h3>Resetting Admin Password</h3>";

// Check if admin user exists
$check = "SELECT * FROM users WHERE username='admin'";
$result = mysqli_query($conn, $check);

if(mysqli_num_rows($result) > 0){
    // Update password
    $new_password = md5('admin123');
    $sql = "UPDATE users SET password='$new_password' WHERE username='admin'";
    
    if(mysqli_query($conn, $sql)){
        echo "✅ Admin password reset successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        echo "❌ Error updating password: " . mysqli_error($conn);
    }
} else {
    // Create admin user
    $password = md5('admin123');
    $sql = "INSERT INTO users (username, password, fullname, email, phone, role) 
            VALUES ('admin', '$password', 'System Administrator', 'admin@example.com', '+255123456789', 'admin')";
    
    if(mysqli_query($conn, $sql)){
        echo "✅ Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        echo "❌ Error creating admin user: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
