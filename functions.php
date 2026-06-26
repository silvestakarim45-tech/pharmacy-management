<?php
// Input validation and sanitization functions

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    return preg_match('/^[+0-9]{10,15}$/', $phone);
}

function validate_password($password) {
    return strlen($password) >= 6;
}

function validate_number($value, $min = 0, $max = null) {
    if (!is_numeric($value)) return false;
    $value = floatval($value);
    if ($value < $min) return false;
    if ($max !== null && $value > $max) return false;
    return true;
}

function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Error handling function
function handle_db_error($conn, $operation = "database operation") {
    if(mysqli_error($conn)){
        error_log("Database Error in $operation: " . mysqli_error($conn));
        return false;
    }
    return true;
}

// CSRF Protection functions
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
