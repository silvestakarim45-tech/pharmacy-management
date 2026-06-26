<?php
include("config.php");

echo "<h3>Create Seller Account</h3>";

$username = "seller";
$password = "seller123";
$fullname = "Pharmacist Seller";
$email = "seller@pharmacy.com";
$phone = "0712345678";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if seller already exists
$check_query = "SELECT * FROM users WHERE username='$username'";
$check_result = mysqli_query($conn, $check_query);

if(mysqli_num_rows($check_result) > 0){
    // Update existing seller
    $sql = "UPDATE users SET password='$hashed_password', fullname='$fullname', email='$email', phone='$phone', role='seller' WHERE username='$username'";
    if(mysqli_query($conn, $sql)){
        echo "✅ Seller account updated successfully<br>";
    } else {
        echo "❌ Error updating seller: " . mysqli_error($conn) . "<br>";
    }
} else {
    // Create new seller
    $sql = "INSERT INTO users (username, password, fullname, email, phone, role) 
            VALUES ('$username', '$hashed_password', '$fullname', '$email', '$phone', 'seller')";
    if(mysqli_query($conn, $sql)){
        echo "✅ Seller account created successfully<br>";
    } else {
        echo "❌ Error creating seller: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br><strong>Login Credentials:</strong><br>";
echo "Username: $username<br>";
echo "Password: $password<br>";
echo "<br><a href='login.php'>Go to Login</a>";

mysqli_close($conn);
?>
