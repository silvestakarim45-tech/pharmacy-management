<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "pharmacy_management"
);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");

// Enable error reporting for development (disable in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>