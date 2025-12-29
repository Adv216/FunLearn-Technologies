<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'db_connect.php';

// Check if connection exists
if (!isset($conn)) {
    die("Database connection failed");
}

$message = "";
$message_type = "";

// --- PHP LOGIC: HANDLING THE SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture the numeric Employee_ID from the dropdown
    $emp_id = $_POST['employee_id']; 
    $date = $_POST['attendance_date'];
    $entry = $_POST['entry_time'];
    $exit = !empty($_POST['exit_time']) ? $_POST['exit_time'] : NULL;
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

    // The query now uses Employee_ID to match your database schema
    $sql = "INSERT INTO EMPLOYEE_ATTENDANCE 
            (Employee_ID, Attendance_Date, Entry_Time, Exit_Time, Remarks)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // "i" for integer (Employee_ID), followed by 4 strings (s)
        $stmt->bind_param("issss", $emp_id, $date, $entry, $exit, $remarks);

        if ($stmt->execute()) {
            $message = "Attendance recorded successfully!";
            $message_type = "success";
        } else {
            $message = "Error recording attendance: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Error preparing statement: " . $conn->error;
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            padding: 2rem 0;
        }

        .container { max-width: 900px; position: relative; z-index: 1; }

        .header-card, .form-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(30px);
            border-radius: 2rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .header-text h1 {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-label { font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;}
        .form-control { border: 2px solid #e2e8f0; border-radius: 1rem; padding: 1rem; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }

        .btn-submit {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            padding: 1.25rem;
            border-radius: 1.25rem;
            font-weight: 800;
            width: 100%;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }

        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn btn-light mb-4 rounded-pill px-4 fw-bold text-primary">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>

    <div class="header-card text-center">
        <h1 class="display-5 fw-bold text-primary">Attendance Entry</h1>
        <p class="text-muted">Record employee entry and exit times</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fas fa-info-circle me-2"></i> <?= htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-user"></i> Select Employee</label>
                <select name="employee_id" class="form-control" required>
                    <option value="">-- Choose Employee --</option>
                    <?php
                    // Pulling real names and IDs from your employees table
                    $emp_res = $conn->query("SELECT Employee_ID, Employee_Name FROM employees ORDER BY Employee_Name ASC");
                    if($emp_res && $emp_res->num_rows > 0) {
                        while($row = $emp_res->fetch_assoc()) {
                            echo "<option value='{$row['Employee_ID']}'>" . htmlspecialchars($row['Employee_Name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-row">
                <div>
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Attendance Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div>
                    <label class="form-label"><i class="fas fa-clock"></i> Entry Time</label>
                    <input type="time" name="entry_time" class="form-control" required value="<?= date('H:i'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label class="form-label"><i class="fas fa-sign-out-alt"></i> Exit Time (Optional)</label>
                    <input type="time" name="exit_time" class="form-control">
                </div>
                <div>
                    <label class="form-label"><i class="fas fa-sticky-note"></i> Remarks</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save me-2"></i> Save Record
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>