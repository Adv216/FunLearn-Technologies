<?php
include 'secure_page_template.php';
include 'db_connect.php';
check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$products = $conn->query("SELECT * FROM FINISHED_PRODUCTS");

if(isset($_POST['save_invoice'])){

    $customer = $_POST['customer'];
    $date = date('Y-m-d');

    $conn->query("INSERT INTO INVOICES (Customer_Name, Invoice_Date, Grand_Total) VALUES ('$customer','$date',0)");
    $invoice_id = $conn->insert_id;

    $grand = 0;
    $productionAlerts = [];

    foreach($_POST['product'] as $i => $pid){

        $qty   = $_POST['qty'][$i];
        $price = $_POST['price'][$i];
        $total = $qty * $price;
        $grand += $total;

        $conn->query("INSERT INTO INVOICE_ITEMS (Invoice_ID, Product_ID, Quantity, Price, Total)
                      VALUES ($invoice_id,$pid,$qty,$price,$total)");

        $p = $conn->query("SELECT Product_Name, Quantity FROM FINISHED_PRODUCTS WHERE Product_ID=$pid")->fetch_assoc();

        if($p['Quantity'] >= $qty){
            $conn->query("UPDATE FINISHED_PRODUCTS SET Quantity = Quantity - $qty WHERE Product_ID=$pid");
        }
        else {
            $shortage = $qty - $p['Quantity'];
            $conn->query("UPDATE FINISHED_PRODUCTS SET Quantity = 0 WHERE Product_ID=$pid");

            $conn->query("INSERT INTO production_requirements (Order_ID, Product_ID, Required_Qty)
                          VALUES ($invoice_id, $pid, $shortage)");

            $productionAlerts[] = $p['Product_Name']." → Produce $shortage units";
        }
    }

    $conn->query("UPDATE INVOICES SET Grand_Total=$grand WHERE Invoice_ID=$invoice_id");

    $_SESSION['production_alerts'] = $productionAlerts;
    header("Location: invoice_success.php?id=$invoice_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>New Order</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background: linear-gradient(135deg,#4f9cf9,#2c5282);
  padding: 40px;
}
.card {
  border-radius: 15px;
  box-shadow: 0 15px 30px rgba(0,0,0,.2);
}
.header {
  font-size: 26px;
  font-weight: 800;
  color:#2c5282;
}
.btn-add { background:#10b981;color:white; }
.btn-save { background:#ef4444;color:white;font-size:18px;font-weight:700; }
.table thead { background:#2c5282;color:white; }
.total-box {
  background:#2c5282;color:white;
  padding:20px;border-radius:10px;
  font-size:24px;font-weight:800;
}
</style>
</head>

<body>

<div class="container">
<div class="card p-4">

<div class="header mb-3">🧾 Create New Order</div>

<form method="POST" id="invoiceForm">

<div class="row mb-3">
<div class="col-md-6">
<label>Customer Name</label>
<input type="text" name="customer" class="form-control" required>
</div>
<div class="col-md-6">
<label>Date</label>
<input type="text" class="form-control" value="<?= date('d M Y') ?>" readonly>
</div>
</div>

<table class="table">
<thead>
<tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr>
</thead>
<tbody id="rows"></tbody>
</table>

<button type="button" class="btn btn-add mb-3" onclick="addRow()">➕ Add Item</button>

<div class="total-box text-end">
Grand Total: ₹ <span id="grand">0.00</span>
</div>

<button class="btn btn-save w-100 mt-3" name="save_invoice">💾 Save Order</button>

</form>

</div>
</div>

<script>
let products = <?= json_encode($products->fetch_all(MYSQLI_ASSOC)) ?>;

function addRow(){
  let tr = document.createElement('tr');

  let productSelect = `<select class='form-select' name='product[]' onchange='setPrice(this)'>
    <option value=''>Select</option>`;
  products.forEach(p=>{
    productSelect+=`<option value='${p.Product_ID}' data-price='${p.Price}' data-stock='${p.Quantity}'>
    ${p.Product_Name} (Stock: ${p.Quantity})
    </option>`;
  });
  productSelect+='</select>';

  tr.innerHTML=`
  <td>${productSelect}</td>
  <td><input type="number" name="qty[]" class="form-control" value="1" min="1" oninput="calc()"></td>
  <td><input type="number" name="price[]" class="form-control" step="0.01" oninput="calc()"></td>
  <td class="fw-bold">0.00</td>
  <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove();calc()">✖</button></td>
  `;
  document.getElementById('rows').appendChild(tr);
}

function setPrice(sel){
  let row = sel.closest('tr');
  let price = sel.options[sel.selectedIndex].dataset.price;
  row.children[2].children[0].value = price;
  calc();
}

function calc(){
  let grand = 0;
  document.querySelectorAll('#rows tr').forEach(r=>{
    let q = r.children[1].children[0].value;
    let p = r.children[2].children[0].value;
    let t = q*p;
    r.children[3].innerText = t.toFixed(2);
    grand += t;
  });
  document.getElementById('grand').innerText = grand.toFixed(2);
}
</script>

</body>
</html>
