<?php
session_start();
include("config.php");
include("functions.php");

$error = "";

if(isset($_POST['username']) && isset($_POST['password'])) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    if(!validate_username($username)){
        $error = "Username format batili";
    } else {

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result)==1){

        $row = mysqli_fetch_assoc($result);

        // Verify password using bcrypt
        if(!password_verify($password, $row['password'])){
            $error = "Username au Password si sahihi, au akaunti haija activated";
        } else {

        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['phone'] = $row['phone'];

        // Redirect based on role
        switch($row['role']) {
            case 'admin':
                header("Location: admin_dashboard.php");
                break;
            case 'seller':
                header("Location: seller_dashboard.php");
                break;
            case 'customer':
                header("Location: customer_dashboard.php");
                break;
            default:
                header("Location: index.php");
        }
        exit();
        }
    }
    else{
        $error = "Username au Password si sahihi, au akaunti haija activated";
    }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('pexels-karola-g-4040568.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #3498db;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .header h2 {
            color: #2c3e50;
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        .btn {
            width: 100%;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="header">
        <h1>🏥 Pharmacy Management</h1>
        <h2>Karibu Tena</h2>
        <p style="color: #666; margin-top: 10px;">Ingiza kwenye akaunti yako</p>
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
        <p style="color: #666;">Au login kama:</p>
        <a href="customer_login.php" style="color: #3498db; text-decoration: none; margin: 0 10px;">👤 Mteja</a>
    </div>
</div>

</body>
</html>