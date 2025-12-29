<?php
include 'secure_page_template.php';
include 'db_connect.php';

$data = $conn->query("
    SELECT c.Category_Name, SUM(p.Quantity) AS total
    FROM FINISHED_PRODUCTS p
    JOIN CATEGORIES c ON p.Category_ID = c.Category_ID
    GROUP BY c.Category_Name
");

$labels = [];
$values = [];

while($r=$data->fetch_assoc()){
    $labels[] = $r['Category_Name'];
    $values[] = $r['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Category Stock Chart</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<div class="container my-5">
<h3>📊 Category-Wise Stock</h3>

<canvas id="chart"></canvas>

<a href="index.php" class="btn btn-secondary mt-4">Back</a>
</div>

<script>
new Chart(document.getElementById('chart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Total Stock',
            data: <?= json_encode($values) ?>,
            backgroundColor: '#6366f1'
        }]
    }
});
</script>
</body>
</html>
