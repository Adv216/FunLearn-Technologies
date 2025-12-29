<?php
include 'db_connect.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Finished_Products_Inventory.xls");

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$where = "1";

if($search != ''){
    $s = "%$search%";
    $where .= " AND (p.Product_Name LIKE '$s' OR p.Item_Code LIKE '$s')";
}

if($filter == 'in'){
    $where .= " AND p.Quantity > 0";
}
if($filter == 'out'){
    $where .= " AND p.Quantity = 0";
}

$sql = "
SELECT p.Item_Code, p.Product_Name, c.Category_Name, p.Quantity, p.Price
FROM FINISHED_PRODUCTS p
JOIN PRODUCT_CATEGORIES c ON p.Category_ID = c.Category_ID
WHERE $where
ORDER BY p.Product_ID DESC
";

$result = $conn->query($sql);

echo "Item Code\tProduct Name\tCategory\tQuantity\tPrice\n";

while($row = $result->fetch_assoc()){
    echo "{$row['Item_Code']}\t{$row['Product_Name']}\t{$row['Category_Name']}\t{$row['Quantity']}\t{$row['Price']}\n";
}
