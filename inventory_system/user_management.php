<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: ADMIN ONLY ===
if (!check_permission([ROLE_ADMIN])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================

$sql = "SELECT User_ID, Username, Role FROM USERS ORDER BY User_ID ASC";
$result = $conn->query($sql);

// Handle status messages after redirection
$status_message = '';
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $status = htmlspecialchars($_GET['status']);
    $message = htmlspecialchars(urldecode($_GET['msg']));
    if ($status == 'success') {
        $status_message = "<div class='alert alert-success alert-modern' role='alert'><i class='fas fa-check-circle me-2'></i>$message</div>";
    } elseif ($status == 'error') {
        $status_message = "<div class='alert alert-danger alert-modern' role='alert'><i class='fas fa-exclamation-triangle me-2'></i>$message</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --success-dark: #059669;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --warning: #f59e0b;
            --info: #06b6d4;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow-x: hidden;
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
                radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.3), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(167, 139, 250, 0.3), transparent 50%);
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
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-text strong {
            color: #fff;
            font-weight: 600;
        }

        .container-main { 
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        /* Alert Styles */
        .alert-modern {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
            animation: slideInDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Page Header */
        .page-header { 
            background: rgba(255, 255, 255, 0.98);
            border-radius: 2rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 
                0 20px 60px -15px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header h1 { 
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--success), var(--info));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Enhanced Buttons */
        .btn-add-user {
            background: linear-gradient(135deg, var(--success), var(--success-dark));
            border: none;
            color: white;
            padding: 0.9rem 2rem;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-add-user::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn-add-user:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-add-user:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
        }

        /* Table Card */
        .table-card { 
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            box-shadow: 
                0 20px 50px -15px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s backwards;
        }

        /* Modern Table */
        .table-modern {
            margin: 0;
        }

        .table-modern thead th {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            font-weight: 700;
            color: var(--dark);
            padding: 1.25rem 1.5rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border: none;
        }

        .table-modern tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-color: #f1f5f9;
            font-weight: 600;
            color: var(--dark);
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.05), transparent);
            transform: scale(1.01);
        }

        /* User ID Badge */
        .user-id-badge {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        /* Username Display */
        .username-display {
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .username-display i {
            color: var(--primary);
        }

        /* Role Badges */
        .role-badge {
            padding: 0.6rem 1.2rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }

        .role-badge:hover {
            transform: scale(1.05);
        }

        .role-admin { 
            background: linear-gradient(135deg, var(--danger), var(--danger-dark));
            color: white;
        }

        .role-manager { 
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .role-cashier { 
            background: linear-gradient(135deg, var(--success), var(--success-dark));
            color: white;
        }

        /* Action Buttons */
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 0.6rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-action::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .btn-action:hover::after {
            width: 200px;
            height: 200px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-edit:hover {
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger), var(--danger-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
            margin-left: 0.5rem;
        }

        .btn-delete:hover {
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--warning);
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .empty-state h3 {
            color: var(--gray);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 1rem;
        }

        /* Stats Bar */
        .stats-bar {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-around;
            align-items: center;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.1s backwards;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            display: block;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .stats-bar {
                flex-direction: column;
                gap: 1.5rem;
            }

            .btn-action {
                font-size: 0.75rem;
                padding: 0.4rem 0.8rem;
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item me-3 d-flex align-items-center">
                        <span class="navbar-text text-white-50">
                            <i class="fas fa-user-shield me-1"></i> Logged in as: 
                            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                            (<?= htmlspecialchars($_SESSION['role']) ?>)
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
    
    <div class="container-main">
        
        <?= $status_message ?>

        <div class="page-header">
            <h1><i class="fas fa-users-cog"></i> User Management</h1>
            <button type="button" class="btn btn-add-user" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openUserModal('add')">
                <i class="fas fa-user-plus me-2"></i> Add New User
            </button>
        </div>

        <?php
        // Count users by role
        $result_copy = $conn->query($sql);
        $total_users = $result_copy->num_rows;
        $admin_count = $manager_count = $cashier_count = 0;
        while($row = $result_copy->fetch_assoc()) {
            if($row['Role'] == 'Admin') $admin_count++;
            elseif($row['Role'] == 'Manager') $manager_count++;
            elseif($row['Role'] == 'Cashier') $cashier_count++;
        }
        ?>

        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-value"><?= $total_users ?></span>
                <span class="stat-label"><i class="fas fa-users me-1"></i> Total Users</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: var(--danger);"><?= $admin_count ?></span>
                <span class="stat-label"><i class="fas fa-user-shield me-1"></i> Admins</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: var(--primary);"><?= $manager_count ?></span>
                <span class="stat-label"><i class="fas fa-user-tie me-1"></i> Managers</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: var(--success);"><?= $cashier_count ?></span>
                <span class="stat-label"><i class="fas fa-cash-register me-1"></i> Cashiers</span>
            </div>
        </div>
        
        <div class="table-card">
            <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th style="width: 12%;">User ID</th>
                            <th style="width: 35%;">Username</th>
                            <th style="width: 25%;">Role</th>
                            <th style="width: 28%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            $role_class = strtolower(str_replace(' ', '-', $row['Role']));
                        ?>
                        <tr>
                            <td>
                                <span class="user-id-badge">#<?= $row['User_ID'] ?></span>
                            </td>
                            <td>
                                <div class="username-display">
                                    <i class="fas fa-user-circle"></i>
                                    <?= htmlspecialchars($row['Username']) ?>
                                    <?php if ($row['User_ID'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-info">You</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?= $role_class ?>">
                                    <?php if($row['Role'] == 'Admin'): ?>
                                        <i class="fas fa-shield-halved me-1"></i>
                                    <?php elseif($row['Role'] == 'Manager'): ?>
                                        <i class="fas fa-briefcase me-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-cash-register me-1"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($row['Role']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-action btn-edit" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#userModal"
                                    onclick="openUserModal('edit', <?= $row['User_ID'] ?>, '<?= htmlspecialchars($row['Username'], ENT_QUOTES) ?>', '<?= $row['Role'] ?>')">
                                    <i class="fas fa-pen-to-square me-1"></i> Edit
                                </button>
                                <?php if ($row['User_ID'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-action btn-delete" onclick="deleteUser(<?= $row['User_ID'] ?>, '<?= htmlspecialchars($row['Username'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash-can me-1"></i> Delete
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h3>No Users Found</h3>
                    <p>Get started by adding your first system user.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'add_user_modal.html'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openUserModal(action, id = '', username = '', role = '') {
            const modalTitle = document.getElementById('userModalLabel');
            const form = document.getElementById('userForm');
            const userIdInput = document.getElementById('user_id');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const roleSelect = document.getElementById('role');

            form.reset(); 

            if (action === 'add') {
                modalTitle.innerHTML = '<i class="fas fa-user-plus me-2"></i> Add New System User';
                userIdInput.value = '';
                passwordInput.placeholder = 'Enter password';
                passwordInput.required = true;
            } else if (action === 'edit') {
                modalTitle.innerHTML = '<i class="fas fa-user-pen me-2"></i> Edit User: ' + username;
                userIdInput.value = id;
                usernameInput.value = username;
                roleSelect.value = role;
                passwordInput.placeholder = 'Leave blank to keep current password';
                passwordInput.required = false;
            }
        }

        function deleteUser(id, username) {
            // Create custom confirmation with better styling
            if (confirm("⚠️ DELETE USER\n\nAre you sure you want to permanently delete user '" + username + "' (ID: " + id + ")?\n\nThis action cannot be undone!")) {
                window.location.href = 'process_user.php?action=delete&user_id=' + id;
            }
        }

        // Add animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.table-modern tbody tr');
            rows.forEach((row, index) => {
                row.style.animation = `fadeInUp 0.4s ease ${0.3 + (index * 0.05)}s backwards`;
            });
        });
    </script>
</body>