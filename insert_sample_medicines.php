<?php
include("config.php");

echo "<h3>Inserting Sample Medicines for Testing</h3>";

// Sample medicines data
$medicines = [
    [
        'medicine_name' => 'Paracetamol 500mg',
        'category' => 'Pain Relief',
        'description' => 'Pain reliever and fever reducer',
        'buying_price' => 200,
        'selling_price' => 500,
        'quantity' => 100,
        'expiry_date' => '2027-12-31',
        'reorder_point' => 20
    ],
    [
        'medicine_name' => 'Amoxicillin 250mg',
        'category' => 'Antibiotics',
        'description' => 'Antibiotic for bacterial infections',
        'buying_price' => 500,
        'selling_price' => 1200,
        'quantity' => 50,
        'expiry_date' => '2027-06-30',
        'reorder_point' => 15
    ],
    [
        'medicine_name' => 'Ibuprofen 400mg',
        'category' => 'Pain Relief',
        'description' => 'Anti-inflammatory pain reliever',
        'buying_price' => 300,
        'selling_price' => 800,
        'quantity' => 75,
        'expiry_date' => '2027-09-15',
        'reorder_point' => 20
    ],
    [
        'medicine_name' => 'Cough Syrup 100ml',
        'category' => 'Cough & Cold',
        'description' => 'Cough suppressant syrup',
        'buying_price' => 400,
        'selling_price' => 1000,
        'quantity' => 60,
        'expiry_date' => '2027-08-20',
        'reorder_point' => 15
    ],
    [
        'medicine_name' => 'Vitamin C 500mg',
        'category' => 'Vitamins',
        'description' => 'Vitamin C supplement',
        'buying_price' => 150,
        'selling_price' => 400,
        'quantity' => 120,
        'expiry_date' => '2028-01-15',
        'reorder_point' => 30
    ],
    [
        'medicine_name' => 'Antacid Tablets',
        'category' => 'Digestive',
        'description' => 'Relief from heartburn and indigestion',
        'buying_price' => 250,
        'selling_price' => 600,
        'quantity' => 80,
        'expiry_date' => '2027-11-30',
        'reorder_point' => 20
    ],
    [
        'medicine_name' => 'Aspirin 100mg',
        'category' => 'Pain Relief',
        'description' => 'Blood thinner and pain reliever',
        'buying_price' => 180,
        'selling_price' => 450,
        'quantity' => 90,
        'expiry_date' => '2027-10-25',
        'reorder_point' => 25
    ],
    [
        'medicine_name' => 'Allergy Tablets',
        'category' => 'Allergy',
        'description' => 'Antihistamine for allergy relief',
        'buying_price' => 350,
        'selling_price' => 900,
        'quantity' => 55,
        'expiry_date' => '2027-07-15',
        'reorder_point' => 15
    ],
    [
        'medicine_name' => 'Multivitamins',
        'category' => 'Vitamins',
        'description' => 'Daily multivitamin supplement',
        'buying_price' => 450,
        'selling_price' => 1100,
        'quantity' => 70,
        'expiry_date' => '2028-02-28',
        'reorder_point' => 20
    ],
    [
        'medicine_name' => 'Eye Drops',
        'category' => 'Eye Care',
        'description' => 'Lubricating eye drops',
        'buying_price' => 300,
        'selling_price' => 750,
        'quantity' => 40,
        'expiry_date' => '2027-05-30',
        'reorder_point' => 10
    ],
    [
        'medicine_name' => 'Bandage Pack',
        'category' => 'First Aid',
        'description' => 'Adhesive bandages for cuts',
        'buying_price' => 100,
        'selling_price' => 300,
        'quantity' => 150,
        'expiry_date' => '2028-06-30',
        'reorder_point' => 40
    ],
    [
        'medicine_name' => 'Thermometer',
        'category' => 'Medical Equipment',
        'description' => 'Digital thermometer',
        'buying_price' => 2000,
        'selling_price' => 3500,
        'quantity' => 25,
        'expiry_date' => '2029-01-01',
        'reorder_point' => 10
    ],
    [
        'medicine_name' => 'Blood Pressure Monitor',
        'category' => 'Medical Equipment',
        'description' => 'Digital blood pressure monitor',
        'buying_price' => 15000,
        'selling_price' => 25000,
        'quantity' => 10,
        'expiry_date' => '2029-06-30',
        'reorder_point' => 5
    ],
    [
        'medicine_name' => 'Diabetes Test Strips',
        'category' => 'Diabetes Care',
        'description' => 'Blood glucose test strips',
        'buying_price' => 500,
        'selling_price' => 1200,
        'quantity' => 200,
        'expiry_date' => '2027-04-15',
        'reorder_point' => 50
    ],
    [
        'medicine_name' => 'Insulin Pen',
        'category' => 'Diabetes Care',
        'description' => 'Insulin delivery device',
        'buying_price' => 5000,
        'selling_price' => 8000,
        'quantity' => 30,
        'expiry_date' => '2027-03-31',
        'reorder_point' => 10
    ]
];

$inserted_count = 0;
$error_count = 0;

foreach($medicines as $med){
    $sql = "INSERT INTO medicines (medicine_name, category, description, buying_price, selling_price, quantity, expiry_date, reorder_point)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssddiisi", 
        $med['medicine_name'], 
        $med['category'], 
        $med['description'], 
        $med['buying_price'], 
        $med['selling_price'], 
        $med['quantity'], 
        $med['expiry_date'],
        $med['reorder_point']
    );
    
    if(mysqli_stmt_execute($stmt)){
        $inserted_count++;
        echo "✅ Inserted: " . $med['medicine_name'] . " - TZS " . number_format($med['selling_price'], 2) . "<br>";
    } else {
        $error_count++;
        echo "❌ Error inserting " . $med['medicine_name'] . ": " . mysqli_error($conn) . "<br>";
    }
}

echo "<br><strong>Summary:</strong><br>";
echo "Inserted: $inserted_count medicines<br>";
echo "Errors: $error_count<br>";
echo "<br><a href='medicine.php'>View Medicines</a>";

mysqli_close($conn);
?>
