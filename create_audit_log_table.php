<?php
include("config.php");

echo "<h3>Creating Audit Log Table</h3>";

// Create audit_logs table
$sql = "CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if(mysqli_query($conn, $sql)){
    echo "✅ audit_logs table created successfully<br>";
} else {
    echo "❌ Error creating audit_logs table: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>✅ Audit log table setup completed!</strong><br>";
echo "<a href='audit_logs.php'>View Audit Logs</a>";

mysqli_close($conn);
?>
