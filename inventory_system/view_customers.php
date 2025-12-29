<?php
include 'secure_page_template.php'; 
include 'db_connect.php';
$result = $conn->query("SELECT customer_ID, Name, Phone, Email, Address, City FROM CUSTOMER ORDER BY Name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            background: rgba(30, 41, 59, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .container-main {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header h1 i {
            color: var(--primary);
        }

        .action-bar {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .btn-enhanced {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-success-enhanced {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
        }

        .btn-success-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .table-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-modern {
            width: 100%;
            margin: 0;
        }

        .table-modern thead th {
            background: white;
            color: #475569;
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-modern tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.002);
        }

        .table-modern tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #334155;
        }

        .customer-id {
            font-weight: 700;
            color: var(--primary);
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
            padding: 0.35rem 0.75rem;
            border-radius: 0.5rem;
            display: inline-block;
        }

        .customer-name {
            font-weight: 600;
            color: #1e293b;
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
        }

        .contact-info i {
            color: #94a3b8;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .empty-state h3 {
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-header, .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .table-modern {
                font-size: 0.875rem;
            }
            
            .table-modern thead th,
            .table-modern tbody td {
                padding: 0.75rem;
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
                    <li class="nav-item">
                        <a class="nav-link" href="invoice_history.php">
                            <i class="fas fa-history me-2"></i>History
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item me-3 d-flex align-items-center">
                        <span class="navbar-text text-white-50">
                            <i class="fas fa-user-circle me-1"></i> Logged in as: 
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
        
        <div class="page-header">
            <h1>
                <i class="fas fa-users"></i>
                <span>Customer Directory</span>
            </h1>
            <a href="customer_form.php" class="btn btn-success-enhanced btn-enhanced">
                <i class="fas fa-user-plus me-2"></i>Add New Customer
            </a>
        </div>

        <div class="action-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search by name, email, phone..." onkeyup="searchTable()">
            </div>
            <div style="color: #64748b; font-weight: 500;">
                <i class="fas fa-users me-2"></i>
                Total: <strong style="color: #1e293b;"><?= $result->num_rows ?></strong> customers
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-card">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-address-book"></i>
                        <span>All Customers</span>
                    </h2>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern" id="customerTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>City</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><span class="customer-id">#<?= htmlspecialchars($row['customer_ID']) ?></span></td>
                                <td class="customer-name"><?= htmlspecialchars($row['Name']) ?></td>
                                <td>
                                    <?php if ($row['Phone']): ?>
                                        <div class="contact-info">
                                            <i class="fas fa-phone"></i>
                                            <span><?= htmlspecialchars($row['Phone']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['Email']): ?>
                                        <div class="contact-info">
                                            <i class="fas fa-envelope"></i>
                                            <span><?= htmlspecialchars($row['Email']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['Address'] ? htmlspecialchars($row['Address']) : '<span style="color: #cbd5e1;">—</span>' ?></td>
                                <td><?= $row['City'] ? htmlspecialchars($row['City']) : '<span style="color: #cbd5e1;">—</span>' ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="table-card">
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Customers Found</h3>
                    <p>Start by adding your first customer</p>
                    <a href="customer_form.php" class="btn btn-success-enhanced btn-enhanced mt-3">
                        <i class="fas fa-user-plus me-2"></i>Add First Customer
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>

    <script>
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('customerTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let found = false;
                const td = tr[i].getElementsByTagName('td');
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>