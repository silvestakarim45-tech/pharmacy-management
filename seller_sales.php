<?php
session_start();
include("config.php");

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

$medicines=mysqli_query($conn,
"SELECT * FROM medicines");

if(isset($_POST['sell'])){
    $medicine_id=$_POST['medicine_id'];
    $quantity_sold=$_POST['quantity_sold'];

    $medicine=mysqli_query($conn,
    "SELECT * FROM medicines
    WHERE medicine_id='$medicine_id'");

    $data=mysqli_fetch_assoc($medicine);

    $current_stock=$data['quantity'];

    if($quantity_sold > $current_stock){
        $error = "Stock haitoshi! Idadi iliyopo: " . $current_stock;
    } else {
        $new_stock=$current_stock-$quantity_sold;
        $total_amount=$data['selling_price']*$quantity_sold;

        mysqli_query($conn,"
        INSERT INTO sales
        (medicine_id,quantity_sold,total_amount,seller_id)
        VALUES
        ('$medicine_id','$quantity_sold','$total_amount','$seller_id')
        ");

        mysqli_query($conn,"
        UPDATE medicines
        SET quantity='$new_stock'
        WHERE medicine_id='$medicine_id'
        ");

        $success = "Mauzo yameerekodiwa kwa mafanikio! Jumla: TZS " . number_format($total_amount, 2);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>POS Sales - Seller</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Seller Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="seller_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="seller_sales.php" class="active"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
                <li><a href="seller_medicines.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="seller_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo Mtandaoni</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>💰 Point of Sale</h2>
            <div class="user-info">
                <span>Karibu, <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Chagua Dawa</label>
                    <select name="medicine_id" required>
                        <option value="">-- Chagua dawa --</option>
                        <?php
                        mysqli_data_seek($medicines, 0);
                        while($row=mysqli_fetch_assoc($medicines)){
                        ?>
                        <option value="<?php echo $row['medicine_id']; ?>">
                            <?php echo $row['medicine_name']; ?> (Stock: <?php echo $row['quantity']; ?>) - TZS <?php echo number_format($row['selling_price'], 2); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Idadi ya Kuuza</label>
                    <input type="number" name="quantity_sold" placeholder="Ingiza idadi" required min="1">
                </div>

                <button type="submit" name="sell" class="btn btn-success">💰 Euza</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>