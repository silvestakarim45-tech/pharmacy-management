<?php
include 'config/db.php';

if(isset($_POST['register'])){

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $department = $_POST['department'];

    $sql = "INSERT INTO users(fullname,email,password,role,department)
            VALUES('$fullname','$email','$password','$role','$department')";

    if($conn->query($sql)){
        echo "User registered successfully";
    }else{
        echo "Error";
    }
}
?>

<form method="POST">
    <input type="text" name="fullname" placeholder="Full Name" required><br>

    <input type="email" name="email" placeholder="Email" required><br>

    <input type="password" name="password" placeholder="Password" required><br>

    <input type="text" name="department" placeholder="Department"><br>

    <select name="role">
        <option value="employee">Employee</option>
        <option value="hr">HR</option>
        <option value="boss">Boss</option>
    </select><br>

    <button type="submit" name="register">Register</button>
</form>