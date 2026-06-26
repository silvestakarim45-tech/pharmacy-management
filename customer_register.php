<?php
session_start();
include("config.php");
include("functions.php");

$error = "";
$success = "";

if(isset($_POST['register'])){
    $username = sanitize_input($_POST['username']);
    $fullname = sanitize_input($_POST['fullname']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $password = $_POST['password'];

    // Validate inputs
    if(!validate_username($username)){
        $error = "Username lazima iwe herufi na namba tu, 3-20 characters";
    } elseif(!validate_email($email)){
        $error = "Email batili";
    } elseif(!validate_phone($phone)){
        $error = "Namba ya simu batili";
    } elseif(!validate_password($password)){
        $error = "Password inapaswa kuwa angalau characters 6";
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $check_username = "SELECT * FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $check_username);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0){
        $error = "Username tayari ipo!";
    } else {
        // Insert into users table
        $sql = "INSERT INTO users (username, password, fullname, email, phone, role)
                VALUES (?, ?, ?, ?, ?, 'customer')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $username, $password, $fullname, $email, $phone);
        mysqli_stmt_execute($stmt);
        $user_id = mysqli_insert_id($conn);

        // Insert into customers table
        $customer_sql = "INSERT INTO customers (user_id, customer_name, phone, email, address)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $customer_sql);
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $fullname, $phone, $email, $address);
        if(mysqli_stmt_execute($stmt)){
                $success = "Umefanikiwa kujisajili! Sasa unaweza login.";
            } else {
                $error = "Error creating customer profile: " . mysqli_error($conn);
            }
        } else {
            $error = "Error creating user: " . mysqli_error($conn);
        }
    }
    }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="header">
        <h1>🏥 Pharmacy</h1>
        <h2>Jisajili kama Mteja</h2>
        <p style="color: #666; margin-top: 10px;">Jaza taarifa zako kujisajili</p>
    </div>

    <?php if($error): ?>
    <div class="error-message">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="success-message">
        <?php echo $success; ?>
        <div style="margin-top: 10px;">
            <a href="customer_login.php" class="btn" style="color: white;">Login Sasa</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!$success): ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="form-group">
            <label>Jina Kamili</label>
            <input type="text" name="fullname" placeholder="Ingiza jina lako kamili" required>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Chagua username" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Ingiza email yako" required>
        </div>

        <div class="form-group">
            <label>Namba ya Simu</label>
            <input type="tel" name="phone" placeholder="Ingiza namba ya simu" required>
        </div>

        <div class="form-group">
            <label>Anuani</label>
            <input type="text" name="address" placeholder="Ingiza anuani yako" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Chagua password" required>
        </div>

        <button type="submit" name="register" class="btn btn-success">Jisajili</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <p style="color: #666;">Tayari uko na akaunti?</p>
        <a href="customer_login.php" style="color: #3498db; text-decoration: none;">Login Hapa</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>