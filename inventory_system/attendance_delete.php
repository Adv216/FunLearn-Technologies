<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

if (!isset($_GET['id'])) {
    header("Location: attendance_view.php");
    exit;
}

$id = intval($_GET['id']);

/* Fetch record to show confirmation */
$sql = "
SELECT 
    a.Attendance_ID,
    e.Employee_Name,
    a.Attendance_Date,
    a.Entry_Time,
    a.Exit_Time
FROM EMPLOYEE_ATTENDANCE a
JOIN EMPLOYEES e ON a.Employee_ID = e.Employee_ID
WHERE a.Attendance_ID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: attendance_view.php");
    exit;
}

$row = $result->fetch_assoc();

/* Handle delete confirmation */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_sql = "DELETE FROM EMPLOYEE_ATTENDANCE WHERE Attendance_ID = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();

    $_SESSION['success_message'] = "Attendance record deleted successfully.";
    header("Location: attendance_view.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
        
        body {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .delete-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            padding: 3rem;
            backdrop-filter: blur(10px);
            max-width: 650px;
            width: 100%;
            animation: shakeIn 0.6s ease;
        }
        
        @keyframes shakeIn {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
            }
        }
        
        .warning-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid #eb3349;
        }
        
        .warning-icon {
            width: 90px;
            height: 90px;
            background: var(--danger-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 25px rgba(235, 51, 73, 0.5);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 8px 25px rgba(235, 51, 73, 0.5);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 12px 35px rgba(235, 51, 73, 0.7);
            }
        }
        
        .warning-icon i {
            font-size: 3rem;
            color: white;
        }
        
        .warning-title {
            font-size: 2rem;
            font-weight: 700;
            background: var(--danger-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 1rem 0;
        }
        
        .warning-text {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .record-details {
            background: linear-gradient(135deg, rgba(235, 51, 73, 0.08) 0%, rgba(244, 92, 67, 0.08) 100%);
            border-radius: 15px;
            padding: 0;
            margin-bottom: 2rem;
            border: 2px solid rgba(235, 51, 73, 0.2);
            overflow: hidden;
        }
        
        .detail-item {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid rgba(235, 51, 73, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: background 0.3s ease;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-item:hover {
            background: rgba(235, 51, 73, 0.05);
        }
        
        .detail-icon {
            width: 45px;
            height: 45px;
            background: var(--danger-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        
        .detail-content {
            flex: 1;
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .confirmation-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .confirmation-box i {
            font-size: 2rem;
            color: #856404;
        }
        
        .confirmation-box p {
            margin: 0;
            color: #856404;
            font-weight: 600;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }
        
        .btn-gradient {
            border: none;
            color: white;
            padding: 0.9rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
            flex: 1;
        }
        
        .btn-delete-confirm {
            background: var(--danger-gradient);
            box-shadow: 0 4px 15px rgba(235, 51, 73, 0.4);
        }
        
        .btn-delete-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(235, 51, 73, 0.6);
            color: white;
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.4);
        }
        
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(149, 165, 166, 0.6);
            color: white;
        }
        
        @media (max-width: 768px) {
            .delete-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .warning-title {
                font-size: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column-reverse;
            }
            
            .btn-gradient {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="delete-container">
        <div class="warning-header">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="warning-title">Delete Attendance Record</h1>
            <p class="warning-text">This action cannot be undone!</p>
        </div>

        <div class="confirmation-box">
            <i class="fas fa-info-circle"></i>
            <p>Are you absolutely sure you want to delete this attendance record?</p>
        </div>

        <div class="record-details">
            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="detail-content">
                    <div class="detail-label">Employee Name</div>
                    <div class="detail-value"><?= htmlspecialchars($row['Employee_Name']) ?></div>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="detail-content">
                    <div class="detail-label">Attendance Date</div>
                    <div class="detail-value"><?= date('F d, Y', strtotime($row['Attendance_Date'])) ?></div>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="detail-content">
                    <div class="detail-label">Entry Time</div>
                    <div class="detail-value"><?= date('h:i A', strtotime($row['Entry_Time'])) ?></div>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="detail-content">
                    <div class="detail-label">Exit Time</div>
                    <div class="detail-value">
                        <?= $row['Exit_Time'] ? date('h:i A', strtotime($row['Exit_Time'])) : 'Not recorded' ?>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST">
            <div class="form-actions">
                <a href="attendance_view.php" class="btn btn-gradient btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-gradient btn-delete-confirm">
                    <i class="fas fa-trash-alt"></i>
                    Yes, Delete Record
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>