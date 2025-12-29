<?php
include 'db_connect.php';

if(isset($_POST['save'])){
    $book = $_POST['book'];
    $grand = $_POST['grand'];

    $conn->query("INSERT INTO book_cost_sheets (Book_Name, Grand_Total) VALUES ('$book',$grand)");
    $sheet_id = $conn->insert_id;

    foreach($_POST['item'] as $i=>$item){
        $cat = $_POST['cat'][$i];
        $cost = $_POST['cost'][$i];
        $qty  = $_POST['qty'][$i];
        $amt  = $_POST['amount'][$i];

        if($amt>0){
            $conn->query("INSERT INTO book_cost_items (Sheet_ID,Category,Item,Cost,Qty,Amount)
                          VALUES ($sheet_id,'$cat','$item',$cost,$qty,$amt)");
        }
    }

    header("Location: book_cost_dashboard.php");
}
$history = $conn->query("SELECT * FROM book_cost_sheets ORDER BY Sheet_ID DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Book Cost Calculator</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#0f172a;color:white}
.card{background:#1e293b}
.total-box{font-size:2rem;font-weight:bold;color:#22c55e}
input{background:#020617;color:white}
</style>
</head>
<body>
<div class="container py-4">

<h2 class="mb-4">📚 Book Cost Dashboard</h2>

<form method="POST">
<input class="form-control mb-3" name="book" placeholder="Book Name" required>

<table class="table table-dark">
<thead>
<tr><th>Category</th><th>Item</th><th>Cost</th><th>Qty</th><th>Amount</th></tr>
</thead>
<tbody id="rows"></tbody>
</table>

<button type="button" class="btn btn-primary" onclick="addRow()">+ Add Item</button>

<div class="mt-4">Total: ₹ <span class="total-box" id="grand">0.00</span></div>

<input type="hidden" name="grand" id="grandInput">
<button class="btn btn-success mt-3" name="save">Save Cost Sheet</button>
</form>

<hr>

<h4>Previous Cost Sheets</h4>
<table class="table table-dark">
<tr><th>Book</th><th>Total</th><th>Date</th></tr>
<?php while($r=$history->fetch_assoc()): ?>
<tr><td><?= $r['Book_Name'] ?></td><td>₹ <?= $r['Grand_Total'] ?></td><td><?= $r['Created_At'] ?></td></tr>
<?php endwhile; ?>
</table>

</div>

<script>
function addRow(){
  let tr=document.createElement('tr');
  tr.innerHTML=`
  <td><input name="cat[]" class="form-control"></td>
  <td><input name="item[]" class="form-control"></td>
  <td><input name="cost[]" class="form-control" oninput="calc()"></td>
  <td><input name="qty[]" class="form-control" oninput="calc()"></td>
  <td><input name="amount[]" class="form-control" readonly></td>`;
  document.getElementById('rows').appendChild(tr);
}

function calc(){
  let rows=document.querySelectorAll('#rows tr');
  let grand=0;
  rows.forEach(r=>{
    let c=r.children[2].children[0].value||0;
    let q=r.children[3].children[0].value||0;
    let a=c*q;
    r.children[4].children[0].value=a.toFixed(2);
    grand+=a;
  });
  document.getElementById('grand').innerText=grand.toFixed(2);
  document.getElementById('grandInput').value=grand.toFixed(2);
}
</script>

</body>
</html>
