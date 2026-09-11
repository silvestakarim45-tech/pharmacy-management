<?php
session_start();
include("config.php");
include("functions.php");

$error = "";
$success = "";

$token = $_GET['token'] ?? '';

// Verify token
if(empty($token)){
    $error = "Token batili au imekosekana";
} else {
    $sql = "SELECT * FROM users WHERE reset_token=? AND reset_expiry > NOW()";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 0){
        $error = "Token imekufa au si sahihi";
    } else {
        $user = mysqli_fetch_assoc($result);

        // Process password change
        if(isset($_POST['reset_password'])){
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if(!validate_password($new_password)){
                $error = "Password inapaswa kuwa angalau characters 6";
            } elseif($new_password !== $confirm_password){
                $error = "Password hazilingani";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password and clear reset token
                $sql = "UPDATE users SET password=?, reset_token=NULL, reset_expiry=NULL WHERE user_id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user['user_id']);
                mysqli_stmt_execute($stmt);

                $success = "Password imebadilishwa kwa mafanikio! <a href='login.php' style='color: white;'>Login Sasa</a>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="header">
        <h1>🏥 Pharmacy</h1>
        <h2>Badilisha Password</h2>
        <p style="color: #666; margin-top: 10px;">Ingiza password mpya</p>
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

    <?php if(!$success && empty($error)): ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="form-group">
            <label>Password Mpya</label>
            <input type="password" name="new_password" placeholder="Ingiza password mpya" required minlength="6">
        </div>

        <div class="form-group">
            <label>Rudia Password</label>
            <input type="password" name="confirm_password" placeholder="Rudia password mpya" required minlength="6">
        </div>

        <button type="submit" name="reset_password" class="btn btn-success">Badilisha Password</button>
    </form>
    <?php endif; ?>

    <div style="margin-top: 20px; text-align: center;">
        <a href="login.php" style="color: #3498db; text-decoration: none;">← Rudi kwenye Login</a>
    </div>
</div>

</body>
</html>
