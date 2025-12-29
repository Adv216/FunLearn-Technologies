<?php
include 'secure_page_template.php';
include 'db_connect.php';
check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$month = date('m');
$year = date('Y');

/* ===== SAVE COST SHEET ===== */
if(isset($_POST['save_sheet'])){
    $book = $_POST['book_name'];
    $grand = $_POST['grand_total'];

    $conn->query("INSERT INTO BOOK_COST_SHEETS (Book_Name, Month, Year, Grand_Total)
                  VALUES ('$book',$month,$year,$grand)");

    $sheet_id = $conn->insert_id;

    foreach($_POST['item'] as $i=>$name){
        $cat = $_POST['category'][$i];
        $cost = $_POST['cost'][$i];
        $qty  = $_POST['qty'][$i];
        $amt  = $_POST['amount'][$i];

        if($amt>0){
            $conn->query("INSERT INTO BOOK_COST_ITEMS
            (Sheet_ID,Category,Equipment,Cost,Quantity,Amount)
            VALUES ($sheet_id,'$cat','$name',$cost,$qty,$amt)");
        }
    }

    header("Location: book_cost_dashboard.php");
    exit;
}

$sheets = $conn->query("SELECT * FROM BOOK_COST_SHEETS ORDER BY Sheet_ID DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Book Cost Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

body {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #e5e7eb;
    min-height: 100vh;
    padding-bottom: 3rem;
}

.container {
    max-width: 1400px;
}

.page-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
}

.page-header h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-header p {
    margin: 0.5rem 0 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.book-name-input {
    background: #1e293b !important;
    border: 2px solid #334155 !important;
    color: white !important;
    border-radius: 12px !important;
    padding: 1rem !important;
    font-size: 1.1rem !important;
    font-weight: 600 !important;
    transition: all 0.3s ease;
}

.book-name-input:focus {
    background: #0f172a !important;
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2) !important;
}

.book-name-input::placeholder {
    color: #64748b;
}

.card-box {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #334155;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.card-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    border-color: #475569;
}

.card-box > b {
    font-size: 1.1rem;
    font-weight: 700;
    color: #a5b4fc;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-box hr {
    border-color: #334155;
    margin: 1rem 0;
    opacity: 0.5;
}

.row-item {
    display: grid;
    grid-template-columns: 1fr 100px 100px 110px;
    gap: 10px;
    margin-bottom: 8px;
    align-items: center;
}

.input-label {
    font-size: 0.75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: 4px;
}

.row-item > div:first-child {
    font-size: 0.9rem;
    color: #cbd5e1;
    font-weight: 500;
}

input[type="number"] {
    background: #020617;
    border: 2px solid #1e293b;
    color: white;
    border-radius: 8px;
    padding: 8px;
    text-align: center;
    font-weight: 600;
    transition: all 0.2s ease;
}

input[type="number"]:focus {
    outline: none;
    border-color: #6366f1;
    background: #0f172a;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

input[type="number"]::placeholder {
    color: #475569;
}

.amount-box {
    background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
    border-radius: 8px;
    padding: 8px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
}

.subtotal {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    text-align: right;
    margin-top: 1rem;
    font-weight: 700;
    font-size: 1.05rem;
    box-shadow: 0 2px 10px rgba(5, 150, 105, 0.3);
}

.grand {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    font-size: 1.8rem;
    text-align: right;
    font-weight: 800;
    box-shadow: 0 8px 30px rgba(34, 197, 94, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.btn-save {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(99, 102, 241, 0.6);
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}

.section-divider {
    border: none;
    height: 2px;
    background: linear-gradient(90deg, transparent, #334155, transparent);
    margin: 3rem 0 2rem;
}

.history-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #a5b4fc;
    margin-bottom: 1.5rem;
}

.table-dark {
    background: #1e293b;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.table-dark thead {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
}

.table-dark th {
    padding: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    color: #a5b4fc;
    border: none;
}

.table-dark td {
    padding: 1rem;
    font-weight: 500;
    border-color: #334155;
}

.table-dark tbody tr {
    transition: all 0.2s ease;
}

.table-dark tbody tr:hover {
    background: #0f172a;
    transform: scale(1.01);
}

@media (max-width: 992px) {
    .row-item {
        grid-template-columns: 1fr 90px 90px 100px;
        gap: 8px;
    }
    
    .input-label {
        font-size: 0.7rem;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 1.5rem;
    }
    
    .page-header h2 {
        font-size: 1.5rem;
    }
    
    .grand {
        font-size: 1.4rem;
        padding: 1rem;
    }
}

/* Smooth fade-in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card-box {
    animation: fadeIn 0.4s ease-out;
}

.card-box:nth-child(1) { animation-delay: 0.05s; }
.card-box:nth-child(2) { animation-delay: 0.1s; }
.card-box:nth-child(3) { animation-delay: 0.15s; }
</style>
</head>

<body>
<div class="container py-4">

<div class="page-header">
    <h2>📘 Book Cost Sheet</h2>
    <p>Create detailed cost analysis for book production</p>
</div>

<form method="POST">

<div class="mb-4">
    <input name="book_name" class="form-control book-name-input" placeholder="Enter Book Name" required>
</div>

<div class="row">
<?php
$sections=[
"Page Type"=>["Ivory Photo Paper","Photo Paper 210","Photo Paper 180"],
"Print Cost"=>["Heavy","Heavy Double Side","Normal"],
"Electricity"=>["Printing Electricity"],
"Lamination"=>["Lamination Blue","Lamination Red"],
"Cutting"=>["Cameo Cutting"],
"Velcro"=>["Velcros Used"],
"Labour"=>["Labour Cost"],
"Binding"=>["Binding Small","Binding Big"],
"Packaging"=>["Button Folder A3","Carry Bag Plastic","Carry Bag Cloth","Duster","Pen"]
];

foreach($sections as $cat=>$items){
echo "<div class='col-lg-4'><div class='card-box'><b>$cat</b><hr>";

// Add column headers for each card
echo "<div class='row-item' style='margin-bottom:12px'>
<div></div>
<div class='input-label'>Price (₹)</div>
<div class='input-label'>Quantity</div>
<div class='input-label'>Total (₹)</div>
</div>";

foreach($items as $it){
echo "
<div class='row-item'>
<div>$it</div>
<input name='cost[]' class='cost' type='number' step='0.01' placeholder='0.00'>
<input name='qty[]' class='qty' type='number' placeholder='0'>
<div class='amount-box'>0.00</div>
<input type='hidden' name='item[]' value='$it'>
<input type='hidden' name='category[]' value='$cat'>
<input type='hidden' name='amount[]' class='amount'>
</div>";
}
echo "<div class='subtotal'>Subtotal: ₹ <span>0.00</span></div></div></div>";
}
?>
</div>

<div class="grand mt-4">Grand Total: ₹ <span id="grand">0.00</span></div>
<input type="hidden" name="grand_total" id="grand_input">

<button class="btn btn-save mt-4 w-100" name="save_sheet">💾 Save Cost Sheet</button>
</form>

<hr class="section-divider">

<h4 class="history-header">📂 Previous Cost Sheets</h4>
<table class="table table-dark table-striped">
<thead>
<tr>
    <th>Book Name</th>
    <th>Month</th>
    <th>Year</th>
    <th>Total Cost</th>
</tr>
</thead>
<tbody>
<?php while($s=$sheets->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($s['Book_Name']) ?></td>
<td><?= $s['Month'] ?></td>
<td><?= $s['Year'] ?></td>
<td><strong>₹<?= number_format($s['Grand_Total'], 2) ?></strong></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>

<script>
function calc(){
 let grand=0;
 document.querySelectorAll('.card-box').forEach(card=>{
   let sub=0;
   card.querySelectorAll('.row-item').forEach(r=>{
     let c=r.querySelector('.cost').value||0;
     let q=r.querySelector('.qty').value||0;
     let t=c*q;
     r.querySelector('.amount-box').innerText=t.toFixed(2);
     r.querySelector('.amount').value=t;
     sub+=t;
   });
   card.querySelector('.subtotal span').innerText=sub.toFixed(2);
   grand+=sub;
 });
 document.getElementById('grand').innerText=grand.toFixed(2);
 document.getElementById('grand_input').value=grand;
}
document.querySelectorAll('input').forEach(i=>i.addEventListener('input',calc));
</script>

</body>
</html>