<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$sql = "
SELECT 
    a.Attendance_ID,
    e.Employee_Name,
    a.Attendance_Date,
    a.Entry_Time,
    a.Exit_Time,
    a.Working_Hours,
    a.Remarks
FROM EMPLOYEE_ATTENDANCE a
JOIN EMPLOYEES e ON a.Employee_ID = e.Employee_ID
ORDER BY a.Attendance_Date DESC, a.Entry_Time ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            backdrop-filter: blur(10px);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid #667eea;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }
        
        .btn-success-gradient {
            background: var(--success-gradient);
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
        }
        
        .btn-success-gradient:hover {
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.6);
        }
        
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 15px;
        }
        
        .table-modern {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table-modern thead th {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1.2rem 1rem;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table-modern tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table-modern tbody tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
        }
        
        .badge-hours {
            background: var(--info-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #000;
            padding: 0.35rem 0.75rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            color: white;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .btn-exit {
            background: var(--warning-gradient);
        }
        
        .btn-edit {
            background: var(--info-gradient);
        }
        
        .btn-delete {
            background: var(--danger-gradient);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            color: white;
        }
        
        .bottom-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }
        
        .btn-dark-gradient {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.4);
        }
        
        .btn-secondary-gradient {
            background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }
        
        .employee-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .time-badge {
            background: rgba(102, 126, 234, 0.1);
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
            
            .bottom-actions {
                flex-direction: column;
            }
            
            .bottom-actions .btn-gradient {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-clock"></i>
                Attendance Records
            </h1>
            <a href="attendance_entry.php" class="btn btn-gradient">
                <i class="fas fa-plus-circle"></i> Mark Attendance
            </a>
        </div>

        <div class="card-custom">
            <div class="table-container">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Employee</th>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-sign-in-alt"></i> Entry</th>
                            <th><i class="fas fa-sign-out-alt"></i> Exit</th>
                            <th><i class="fas fa-hourglass-half"></i> Hours</th>
                            <th><i class="fas fa-comment"></i> Remarks</th>
                            <th><i class="fas fa-cog"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="employee-name">
                                    <?= htmlspecialchars($row['Employee_Name']) ?>
                                </td>
                                <td>
                                    <span class="time-badge">
                                        <?= date('M d, Y', strtotime($row['Attendance_Date'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-arrow-right text-success"></i>
                                    <?= date('h:i A', strtotime($row['Entry_Time'])) ?>
                                </td>
                                <td>
                                    <?php if ($row['Exit_Time']): ?>
                                        <i class="fas fa-arrow-left text-danger"></i>
                                        <?= date('h:i A', strtotime($row['Exit_Time'])) ?>
                                    <?php else: ?>
                                        <span class="badge-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['Working_Hours']): ?>
                                        <span class="badge-hours">
                                            <?= number_format($row['Working_Hours'], 2) ?> hrs
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($row['Remarks']) ?: '-' ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="attendance_exit_update.php?id=<?= $row['Attendance_ID'] ?>"
                                           class="btn-action btn-exit">
                                            <i class="fas fa-door-open"></i> Exit
                                        </a>
                                        <a href="attendance_edit.php?id=<?= $row['Attendance_ID'] ?>"
                                           class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="attendance_delete.php?id=<?= $row['Attendance_ID'] ?>"
                                           class="btn-action btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <h4>No Attendance Records Found</h4>
                                    <p>Start by marking attendance for your employees.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bottom-actions">
            <a href="attendance_monthly_summary.php" class="btn btn-gradient btn-success-gradient">
                <i class="fas fa-chart-bar"></i> Monthly Summary
            </a>
            <a href="salary_report.php" class="btn btn-gradient btn-dark-gradient">
                <i class="fas fa-file-invoice-dollar"></i> Salary Report
            </a>
            <a href="index.php" class="btn btn-gradient btn-secondary-gradient">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>