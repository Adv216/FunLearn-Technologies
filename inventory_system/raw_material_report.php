<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');

$query = "
SELECT 
    m.Material_Name,
    SUM(u.Used_Quantity) AS Total_Used
FROM RAW_MATERIAL_USAGE u
JOIN RAW_MATERIALS m ON u.Material_ID = m.Material_ID
WHERE MONTH(u.Usage_Date) = ?
AND YEAR(u.Usage_Date) = ?
GROUP BY u.Material_ID
ORDER BY m.Material_Name
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monthly Raw Material Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --secondary: #8b5cf6;
    --success: #10b981;
    --info: #06b6d4;
    --warning: #f59e0b;
    --danger: #ef4444;
    --light: #f8fafc;
    --dark: #1e293b;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    padding: 2rem 0;
}

.main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.page-header {
    text-align: center;
    color: white;
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.page-header p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.card-modern {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
    margin-bottom: 2rem;
    transition: transform 0.3s ease;
}

.card-header-modern {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: 1.5rem 2rem;
    border: none;
}

.card-header-modern h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-body-modern {
    padding: 2rem;
}

.filter-section {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.filter-section label {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.filter-section .form-select,
.filter-section .form-control {
    border: 2px solid #cbd5e1;
    border-radius: 10px;
    padding: 0.75rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.filter-section .form-select:focus,
.filter-section .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    outline: none;
}

.btn-generate {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    height: 100%;
}

.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
}

.report-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 12px;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.report-meta .period {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #92400e;
}

.report-meta .period i {
    font-size: 1.2rem;
}

.report-meta .date-display {
    font-size: 1.1rem;
    color: #78350f;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 2rem;
}

.report-table {
    width: 100%;
    margin: 0;
    background: white;
}

.report-table thead tr {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.report-table thead th {
    color: white;
    font-weight: 600;
    padding: 1rem 1.5rem;
    text-align: left;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
}

.report-table tbody tr {
    transition: all 0.2s ease;
}

.report-table tbody tr:hover {
    background: #f8fafc;
    transform: scale(1.01);
}

.report-table tbody td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.material-name {
    font-weight: 500;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.material-name i {
    color: var(--primary);
}

.quantity-cell {
    font-weight: 600;
    color: var(--info);
    font-size: 1.1rem;
}

.total-row {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: white !important;
    font-weight: 700;
    font-size: 1.2rem;
}

.total-row td {
    border: none !important;
    padding: 1.25rem 1.5rem !important;
}

.total-row:hover {
    transform: none !important;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    padding: 1.5rem;
    border-radius: 12px;
    border-left: 4px solid var(--info);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(6, 182, 212, 0.2);
}

.stat-card .stat-label {
    font-size: 0.85rem;
    color: #0369a1;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #0c4a6e;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-back {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(100, 116, 139, 0.4);
    color: white;
}

.btn-print {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-print:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(5, 150, 105, 0.4);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
}

.empty-state i {
    font-size: 5rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #475569;
}

.empty-state p {
    color: #94a3b8;
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 1.8rem;
        flex-direction: column;
    }
    
    .card-body-modern {
        padding: 1rem;
    }
    
    .report-meta {
        flex-direction: column;
        text-align: center;
    }
    
    .report-table thead th,
    .report-table tbody td {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-back,
    .btn-print {
        width: 100%;
    }
}

@media print {
    body {
        background: white;
    }
    
    .filter-section,
    .action-buttons,
    .page-header {
        display: none !important;
    }
    
    .card-modern {
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }
}
</style>
</head>

<body>
<div class="main-container">

<div class="page-header">
    <h1>
        <i class="fas fa-chart-bar"></i>
        Monthly Material Usage Report
    </h1>
    <p>Track and analyze raw material consumption</p>
</div>

<div class="card-modern">
    <div class="card-header-modern">
        <h3><i class="fas fa-filter"></i> Report Filters</h3>
    </div>
    <div class="card-body-modern">
        
        <div class="filter-section">
            <form class="row g-3" method="GET">
                <div class="col-md-5">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt"></i> Select Month
                    </label>
                    <select class="form-select" name="month">
                        <?php
                        for($m=1; $m<=12; $m++){
                            $sel = $m==$month ? "selected" : "";
                            echo "<option value='$m' $sel>".date("F", mktime(0,0,0,$m,1))."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">
                        <i class="fas fa-calendar"></i> Select Year
                    </label>
                    <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2000" max="2100">
                </div>

                <div class="col-md-2 d-grid align-items-end">
                    <button type="submit" class="btn-generate">
                        <i class="fas fa-sync-alt"></i> Generate
                    </button>
                </div>
            </form>
        </div>

        <div class="report-meta">
            <div class="period">
                <i class="fas fa-calendar-check"></i>
                <span>Report Period:</span>
            </div>
            <div class="date-display">
                <strong><?= date("F", mktime(0,0,0,$month,1)) . " " . $year ?></strong>
            </div>
        </div>

        <?php 
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        if(count($data) > 0): 
            $grandTotal = array_sum(array_column($data, 'Total_Used'));
            $itemCount = count($data);
        ?>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-label">
                    <i class="fas fa-boxes"></i> Total Items
                </div>
                <div class="stat-value"><?= $itemCount ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">
                    <i class="fas fa-calculator"></i> Total Usage
                </div>
                <div class="stat-value"><?= number_format($grandTotal, 2) ?></div>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="report-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> #</th>
                        <th><i class="fas fa-box"></i> Material Name</th>
                        <th><i class="fas fa-chart-line"></i> Total Quantity Used</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    foreach($data as $row): 
                    ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td>
                            <div class="material-name">
                                <i class="fas fa-cube"></i>
                                <?= htmlspecialchars($row['Material_Name']) ?>
                            </div>
                        </td>
                        <td class="quantity-cell"><?= number_format($row['Total_Used'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <tr class="total-row">
                        <td colspan="2"><i class="fas fa-calculator"></i> GRAND TOTAL</td>
                        <td><?= number_format($grandTotal, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h4>No Data Found</h4>
            <p>No raw material usage recorded for <?= date("F", mktime(0,0,0,$month,1)) . " " . $year ?></p>
            <p>Try selecting a different month or year.</p>
        </div>

        <?php endif; ?>

        <div class="action-buttons">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

    </div>
</div>

</div>

</body>
</html>
