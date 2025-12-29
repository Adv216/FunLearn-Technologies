<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$month = date('m');
$year  = date('Y');

if (isset($_POST['generate'])) {
    $month = $_POST['month'];
    $year  = $_POST['year'];
}

$query = "
SELECT 
    e.Employee_Name,
    COUNT(a.Attendance_ID) AS days_worked,
    SUM(a.Working_Hours) AS total_hours,
    GROUP_CONCAT(DATE_FORMAT(a.Attendance_Date, '%d %b %Y') ORDER BY a.Attendance_Date SEPARATOR ', ') AS worked_dates
FROM employee_attendance a
JOIN employees e ON a.Employee_ID = e.Employee_ID
WHERE MONTH(a.Attendance_Date) = ?
  AND YEAR(a.Attendance_Date) = ?
GROUP BY e.Employee_ID
ORDER BY e.Employee_Name
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
    <title>Monthly Attendance Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary: #22c55e;
            --secondary-dark: #16a34a;
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
            background: linear-gradient(135deg, #0ea5e9 0%, #22c55e 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 2rem 0;
        }

        .main-container {
            max-width: 1400px;
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
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 1.5rem;
            border-radius: 15px;
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
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            outline: none;
        }

        .btn-generate {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
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
            box-shadow: 0 10px 25px rgba(34, 197, 94, 0.4);
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

        .report-meta .date-display {
            font-size: 1.1rem;
            color: #78350f;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.2);
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

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .attendance-table {
            width: 100%;
            margin: 0;
            background: white;
        }

        .attendance-table thead tr {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        .attendance-table thead th {
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .attendance-table tbody tr {
            transition: all 0.2s ease;
        }

        .attendance-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.005);
        }

        .attendance-table tbody td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .employee-name {
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .employee-name i {
            color: var(--primary);
            font-size: 1.2rem;
        }

        .days-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1rem;
        }

        .hours-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1rem;
        }

        .dates-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .date-badge {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .date-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        .date-badge i {
            font-size: 0.75rem;
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

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
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
            
            .attendance-table thead th,
            .attendance-table tbody td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .dates-container {
                max-height: 150px;
                overflow-y: auto;
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
            <i class="fas fa-user-clock"></i>
            Monthly Attendance Summary
        </h1>
        <p>Track employee attendance and working hours</p>
    </div>

    <div class="card-modern">
        <div class="card-header-modern">
            <h3><i class="fas fa-filter"></i> Report Filters</h3>
        </div>
        <div class="card-body-modern">
            
            <div class="filter-section">
                <form method="POST" class="row g-3">
                    <div class="col-md-5">
                        <label>
                            <i class="fas fa-calendar-alt"></i> Select Month
                        </label>
                        <select name="month" class="form-select">
                            <?php for ($m=1; $m<=12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>>
                                    <?= date("F", mktime(0,0,0,$m,1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label>
                            <i class="fas fa-calendar"></i> Select Year
                        </label>
                        <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2000" max="2100">
                    </div>
                    <div class="col-md-2 d-grid align-items-end">
                        <button type="submit" name="generate" class="btn-generate">
                            <i class="fas fa-sync-alt"></i> Generate
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <div class="card-modern">
        <div class="card-header-modern">
            <h3><i class="fas fa-chart-line"></i> Attendance Report</h3>
        </div>
        <div class="card-body-modern">

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
            $total_days = 0;
            $total_hours = 0;
            
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
                $total_days += $row['days_worked'];
                $total_hours += $row['total_hours'];
            }
            
            if(count($data) > 0): 
                $employee_count = count($data);
            ?>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-label">
                        <i class="fas fa-users"></i> Total Employees
                    </div>
                    <div class="stat-value"><?= $employee_count ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">
                        <i class="fas fa-calendar-check"></i> Total Days Worked
                    </div>
                    <div class="stat-value"><?= $total_days ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">
                        <i class="fas fa-clock"></i> Total Hours
                    </div>
                    <div class="stat-value"><?= number_format($total_hours, 1) ?></div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> #</th>
                            <th><i class="fas fa-user"></i> Employee Name</th>
                            <th><i class="fas fa-calendar-day"></i> Days Worked</th>
                            <th><i class="fas fa-clock"></i> Total Hours</th>
                            <th><i class="fas fa-calendar-alt"></i> Worked Dates</th>
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
                                <div class="employee-name">
                                    <i class="fas fa-user-circle"></i>
                                    <?= htmlspecialchars($row['Employee_Name']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="days-badge">
                                    <i class="fas fa-calendar-check"></i>
                                    <?= $row['days_worked'] ?> days
                                </span>
                            </td>
                            <td>
                                <span class="hours-badge">
                                    <i class="fas fa-clock"></i>
                                    <?= number_format($row['total_hours'], 2) ?> hrs
                                </span>
                            </td>
                            <td>
                                <div class="dates-container">
                                    <?php
                                    $dates = explode(', ', $row['worked_dates']);
                                    foreach ($dates as $d) {
                                        echo "<span class='date-badge'><i class='fas fa-calendar'></i> $d</span>";
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php else: ?>
            
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h4>No Attendance Records Found</h4>
                <p>No attendance data recorded for <?= date("F", mktime(0,0,0,$month,1)) . " " . $year ?></p>
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