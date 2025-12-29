<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');

$sql = "
SELECT
    e.Employee_Name,
    e.Hourly_Rate,
    COUNT(DISTINCT a.Attendance_Date) AS Days_Worked,
    SUM(a.Working_Hours) AS Total_Working_Hours,
    ROUND(SUM(a.Working_Hours) * e.Hourly_Rate, 2) AS Salary
FROM EMPLOYEE_ATTENDANCE a
JOIN EMPLOYEES e ON a.Employee_ID = e.Employee_ID
WHERE
    MONTH(a.Attendance_Date) = ?
    AND YEAR(a.Attendance_Date) = ?
GROUP BY e.Employee_ID
ORDER BY e.Employee_Name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

// Calculate totals
$total_salary = 0;
$total_hours = 0;
$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
    $total_salary += $row['Salary'];
    $total_hours += $row['Total_Working_Hours'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --info: #06b6d4;
            --dark: #1e293b;
            --gray: #64748b;
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.3), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .navbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.6rem 1.2rem !important;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .container {
            position: relative;
            z-index: 1;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 2rem;
            padding: 2.5rem;
            margin: 2rem 0;
            box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header h3 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1.1rem;
            margin: 0;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            padding: 1.75rem;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease backwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.3);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-top: 0.5rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.98);
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.6s ease 0.5s backwards;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn {
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #4338ca);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--gray), #475569);
            box-shadow: 0 4px 15px rgba(100, 116, 139, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(100, 116, 139, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .table thead {
            background: linear-gradient(135deg, var(--dark), #334155);
            color: white;
        }

        .table thead th {
            padding: 1rem;
            font-weight: 600;
            border: none;
        }

        .table thead th:first-child {
            border-top-left-radius: 0.75rem;
        }

        .table thead th:last-child {
            border-top-right-radius: 0.75rem;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(99, 102, 241, 0.05);
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .table tfoot {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            font-weight: 700;
        }

        .table tfoot td {
            padding: 1.25rem 1rem;
            border-top: 3px solid var(--primary);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-chart-line me-2"></i>Billing System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="invoice_form.php">
                        <i class="fas fa-file-invoice me-2"></i>New Invoice
                    </a>
                </li>
                <?php if (check_permission([ROLE_ADMIN, ROLE_MANAGER])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="purchase_form.php">
                        <i class="fas fa-truck-loading me-2"></i>New Purchase
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="view_inventory.php">
                        <i class="fas fa-boxes me-2"></i>Inventory
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item me-3 d-flex align-items-center">
                    <span class="navbar-text text-white-50">
                        <i class="fas fa-user-circle me-1"></i> 
                        <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">

    <div class="page-header">
        <h3>
            <i class="fas fa-money-check-alt"></i>
            <span>Salary Report</span>
        </h3>
        <p class="page-subtitle">
            <i class="fas fa-calendar-alt me-2"></i>
            Viewing: <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?>
        </p>
    </div>

    <?php if (count($employees) > 0): ?>
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.1)); color: var(--primary);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-label">Total Employees</div>
            <div class="stat-value" style="color: var(--primary);"><?= count($employees) ?></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1)); color: var(--success);">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-label">Total Salary</div>
            <div class="stat-value" style="color: var(--success);">₹<?= number_format($total_salary, 2) ?></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(6, 182, 212, 0.1)); color: var(--info);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-label">Total Hours</div>
            <div class="stat-value" style="color: var(--info);"><?= number_format($total_hours, 2) ?></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1)); color: #f59e0b;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-label">Avg. Salary</div>
            <div class="stat-value" style="color: #f59e0b;">₹<?= number_format($total_salary / count($employees), 2) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FILTER FORM -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-calendar me-2"></i>Month
                    </label>
                    <select name="month" class="form-select">
                        <?php
                        for ($m = 1; $m <= 12; $m++) {
                            $selected = ($m == $month) ? 'selected' : '';
                            echo "<option value='$m' $selected>" . date('F', mktime(0,0,0,$m,1)) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt me-2"></i>Year
                    </label>
                    <select name="year" class="form-select">
                        <?php
                        for ($y = date('Y'); $y >= date('Y') - 5; $y--) {
                            $selected = ($y == $year) ? 'selected' : '';
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SALARY TABLE -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>Detailed Salary Breakdown
                </h5>
                <a href="salary_export.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user me-2"></i>Employee</th>
                            <th class="text-center"><i class="fas fa-calendar-check me-2"></i>Days Worked</th>
                            <th class="text-end"><i class="fas fa-clock me-2"></i>Total Hours</th>
                            <th class="text-end"><i class="fas fa-rupee-sign me-2"></i>Hourly Rate</th>
                            <th class="text-end"><i class="fas fa-money-bill-wave me-2"></i>Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($employees) > 0): ?>
                        <?php foreach ($employees as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['Employee_Name']) ?></strong></td>
                                <td class="text-center"><?= $row['Days_Worked'] ?> days</td>
                                <td class="text-end"><?= number_format($row['Total_Working_Hours'], 2) ?> hrs</td>
                                <td class="text-end">₹<?= number_format($row['Hourly_Rate'], 2) ?>/hr</td>
                                <td class="text-end"><strong style="color: var(--success);">₹<?= number_format($row['Salary'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No salary data found for the selected period.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                    <?php if (count($employees) > 0): ?>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end">TOTAL PAYROLL:</td>
                            <td class="text-end"><strong style="font-size: 1.25rem; color: var(--success);">₹<?= number_format($total_salary, 2) ?></strong></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center mb-5">
        <a href="attendance_view.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Attendance
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>