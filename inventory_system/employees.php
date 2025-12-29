<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$message = "";

/* ADD / UPDATE EMPLOYEE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id   = $_POST['employee_id'] ?? '';
    $employee_name = trim($_POST['employee_name']);
    $hourly_rate   = floatval($_POST['hourly_rate']);
    $status        = $_POST['status'];

    if ($employee_id) {
        $sql = "UPDATE EMPLOYEES 
                SET Employee_Name = ?, Hourly_Rate = ?, Status = ?
                WHERE Employee_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsi", $employee_name, $hourly_rate, $status, $employee_id);
        $stmt->execute();
        $message = "Employee updated successfully.";
    } else {
        $sql = "INSERT INTO EMPLOYEES (Employee_Name, Hourly_Rate, Status)
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sds", $employee_name, $hourly_rate, $status);
        $stmt->execute();
        $message = "Employee added successfully.";
    }
}

/* FETCH EMPLOYEES */
$result = $conn->query("
    SELECT Employee_ID, Employee_Name, Hourly_Rate, Status
    FROM EMPLOYEES
    ORDER BY Employee_Name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Management</title>
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
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header h3 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert {
            border-radius: 1rem;
            border: none;
            padding: 1.25rem;
            animation: fadeInUp 0.4s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            background: rgba(255, 255, 255, 0.98);
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.6s ease backwards;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }

        .card-body {
            padding: 2rem;
        }

        .card h5 {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .btn-info {
            background: linear-gradient(135deg, var(--info), #0891b2);
            color: white;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4);
        }

        .table {
            margin: 0;
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

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .bg-success {
            background: linear-gradient(135deg, var(--success), #059669) !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .bg-secondary {
            background: linear-gradient(135deg, var(--gray), #475569) !important;
            box-shadow: 0 2px 8px rgba(100, 116, 139, 0.3);
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1rem;
            }
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
            <i class="fas fa-users-cog"></i>
            <span>Employee Management</span>
        </h3>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ADD / EDIT EMPLOYEE -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>
                <i class="fas fa-user-plus"></i>
                <span id="formTitle">Add New Employee</span>
            </h5>

            <form method="POST" class="row g-3" id="employeeForm">
                <input type="hidden" name="employee_id" id="employee_id">

                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Employee Name
                    </label>
                    <input type="text"
                           name="employee_name"
                           id="employee_name"
                           class="form-control"
                           placeholder="Enter employee name"
                           required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-rupee-sign"></i>
                        Hourly Rate (₹)
                    </label>
                    <input type="number"
                           step="0.01"
                           name="hourly_rate"
                           id="hourly_rate"
                           class="form-control"
                           placeholder="0.00"
                           required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        Status
                    </label>
                    <select name="status" id="status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EMPLOYEE LIST -->
    <div class="card">
        <div class="card-body">
            <h5>
                <i class="fas fa-list"></i>
                Employee List
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user me-2"></i>Name</th>
                            <th><i class="fas fa-rupee-sign me-2"></i>Hourly Rate</th>
                            <th><i class="fas fa-info-circle me-2"></i>Status</th>
                            <th width="120"><i class="fas fa-cog me-2"></i>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['Employee_Name']) ?></strong></td>
                                <td>₹<?= number_format($row['Hourly_Rate'], 2) ?></td>
                                <td>
                                    <span class="badge <?= $row['Status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                        <?= $row['Status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info"
                                        onclick="editEmployee(
                                            '<?= $row['Employee_ID'] ?>',
                                            '<?= htmlspecialchars($row['Employee_Name'], ENT_QUOTES) ?>',
                                            '<?= $row['Hourly_Rate'] ?>',
                                            '<?= $row['Status'] ?>'
                                        )">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No employees found. Add your first employee above.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function editEmployee(id, name, rate, status) {
    document.getElementById('employee_id').value = id;
    document.getElementById('employee_name').value = name;
    document.getElementById('hourly_rate').value = rate;
    document.getElementById('status').value = status;
    document.getElementById('formTitle').textContent = 'Edit Employee';
    
    // Scroll to form
    document.getElementById('employeeForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Reset form title when adding new
document.getElementById('employeeForm').addEventListener('reset', function() {
    document.getElementById('formTitle').textContent = 'Add New Employee';
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>