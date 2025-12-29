<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

if (!isset($_GET['id'])) {
    header("Location: attendance_view.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

// Fetch existing record
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

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exit_time = $_POST['exit_time'];

    $update_sql = "
    UPDATE EMPLOYEE_ATTENDANCE
    SET 
        Exit_Time = ?,
        Working_Hours = ROUND(
            TIMESTAMPDIFF(MINUTE, Entry_Time, ?) / 60,
            2
        )
    WHERE Attendance_ID = ?
    ";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $exit_time, $exit_time, $id);

    if ($update_stmt->execute()) {
        header("Location: attendance_view.php");
        exit;
    } else {
        $message = "Failed to update exit time.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Exit Time</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .exit-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            backdrop-filter: blur(10px);
            max-width: 700px;
            width: 100%;
            animation: fadeInScale 0.5s ease;
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid #f5576c;
        }
        
        .page-icon {
            width: 80px;
            height: 80px;
            background: var(--warning-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4);
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .page-icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            background: var(--warning-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(235, 51, 73, 0.3);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .alert-custom i {
            font-size: 1.5rem;
        }
        
        .info-card {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%);
            border-radius: 15px;
            padding: 0;
            margin-bottom: 2rem;
            border: 2px solid rgba(245, 87, 108, 0.2);
            overflow: hidden;
        }
        
        .info-row {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid rgba(245, 87, 108, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: background 0.3s ease;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-row:hover {
            background: rgba(240, 147, 251, 0.08);
        }
        
        .info-icon {
            width: 45px;
            height: 45px;
            background: var(--warning-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .exit-time-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 2px solid #e9ecef;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .form-label-custom {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }
        
        .form-label-custom i {
            color: #f5576c;
            font-size: 1.3rem;
        }
        
        .time-input-wrapper {
            position: relative;
        }
        
        .form-control-custom {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
            text-align: center;
        }
        
        .form-control-custom:focus {
            border-color: #f5576c;
            box-shadow: 0 0 0 0.25rem rgba(245, 87, 108, 0.25);
            background: white;
        }
        
        .time-hint {
            text-align: center;
            margin-top: 0.75rem;
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .time-hint i {
            color: #f5576c;
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
        
        .btn-update {
            background: var(--warning-gradient);
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.6);
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
            .exit-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .exit-time-section {
                padding: 1.5rem;
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
    <div class="exit-container">
        <div class="page-header">
            <div class="page-icon">
                <i class="fas fa-door-open"></i>
            </div>
            <h1 class="page-title">Update Exit Time</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert-custom">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <div class="info-card">
            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Employee</div>
                    <div class="info-value"><?= htmlspecialchars($row['Employee_Name']) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Date</div>
                    <div class="info-value"><?= date('F d, Y', strtotime($row['Attendance_Date'])) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Entry Time</div>
                    <div class="info-value"><?= date('h:i A', strtotime($row['Entry_Time'])) ?></div>
                </div>
            </div>
        </div>

        <form method="POST">
            <div class="exit-time-section">
                <label class="form-label-custom">
                    <i class="fas fa-sign-out-alt"></i>
                    Select Exit Time
                </label>
                <div class="time-input-wrapper">
                    <input type="time" 
                           name="exit_time"
                           class="form-control form-control-custom"
                           value="<?= $row['Exit_Time'] ?>"
                           required>
                    <div class="time-hint">
                        <i class="fas fa-info-circle"></i>
                        <span>Working hours will be automatically calculated</span>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="attendance_view.php" class="btn btn-gradient btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-gradient btn-update">
                    <i class="fas fa-check"></i>
                    Update Exit Time
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>