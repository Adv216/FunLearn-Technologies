<?php
include 'secure_page_template.php';
include 'db_connect.php';

$req = $conn->query("
SELECT r.*, p.Product_Name 
FROM production_requirements r
JOIN FINISHED_PRODUCTS p ON r.Product_ID=p.Product_ID
WHERE Status='Pending'
ORDER BY r.Req_ID DESC
");

$total_pending = $req->num_rows;
?>

<style>
.requirements-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.requirements-header h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.stats-badge {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

.table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.modern-table {
    margin: 0;
    width: 100%;
}

.modern-table thead {
    background: #f8f9fa;
}

.modern-table thead th {
    padding: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.modern-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f0f0f0;
}

.modern-table tbody tr:hover {
    background-color: #f8f9ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}

.modern-table tbody td {
    padding: 1.2rem 1rem;
    vertical-align: middle;
}

.product-name {
    font-weight: 500;
    color: #2d3748;
    font-size: 1rem;
}

.qty-badge {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.btn-complete {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-complete:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .requirements-header {
        padding: 1.5rem;
    }
    
    .modern-table {
        font-size: 0.9rem;
    }
    
    .modern-table thead th,
    .modern-table tbody td {
        padding: 0.8rem;
    }
}
</style>

<div class="requirements-header">
    <h3>📦 Production Requirements</h3>
    <div class="stats-badge">
        <?= $total_pending ?> Pending Task<?= $total_pending != 1 ? 's' : '' ?>
    </div>
</div>

<div class="table-container">
    <?php if($total_pending > 0): ?>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Required Quantity</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $req->data_seek(0); // Reset pointer after counting
                while($r = $req->fetch_assoc()): 
                ?>
                <tr>
                    <td>
                        <span class="product-name"><?= htmlspecialchars($r['Product_Name']) ?></span>
                    </td>
                    <td>
                        <span class="qty-badge"><?= number_format($r['Required_Qty']) ?> units</span>
                    </td>
                    <td style="text-align: center;">
                        <a href="complete_production.php?id=<?= $r['Req_ID'] ?>" 
                           class="btn-complete"
                           onclick="return confirm('Mark this production requirement as completed?')">
                            ✓ Mark Completed
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div>✓</div>
            <h4>All Caught Up!</h4>
            <p>No pending production requirements at the moment.</p>
        </div>
    <?php endif; ?>
</div>