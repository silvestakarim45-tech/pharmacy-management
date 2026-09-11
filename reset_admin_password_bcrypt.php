<?php
include("config.php");

// Reset admin password to "admin123" using bcrypt
$username = "admin";
$new_password = "admin123";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password=? WHERE username=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $username);

if(mysqli_stmt_execute($stmt)){
    echo "✅ Password ya admin imerejeshwa kwa ufanisi!<br>";
    echo "Username: admin<br>";
    echo "Password mpya: admin123<br>";
    echo "<a href='login.php'>Bonyeza hapa kuingia</a>";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
