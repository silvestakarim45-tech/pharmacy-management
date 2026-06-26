<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle backup creation
if(isset($_POST['create_backup'])){
    $backup_dir = 'backups/';
    
    // Create backup directory if it doesn't exist
    if(!file_exists($backup_dir)){
        mkdir($backup_dir, 0777, true);
    }
    
    // Generate backup filename
    $backup_file = $backup_dir . 'pharmacy_backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Get database configuration
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'pharmacy_management';
    
    // Create backup using mysqldump
    $command = "C:\xampp\mysql\bin\mysqldump -h$db_host -u$db_user -p$db_pass $db_name > $backup_file";
    
    // Try to execute the command
    if(function_exists('exec')){
        exec($command, $output, $return_var);
        
        if($return_var === 0 && file_exists($backup_file)){
            $success = "Backup created successfully: " . basename($backup_file);
        } else {
            $error = "Failed to create backup using mysqldump. Trying alternative method...";
            
            // Alternative: PHP-based backup
            $backup_content = "-- Database Backup: $db_name\n";
            $backup_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Get all tables
            $tables = mysqli_query($conn, "SHOW TABLES");
            while($table = mysqli_fetch_row($tables)){
                $table_name = $table[0];
                $backup_content .= "-- Table: $table_name\n";
                
                // Get table structure
                $create_query = mysqli_query($conn, "SHOW CREATE TABLE $table_name");
                $create_table = mysqli_fetch_assoc($create_query);
                $backup_content .= $create_table['Create Table'] . ";\n\n";
                
                // Get table data
                $data_query = mysqli_query($conn, "SELECT * FROM $table_name");
                $columns = mysqli_num_fields($data_query);
                
                while($row = mysqli_fetch_row($data_query)){
                    $backup_content .= "INSERT INTO $table_name VALUES (";
                    for($i = 0; $i < $columns; $i++){
                        $value = $row[$i];
                        if($value === null){
                            $backup_content .= "NULL";
                        } else {
                            $backup_content .= "'" . mysqli_real_escape_string($conn, $value) . "'";
                        }
                        if($i < $columns - 1){
                            $backup_content .= ", ";
                        }
                    }
                    $backup_content .= ");\n";
                }
                $backup_content .= "\n";
            }
            
            // Save backup file
            if(file_put_contents($backup_file, $backup_content)){
                $success = "Backup created successfully using PHP method: " . basename($backup_file);
            } else {
                $error = "Failed to create backup file";
            }
        }
    } else {
        $error = "exec() function is disabled. Using PHP-based backup...";
        
        // PHP-based backup
        $backup_content = "-- Database Backup: $db_name\n";
        $backup_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $tables = mysqli_query($conn, "SHOW TABLES");
        while($table = mysqli_fetch_row($tables)){
            $table_name = $table[0];
            $backup_content .= "-- Table: $table_name\n";
            
            $create_query = mysqli_query($conn, "SHOW CREATE TABLE $table_name");
            $create_table = mysqli_fetch_assoc($create_query);
            $backup_content .= $create_table['Create Table'] . ";\n\n";
            
            $data_query = mysqli_query($conn, "SELECT * FROM $table_name");
            $columns = mysqli_num_fields($data_query);
            
            while($row = mysqli_fetch_row($data_query)){
                $backup_content .= "INSERT INTO $table_name VALUES (";
                for($i = 0; $i < $columns; $i++){
                    $value = $row[$i];
                    if($value === null){
                        $backup_content .= "NULL";
                    } else {
                        $backup_content .= "'" . mysqli_real_escape_string($conn, $value) . "'";
                    }
                    if($i < $columns - 1){
                        $backup_content .= ", ";
                    }
                }
                $backup_content .= ");\n";
            }
            $backup_content .= "\n";
        }
        
        if(file_put_contents($backup_file, $backup_content)){
            $success = "Backup created successfully: " . basename($backup_file);
        } else {
            $error = "Failed to create backup file";
        }
    }
}

// Handle backup deletion
if(isset($_POST['delete_backup'])){
    $backup_file = $_POST['backup_file'];
    if(file_exists($backup_file)){
        if(unlink($backup_file)){
            $success = "Backup deleted successfully";
        } else {
            $error = "Failed to delete backup";
        }
    } else {
        $error = "Backup file not found";
    }
}

// Handle backup restoration
if(isset($_POST['restore_backup'])){
    $backup_file = $_POST['backup_file'];
    
    if(file_exists($backup_file)){
        $backup_content = file_get_contents($backup_file);
        
        // Split by semicolon to get individual statements
        $statements = explode(';', $backup_content);
        
        $success_count = 0;
        $error_count = 0;
        
        foreach($statements as $statement){
            $statement = trim($statement);
            if(!empty($statement) && !str_starts_with($statement, '--')){
                try{
                    if(mysqli_query($conn, $statement)){
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } catch(Exception $e){
                    $error_count++;
                }
            }
        }
        
        if($success_count > 0){
            $success = "Backup restored successfully. $success_count statements executed.";
        } else {
            $error = "Failed to restore backup. No statements executed.";
        }
    } else {
        $error = "Backup file not found";
    }
}

// Get existing backups
$backup_dir = 'backups/';
$backups = [];
if(file_exists($backup_dir)){
    $files = scandir($backup_dir);
    foreach($files as $file){
        if($file != '.' && $file != '..' && str_ends_with($file, '.sql')){
            $filepath = $backup_dir . $file;
            $backups[] = [
                'name' => $file,
                'path' => $filepath,
                'size' => filesize($filepath),
                'date' => date('Y-m-d H:i:s', filemtime($filepath))
            ];
        }
    }
    
    // Sort by date (newest first)
    usort($backups, function($a, $b){
        return strtotime($b['date']) - strtotime($a['date']);
    });
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Backup - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Admin Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="medicine.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="inventory_management.php"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                <li><a href="sales_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="audit_logs.php"><i class="fas fa-history"></i> <span>Audit Logs</span></a></li>
                <li><a href="database_backup.php" class="active"><i class="fas fa-database"></i> <span>Backup</span></a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
                <li><a href="admin_customers.php"><i class="fas fa-user-friends"></i> <span>Wateja</span></a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo</span></a></li>
                <li><a href="sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>💾 Database Backup</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Create Backup -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>➕ Create New Backup</h2>
            </div>

            <form method="POST">
                <div class="alert alert-warning" style="margin-bottom: 20px;">
                    <strong>⚠️ Important:</strong> Creating a backup will save all database data including medicines, sales, customers, and orders.
                </div>
                <button type="submit" name="create_backup" class="btn btn-success">💾 Create Backup Now</button>
            </form>
        </div>

        <!-- Existing Backups -->
        <div class="container">
            <div class="header">
                <h2>📋 Existing Backups</h2>
            </div>

            <?php if(!empty($backups)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Backup Name</th>
                        <th>Size</th>
                        <th>Date Created</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($backups as $backup): ?>
                    <tr>
                        <td><strong><?php echo $backup['name']; ?></strong></td>
                        <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                        <td><?php echo $backup['date']; ?></td>
                        <td>
                            <a href="<?php echo $backup['path']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;" download>⬇️ Download</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="backup_file" value="<?php echo $backup['path']; ?>">
                                <button type="submit" name="restore_backup" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('⚠️ WARNING: This will overwrite current database with this backup. Are you sure?');">♻️ Restore</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="backup_file" value="<?php echo $backup['path']; ?>">
                                <button type="submit" name="delete_backup" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Una hakika unataka kufuta backup hii?');">🗑️ Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">Hakuna backups zilizopo</div>
            <?php endif; ?>
        </div>

        <!-- Backup Information -->
        <div class="container" style="margin-top: 30px;">
            <div class="header">
                <h2>ℹ️ Maelezo ya Backup</h2>
            </div>

            <div class="alert alert-info">
                <h4>📌 Mwongozo wa Backup:</h4>
                <ul>
                    <li><strong>Create Backup:</strong> Hifadhi nakala ya database yako kwa usalama</li>
                    <li><strong>Download:</strong> Pakua backup na uhifadhi kwenye kompyuta yako</li>
                    <li><strong>Restore:</strong> Rudisha database kutoka kwenye backup iliyopo</li>
                    <li><strong>Delete:</strong> Futa backups za zamani ili kuokoa nafasi</li>
                </ul>
                <h4>⚠️ Tahadhari:</h4>
                <ul>
                    <li>Restore itakuondoa data yote ya sasa na kubadilisha na data ya backup</li>
                    <li>Pendekeza kuunda backup mara moja kwa siku</li>
                    <li>Hifadhi backups muhimu kwenye kompyuta yako</li>
                </ul>
            </div>
        </div>
    </div>
</div>

</body>
</html>
