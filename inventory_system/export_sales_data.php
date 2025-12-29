<?php
// Note: This script is protected by Role-Based Access for security.
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: ADMIN ONLY FOR RAW DATA EXPORT ===
if (!check_permission([ROLE_ADMIN])) {
    die("Permission Denied. Only Administrators can export raw sales data.");
}
// ==========================

// 1. Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sales_history_export_' . date('Ymd_His') . '.csv');
$output = fopen('php://output', 'w');

// 2. Output CSV headers (Critical format for the ML model)
// The model needs: Date, Product_ID, Quantity_Sold
fputcsv($output, array('Date', 'Product_ID', 'Quantity_Sold'));

// 3. SQL Query to get daily sales quantity per product
$sql_export = "
    SELECT 
        DATE(I.Date) AS SaleDate,
        ID.Product_ID,
        SUM(ID.Quantity) AS QuantitySold
    FROM 
        INVOICE_DETAILS ID
    JOIN 
        INVOICE I ON ID.Invoice_ID = I.Invoice_ID
    GROUP BY
        SaleDate, ID.Product_ID
    ORDER BY
        SaleDate ASC, ID.Product_ID ASC";

$result = $conn->query($sql_export);

if ($result) {
    // 4. Output data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    // Handle query error in the CSV output
    fputcsv($output, array('ERROR', $conn->error, '0'));
}

fclose($output);
$conn->close();
exit;
?>