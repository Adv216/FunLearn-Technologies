<?php
include 'db_connect.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid invoice request.");
}
$id = $_GET['id'];

$inv = $conn->query("SELECT * FROM INVOICES WHERE Invoice_ID=$id")->fetch_assoc();
$items = $conn->query("SELECT i.*, p.Product_Name FROM INVOICE_ITEMS i JOIN FINISHED_PRODUCTS p ON i.Product_ID=p.Product_ID WHERE Invoice_ID=$id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FunLearn - Invoice #<?= $id ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
  background: linear-gradient(135deg, #5B9BD5 0%, #2B4C7E 100%);
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  padding: 2rem 0;
}

.invoice-container {
  max-width: 900px;
  margin: 0 auto;
}

.invoice-card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.invoice-header {
  background: linear-gradient(135deg, #2B4C7E, #5B9BD5);
  color: white;
  padding: 2.5rem;
  position: relative;
}

.invoice-header::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: #ED1C24;
}

.company-name {
  font-size: 2.5rem;
  font-weight: 800;
  color: #fbbf24;
  margin-bottom: 0.5rem;
}

.company-tagline {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 0;
}

.invoice-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin-top: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.invoice-number {
  background: #ED1C24;
  padding: 0.5rem 1.5rem;
  border-radius: 50px;
  font-weight: 800;
}

.invoice-body {
  padding: 2.5rem;
}

.info-section {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  border-left: 4px solid #5B9BD5;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.info-row:last-child {
  margin-bottom: 0;
}

.info-label {
  font-weight: 600;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-value {
  font-weight: 700;
  color: #2B4C7E;
  font-size: 1.1rem;
}

.section-title {
  color: #2B4C7E;
  font-weight: 700;
  font-size: 1.2rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 3px solid #ED1C24;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.items-table {
  margin-bottom: 2rem;
}

.items-table thead {
  background: linear-gradient(135deg, #2B4C7E, #5B9BD5);
  color: white;
}

.items-table thead th {
  padding: 1rem;
  font-weight: 600;
  border: none;
  text-align: center;
}

.items-table thead th:first-child {
  text-align: left;
  border-top-left-radius: 10px;
}

.items-table thead th:last-child {
  border-top-right-radius: 10px;
}

.items-table tbody td {
  padding: 1rem;
  vertical-align: middle;
  border-bottom: 1px solid #e2e8f0;
  text-align: center;
}

.items-table tbody td:first-child {
  text-align: left;
  font-weight: 600;
  color: #2B4C7E;
}

.items-table tbody tr:last-child td {
  border-bottom: none;
}

.items-table tbody tr:hover {
  background: #f8fafc;
}

.total-section {
  background: linear-gradient(135deg, #2B4C7E, #5B9BD5);
  color: white;
  padding: 2rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  text-align: right;
}

.total-label {
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.total-amount {
  font-size: 2.5rem;
  font-weight: 800;
  color: #fbbf24;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-back {
  background: white;
  border: 2px solid #2B4C7E;
  color: #2B4C7E;
  padding: 0.75rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-back:hover {
  background: #2B4C7E;
  color: white;
  transform: translateY(-2px);
}

.btn-print {
  background: linear-gradient(135deg, #10b981, #059669);
  border: none;
  color: white;
  padding: 0.75rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-print:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  color: white;
}

.invoice-footer {
  background: #f8fafc;
  padding: 1.5rem 2.5rem;
  text-align: center;
  color: #64748b;
  font-size: 0.9rem;
  border-top: 1px solid #e2e8f0;
}

@media print {
  body {
    background: white;
    padding: 0;
  }
  
  .action-buttons {
    display: none;
  }
  
  .invoice-card {
    box-shadow: none;
  }
}

@media (max-width: 768px) {
  .invoice-header {
    padding: 1.5rem;
  }
  
  .company-name {
    font-size: 1.8rem;
  }
  
  .invoice-body {
    padding: 1.5rem;
  }
  
  .total-amount {
    font-size: 2rem;
  }
  
  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
  
  .items-table thead th,
  .items-table tbody td {
    padding: 0.5rem;
    font-size: 0.9rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn-back, .btn-print {
    width: 100%;
    justify-content: center;
  }
}
</style>
</head>

<body>

<div class="invoice-container">

<div class="invoice-card">

<!-- Invoice Header -->
<div class="invoice-header">
<div class="company-name">FunLearn</div>
<div class="company-tagline">Teaching Learning Resources</div>
<div class="invoice-title">
<i class="fas fa-file-invoice"></i>
<span>INVOICE</span>
<span class="invoice-number">#<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?></span>
</div>
</div>

<!-- Invoice Body -->
<div class="invoice-body">

<!-- Customer & Date Info -->
<div class="info-section">
<div class="info-row">
<div class="info-label">
<i class="fas fa-user"></i>
Customer Name
</div>
<div class="info-value"><?= htmlspecialchars($inv['Customer_Name']) ?></div>
</div>
<div class="info-row">
<div class="info-label">
<i class="fas fa-calendar-alt"></i>
Invoice Date
</div>
<div class="info-value"><?= date('d M Y', strtotime($inv['Invoice_Date'])) ?></div>
</div>
</div>

<!-- Items Section -->
<div class="section-title">
<i class="fas fa-list"></i>
Invoice Items
</div>

<table class="table items-table">
<thead>
<tr>
<th>Product Name</th>
<th>Quantity</th>
<th>Price (₹)</th>
<th>Total (₹)</th>
</tr>
</thead>
<tbody>
<?php 
$itemCount = 0;
while($r = $items->fetch_assoc()): 
$itemCount++;
?>
<tr>
<td><?= htmlspecialchars($r['Product_Name']) ?></td>
<td><?= number_format($r['Quantity']) ?></td>
<td><?= number_format($r['Price'], 2) ?></td>
<td><strong><?= number_format($r['Total'], 2) ?></strong></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<!-- Total Section -->
<div class="total-section">
<div class="total-label">Grand Total</div>
<div class="total-amount">₹ <?= number_format($inv['Grand_Total'], 2) ?></div>
</div>

<!-- Action Buttons -->
<div class="action-buttons">
<a href="index.php" class="btn-back">
<i class="fas fa-arrow-left"></i>
Back to Dashboard
</a>
<button onclick="window.print()" class="btn-print">
<i class="fas fa-print"></i>
Print Invoice
</button>
</div>

</div>

<!-- Invoice Footer -->
<div class="invoice-footer">
<p class="mb-0">
<strong>Thank you for your business!</strong><br>
For any queries, please contact FunLearn Teaching Learning Resources
</p>
</div>

</div>

</div>

</body>
</html>
