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

/* Fetch existing attendance record */
$sql = "
SELECT 
    a.Attendance_ID,
    e.Employee_Name,
    a.Attendance_Date,
    a.Entry_Time,
    a.Exit_Time,
    a.Remarks
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

/* Handle form submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry_time = $_POST['entry_time'];
    $exit_time  = !empty($_POST['exit_time']) ? $_POST['exit_time'] : NULL;
    $remarks    = trim($_POST['remarks']);

    if ($exit_time) {
        $update_sql = "
        UPDATE EMPLOYEE_ATTENDANCE
        SET 
            Entry_Time = ?,
            Exit_Time = ?,
            Working_Hours = ROUND(
                TIMESTAMPDIFF(MINUTE, ?, ?) / 60,
                2
            ),
            Remarks = ?
        WHERE Attendance_ID = ?
        ";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            "sssssi",
            $entry_time,
            $exit_time,
            $entry_time,
            $exit_time,
            $remarks,
            $id
        );
    } else {
        $update_sql = "
        UPDATE EMPLOYEE_ATTENDANCE
        SET 
            Entry_Time = ?,
            Exit_Time = NULL,
            Working_Hours = NULL,
            Remarks = ?
        WHERE Attendance_ID = ?
        ";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            "ssi",
            $entry_time,
            $remarks,
            $id
        );
    }

    if ($update_stmt->execute()) {
        header("Location: attendance_view.php");
        exit;
    } else {
        $message = "Failed to update attendance record.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .edit-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            backdrop-filter: blur(10px);
            max-width: 700px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid #667eea;
        }
        
        .page-icon {
            width: 80px;
            height: 80px;
            background: var(--info-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
        }
        
        .page-icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            background: var(--info-gradient);
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
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
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
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group-custom {
            margin-bottom: 1.5rem;
        }
        
        .form-label-custom {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-label-custom i {
            color: #667eea;
        }
        
        .form-control-custom {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.9rem 1.2rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control-custom:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
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
        }
        
        .btn-save {
            background: var(--info-gradient);
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6);
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
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }
        
        @media (max-width: 768px) {
            .edit-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-gradient {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="edit-container">
        <div class="page-header">
            <div class="page-icon">
                <i class="fas fa-edit"></i>
            </div>
            <h1 class="page-title">Edit Attendance Record</h1>
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
        </div>

        <form method="POST">
            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-sign-in-alt"></i>
                    Entry Time
                </label>
                <input type="time"
                       name="entry_time"
                       class="form-control form-control-custom"
                       value="<?= $row['Entry_Time'] ?>"
                       required>
            </div>

            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-sign-out-alt"></i>
                    Exit Time
                    <small class="text-muted ms-2">(Optional)</small>
                </label>
                <input type="time"
                       name="exit_time"
                       class="form-control form-control-custom"
                       value="<?= $row['Exit_Time'] ?>">
            </div>

            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-comment"></i>
                    Remarks
                </label>
                <input type="text"
                       name="remarks"
                       class="form-control form-control-custom"
                       placeholder="Add any notes or comments..."
                       value="<?= htmlspecialchars($row['Remarks']) ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-gradient btn-save">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
                <a href="attendance_view.php" class="btn btn-gradient btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>