<?php
session_start();
include("config.php");
include("functions.php");

$error = "";

if(isset($_POST['username']) && isset($_POST['password'])) {
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid. Tafadhali jaribu tena.";
    } else {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    if(!validate_username($username)){
        $error = "Username format batili";
    } else {

    $sql = "SELECT * FROM users WHERE username=? AND role='customer'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result)==1){

        $row = mysqli_fetch_assoc($result);

        // Verify password using bcrypt
        if(!password_verify($password, $row['password'])){
            $error = "Username au Password si sahihi";
        } else {

        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['phone'] = $row['phone'];

        header("Location: customer_dashboard.php");
        exit();
        }
    }
    else{
        $error = "Username au Password si sahihi";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Login - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="header">
        <h1>🏥 Pharmacy</h1>
        <h2>Customer Login</h2>
        <p style="color: #666; margin-top: 10px;">Ingiza kwenye akaunti yako ya mteja</p>
    </div>

    <?php if($error): ?>
    <div class="error-message">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Ingiza username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Ingiza password" required>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <p style="color: #666;">Huna akaunti?</p>
        <a href="customer_register.php" style="color: #3498db; text-decoration: none;">Jisajili Hapa</a>
    </div>

    <div style="margin-top: 15px; text-align: center;">
        <a href="index.php" style="color: #7f8c8d; text-decoration: none; font-size: 14px;">← Rudi kwenye Login Kuu</a>
    </div>
</div>

</body>
</html>