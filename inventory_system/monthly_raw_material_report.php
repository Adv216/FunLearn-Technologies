<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');

$report = $conn->query("
    SELECT 
        r.Material_Name,
        SUM(u.Used_Quantity) AS Total_Used
    FROM RAW_MATERIAL_USAGE u
    JOIN RAW_MATERIALS r ON u.Material_ID = r.Material_ID
    WHERE MONTH(u.Usage_Date) = $month 
      AND YEAR(u.Usage_Date) = $year
    GROUP BY r.Material_ID
    ORDER BY r.Material_Name
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Monthly Raw Material Usage</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg,#667eea,#764ba2); min-height:100vh; }
.container { margin-top:40px; }
.card { border-radius:1.5rem; box-shadow:0 20px 50px rgba(0,0,0,.25); }
</style>
</head>

<body>
<div class="container">

<div class="card p-4 mb-4">
<h3>📦 Monthly Raw Material Usage</h3>

<form method="GET" class="row g-3 mt-2">
<div class="col-md-4">
<select name="month" class="form-select">
<?php for($m=1;$m<=12;$m++): ?>
<option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>>
<?= date("F", mktime(0,0,0,$m,1)) ?>
</option>
<?php endfor; ?>
</select>
</div>

<div class="col-md-4">
<input type="number" name="year" value="<?= $year ?>" class="form-control">
</div>

<div class="col-md-4 d-grid">
<button class="btn btn-success">Generate Report</button>
</div>
</form>
</div>

<div class="card p-4">
<h5>Usage Summary</h5>

<table class="table table-bordered mt-3">
<thead class="table-dark">
<tr>
<th>Material</th>
<th>Total Used</th>
</tr>
</thead>
<tbody>
<?php if($report->num_rows): ?>
<?php while($row=$report->fetch_assoc()): ?>
<tr>
<td><?= $row['Material_Name'] ?></td>
<td><?= $row['Total_Used'] ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="2" class="text-center">No data for this period</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="text-center mt-4">
<a href="index.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
</div>

</div>
</body>
</html>
