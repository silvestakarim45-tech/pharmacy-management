<?php
include("config.php");

echo "<h3>Updating Medicines Expiry Dates</h3>";

// Update all medicines to have expiry dates in 2027-2029
$update_query = "UPDATE medicines SET 
    expiry_date = CASE 
        WHEN medicine_name = 'Paracetamol 500mg' THEN '2027-12-31'
        WHEN medicine_name = 'Amoxicillin 250mg' THEN '2027-06-30'
        WHEN medicine_name = 'Ibuprofen 400mg' THEN '2027-09-15'
        WHEN medicine_name = 'Cough Syrup 100ml' THEN '2027-08-20'
        WHEN medicine_name = 'Vitamin C 500mg' THEN '2028-01-15'
        WHEN medicine_name = 'Antacid Tablets' THEN '2027-11-30'
        WHEN medicine_name = 'Aspirin 100mg' THEN '2027-10-25'
        WHEN medicine_name = 'Allergy Tablets' THEN '2027-07-15'
        WHEN medicine_name = 'Multivitamins' THEN '2028-02-28'
        WHEN medicine_name = 'Eye Drops' THEN '2027-05-30'
        WHEN medicine_name = 'Bandage Pack' THEN '2028-06-30'
        WHEN medicine_name = 'Thermometer' THEN '2029-01-01'
        WHEN medicine_name = 'Blood Pressure Monitor' THEN '2029-06-30'
        WHEN medicine_name = 'Diabetes Test Strips' THEN '2027-04-15'
        WHEN medicine_name = 'Insulin Pen' THEN '2027-03-31'
        ELSE expiry_date
    END";

if(mysqli_query($conn, $update_query)){
    $affected_rows = mysqli_affected_rows($conn);
    echo "✅ Updated $affected_rows medicines expiry dates to 2027-2029";
} else {
    echo "❌ Error updating expiry dates: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
