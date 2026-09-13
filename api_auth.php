<?php
session_start();
include("config.php");

// API Authentication for external supplier connections
class APIAuth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Generate API key for supplier
    public function generateApiKey($supplier_id) {
        $api_key = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $sql = "UPDATE suppliers SET api_key=?, api_key_expiry=? WHERE supplier_id=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $api_key, $expiry, $supplier_id);
        mysqli_stmt_execute($stmt);
        
        return $api_key;
    }
    
    // Verify API key
    public function verifyApiKey($api_key) {
        $sql = "SELECT supplier_id, supplier_name, status FROM suppliers WHERE api_key=? AND api_key_expiry > NOW() AND status='active'";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $api_key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }
    
    // Revoke API key
    public function revokeApiKey($supplier_id) {
        $sql = "UPDATE suppliers SET api_key=NULL, api_key_expiry=NULL WHERE supplier_id=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $supplier_id);
        mysqli_stmt_execute($stmt);
    }
    
    // Rate limiting
    private $rateLimit = 100; // 100 requests per minute
    private $rateLimitWindow = 60; // 60 seconds
    
    public function checkRateLimit($api_key) {
        $redis_key = "rate_limit_" . $api_key;
        
        // For simplicity, using session-based rate limiting
        // In production, use Redis or Memcached
        if(!isset($_SESSION['rate_limit'][$api_key])) {
            $_SESSION['rate_limit'][$api_key] = [
                'count' => 0,
                'window' => time()
            ];
        }
        
        $current_time = time();
        $window_start = $_SESSION['rate_limit'][$api_key]['window'];
        
        // Reset window if expired
        if($current_time - $window_start > $this->rateLimitWindow) {
            $_SESSION['rate_limit'][$api_key] = [
                'count' => 0,
                'window' => $current_time
            ];
        }
        
        // Check if rate limit exceeded
        if($_SESSION['rate_limit'][$api_key]['count'] >= $this->rateLimit) {
            return false;
        }
        
        // Increment counter
        $_SESSION['rate_limit'][$api_key]['count']++;
        return true;
    }
    
    // Log API access
    public function logApiAccess($supplier_id, $endpoint, $method, $status) {
        $sql = "INSERT INTO api_logs (supplier_id, endpoint, method, status, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->conn, $sql);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        mysqli_stmt_bind_param($stmt, "isssss", $supplier_id, $endpoint, $method, $status, $ip_address, $user_agent);
        mysqli_stmt_execute($stmt);
    }
}

// Create api_logs table if not exists
$sql = "CREATE TABLE IF NOT EXISTS api_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    status VARCHAR(20),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
)";

mysqli_query($conn, $sql);

// Add api_key columns to suppliers table if not exists
$sql1 = "ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS api_key VARCHAR(64) DEFAULT NULL";
$sql2 = "ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS api_key_expiry DATETIME DEFAULT NULL";

mysqli_query($conn, $sql1);
mysqli_query($conn, $sql2);
?>
