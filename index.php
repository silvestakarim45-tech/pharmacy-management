<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="header">
        <h1>🏥 Pharmacy Management</h1>
        <h2>Karibu Tena</h2>
        <p style="color: #666; margin-top: 10px;">Ingiza kwenye akaunti yako</p>
    </div>

    <form method="POST" action="login.php">
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
</div>

</body>
</html>