<!DOCTYPE html>
<html>
<head>
<title>Cost Sheet</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<script>
function calculateTotal(){
    let total = 0;
    document.querySelectorAll('.cost').forEach(e=>{
        total += parseFloat(e.value) || 0;
    });
    document.getElementById('total').innerText = total.toFixed(2);
    document.getElementById('totalInput').value = total.toFixed(2);
}
</script>
</head>

<body class="p-4">

<h3>Create Cost Sheet</h3>

<form method="POST" action="save_cost_sheet.php">
<input type="text" name="sheet_name" class="form-control mb-2" placeholder="Sheet Name" required>

<div class="row mb-2">
<div class="col"><input class="form-control cost" name="costs[]" oninput="calculateTotal()" placeholder="Material Cost"></div>
<div class="col"><input class="form-control cost" name="costs[]" oninput="calculateTotal()" placeholder="Labor Cost"></div>
<div class="col"><input class="form-control cost" name="costs[]" oninput="calculateTotal()" placeholder="Transport Cost"></div>
</div>

<h5>Total: ₹ <span id="total">0.00</span></h5>
<input type="hidden" name="total" id="totalInput">

<button class="btn btn-success mt-2">Save Sheet</button>
</form>

<hr>

<h4>Load Previous Sheets</h4>

<select class="form-select" onchange="location='cost_sheet.php?load='+this.value">
<option value="">Select Sheet</option>

<?php
include 'db_connect.php';
$sheets = $conn->query("SELECT * FROM cost_sheets ORDER BY created_at DESC");
while($s = $sheets->fetch_assoc()){
    echo "<option value='{$s['id']}'>{$s['sheet_name']} (₹{$s['total']})</option>";
}
?>
</select>

<?php
if(isset($_GET['load'])){
    $id = intval($_GET['load']);
    $data = $conn->query("SELECT * FROM cost_sheets WHERE id=$id")->fetch_assoc();
    $costs = json_decode($data['data'], true);
    echo "<script>
        document.querySelectorAll('.cost').forEach((e,i)=>{
            e.value = '".implode("','",$costs)."'.split(',')[i] || '';
        });
        calculateTotal();
    </script>";
}
?>

</body>
</html>
