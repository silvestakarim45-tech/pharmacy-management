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
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Management System - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .background-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('pexels-karola-g-4040568.jpg') center/cover;
            z-index: -1;
            filter: blur(5px);
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 50px 40px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .header .logo {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .header h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header h2 {
            color: #7f8c8d;
            font-size: 16px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            font-size: 18px;
            transition: color 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input:focus + i {
            color: #667eea;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #95a5a6;
            font-size: 18px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7f8c8d;
            cursor: pointer;
        }

        .remember-me input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #95a5a6;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 15px;
        }

        .customer-login {
            text-align: center;
        }

        .customer-login a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: #f8f9fa;
            color: #667eea;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid #e0e0e0;
        }

        .customer-login a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 25px;
            }

            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="background-overlay"></div>

<div class="login-container">
    <div class="header">
        <div class="logo">
            <i class="fas fa-hospital"></i>
        </div>
        <h1>Pharmacy Management</h1>
        <h2>System Login</h2>
    </div>

    <?php if($error): ?>
    <div class="error-message">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="form-group">
            <label>Username</label>
            <div class="input-wrapper">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Ingiza username" required autocomplete="username">
            </div>
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="login_password" placeholder="Ingiza password" required autocomplete="current-password">
                <i class="fas fa-eye password-toggle" id="toggle_password" onclick="togglePassword()"></i>
            </div>
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember">
                <span>Nikumbuke</span>
            </label>
            <a href="password_reset.php" class="forgot-password">Umesahau password?</a>
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>

    <div class="divider">
        <span>au</span>
    </div>

    <div class="customer-login">
        <a href="customer_login.php">
            <i class="fas fa-user"></i> Login kama Mteja
        </a>
    </div>
</div>

<script>
function togglePassword() {
    var passwordInput = document.getElementById("login_password");
    var toggleIcon = document.getElementById("toggle_password");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>