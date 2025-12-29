<?php
include 'secure_page_template.php';
include 'db_connect.php';

if(isset($_POST['save_invoice'])){

    $customer = $_POST['customer'];
    $date = date('Y-m-d');
    $total = 0;

    $conn->query("INSERT INTO INVOICES(Customer_Name, Invoice_Date) VALUES('$customer','$date')");
    $order_id = $conn->insert_id;

    $production_alerts = [];

    foreach($_POST['product_id'] as $i => $pid){
        $qty = $_POST['qty'][$i];
        $price = $_POST['price'][$i];
        $line_total = $qty * $price;
        $total += $line_total;

        $conn->query("INSERT INTO INVOICE_ITEMS(Invoice_ID, Product_ID, Quantity, Price, Total)
                      VALUES($order_id, $pid, $qty, $price, $line_total)");

        // Check stock
        $stock = $conn->query("SELECT Quantity FROM FINISHED_PRODUCTS WHERE Product_ID=$pid")->fetch_assoc()['Quantity'];

        if($stock >= $qty){
            $conn->query("UPDATE FINISHED_PRODUCTS SET Quantity=Quantity-$qty WHERE Product_ID=$pid");
        } else {
            $shortage = $qty - $stock;
            $conn->query("UPDATE FINISHED_PRODUCTS SET Quantity=0 WHERE Product_ID=$pid");

            $conn->query("INSERT INTO production_requirements(Product_ID, Required_Qty, Order_ID)
                          VALUES($pid, $shortage, $order_id)");

            $name = $conn->query("SELECT Product_Name FROM FINISHED_PRODUCTS WHERE Product_ID=$pid")->fetch_assoc()['Product_Name'];
            $production_alerts[] = "$name → Produce $shortage units";
        }
    }

    $conn->query("UPDATE INVOICES SET Grand_Total=$total WHERE Invoice_ID=$order_id");

    $_SESSION['production_alerts'] = $production_alerts;
    header("Location: invoice_success.php?order_id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>New Order</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>New Order</h3>

<form method="POST">
<input class="form-control mb-2" name="customer" placeholder="Customer Name" required>

<table class="table">
<thead>
<tr><th>Product</th><th>Qty</th><th>Price</th></tr>
</thead>
<tbody>
<?php
$p = $conn->query("SELECT * FROM FINISHED_PRODUCTS");
while($r = $p->fetch_assoc()):
?>
<tr>
<td>
<input type="hidden" name="product_id[]" value="<?= $r['Product_ID'] ?>">
<?= $r['Product_Name'] ?>
</td>
<td><input type="number" name="qty[]" class="form-control" required></td>
<td><input type="number" name="price[]" value="<?= $r['Price'] ?>" class="form-control" required></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<button name="save_invoice" class="btn btn-success">Save Invoice</button>
</form>

</body>
</html>
