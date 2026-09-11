<?php
session_start();
include("config.php");
include("functions.php");

$error = "";
$success = "";

// Step 1: Request password reset
if(isset($_POST['request_reset'])){
    $email = sanitize_input($_POST['email']);

    if(!validate_email($email)){
        $error = "Email batili";
    } else {
        // Check if email exists in database
        $sql = "SELECT * FROM users WHERE email=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            $user = mysqli_fetch_assoc($result);

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database (you may need to add a password_reset_tokens table)
            // For now, we'll store it in the users table temporarily
            $sql = "UPDATE users SET reset_token=?, reset_expiry=? WHERE user_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $reset_token, $expiry_time, $user['user_id']);
            mysqli_stmt_execute($stmt);

            // In production, send email with reset link
            // For demo, show the reset link
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $reset_token;

            $success = "Reset link imetengenezwa. <br><strong>Reset Link:</strong> <a href='$reset_link'>$reset_link</a><br><small>(Katika production, link hii itatumwa kwa email)</small>";
        } else {
            // Don't reveal if email exists for security
            $success = "Kama email ipo, reset link itatumwa.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Reset - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="header">
        <h1>🏥 Pharmacy</h1>
        <h2>Reset Password</h2>
        <p style="color: #666; margin-top: 10px;">Ingiza email yako kurejesha password</p>
    </div>

    <?php if($error): ?>
    <div class="error-message">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="success-message" style="background: #27ae60; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Ingiza email yako" required>
        </div>

        <button type="submit" name="request_reset" class="btn btn-success">Tuma Reset Link</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <a href="login.php" style="color: #3498db; text-decoration: none;">← Rudi kwenye Login</a>
    </div>
</div>

</body>
</html>
