<?php
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH.'/config/db.php');

// Check if we only need total
$total_only = isset($_GET['total_only']) && $_GET['total_only'] === 'yes';

// Get filters
$from_date   = $_GET['from'] ?? "";
$to_date     = $_GET['to'] ?? "";
$name_filter = $_GET['name'] ?? "";
$type_filter = $_GET['rtype'] ?? "";

// Escape inputs
$name_safe = mysqli_real_escape_string($conn, $name_filter);
$type_safe = mysqli_real_escape_string($conn, $type_filter);

// Build WHERE conditions
$where_conditions = [];

if ($name_safe !== "") {
    $where_conditions[] = "(
        (e.receiver_type='employee' AND u.name LIKE '%$name_safe%') OR
        (e.receiver_type='other' AND o.name LIKE '%$name_safe%')
    )";
}

if ($type_safe !== "") {
    $where_conditions[] = "e.receiver_type = '$type_safe'";
}

if ($from_date !== "" && $to_date !== "") {
    $where_conditions[] = "e.date BETWEEN '$from_date' AND '$to_date'";
} else if ($from_date !== "") {
    $where_conditions[] = "e.date >= '$from_date'";
} else if ($to_date !== "") {
    $where_conditions[] = "e.date <= '$to_date'";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// If only total is requested
if ($total_only) {
    $total_sql = "
    SELECT 
        COALESCE(SUM(e.received_amt), 0) AS total_expense,
        COUNT(*) AS record_count
    FROM expenses e
    LEFT JOIN users u ON e.receiver_id = u.id AND e.receiver_type = 'employee'
    LEFT JOIN other_receivers o ON e.receiver_id = o.id AND e.receiver_type = 'other'
    $where_clause
    ";
    
    $total_res = mysqli_query($conn, $total_sql);
    $row_total = mysqli_fetch_assoc($total_res);
    
    echo json_encode([
        'total_expense' => $row_total['total_expense'] ?? 0,
        'record_count' => $row_total['record_count'] ?? 0
    ]);
    exit;
}

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Main query fetching expenses with receiver name
$sql = "
SELECT e.*, 
       CASE 
           WHEN e.receiver_type = 'employee' THEN u.name
           WHEN e.receiver_type = 'other' THEN o.name
           ELSE 'Unknown'
       END AS receiver_name
FROM expenses e
LEFT JOIN users u ON e.receiver_id = u.id AND e.receiver_type = 'employee'
LEFT JOIN other_receivers o ON e.receiver_id = o.id AND e.receiver_type = 'other'
$where_clause
ORDER BY e.date DESC 
LIMIT $limit OFFSET $offset
";

$res = mysqli_query($conn, $sql);

// Get total count for pagination
$count_sql = "
SELECT COUNT(*) AS total
FROM expenses e
LEFT JOIN users u ON e.receiver_id = u.id AND e.receiver_type = 'employee'
LEFT JOIN other_receivers o ON e.receiver_id = o.id AND e.receiver_type = 'other'
$where_clause
";

$count_res = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_res);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);
?>

<!-- STYLES -->
<style>
    .table thead th {
        background: #2d6cdf;
        color: white;
        font-size: 14px;
        padding: 8px 6px !important;
    }
    .table tbody td {
        font-size: 14px;
        padding: 8px 10px;
        text-align: center;
        vertical-align: middle;
    }
    .badge {
        font-size: 11px;
        padding: 3px 8px;
    }
    .table tbody tr:hover {
        background: #f1f5ff;
    }
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
    }
</style>

<!-- TABLE -->
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Receiver Name</th>
                <th>Received Amount</th>
                <th>Payment Method</th>
                <th>Voucher Image</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = $offset + 1;
            if (mysqli_num_rows($res) == 0): ?>
                <tr>
                    <td colspan="7" class="text-center">No expenses found.</td>
                </tr>
            <?php endif; ?>
            
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <?= htmlspecialchars($row['receiver_name']) ?>
                        <br>
                        <span class="badge bg-info"><?= htmlspecialchars($row['receiver_type']) ?></span>
                    </td>
                    <td>₹ <?= htmlspecialchars($row['received_amt']) ?></td>
                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                    <td>
                    <?php if (!empty($row['voucher_image'])): 
                        
                        $image_url = BASE_URL . "uploads/vouchers/" . $row['voucher_image'];
                        $full_path = BASE_PATH . "/uploads/vouchers/" . $row['voucher_image'];

                        if (file_exists($full_path)): ?>
                            <img src="<?= $image_url ?>" 
                                alt="Voucher Image" 
                                style="width:50px; height:50px; object-fit:cover; border-radius:50%;"
                                class="img-thumbnail">
                        <?php else: ?>
                            <span class="badge bg-secondary">N/A</span>
                        <?php endif; ?>

                    <?php else: ?>
                        <span class="badge bg-secondary">N/A</span>
                    <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td>
                        <button class="btn btn-info btn-sm" 
                                onclick="openViewModal(
                                    '<?= htmlspecialchars($row['receiver_type']) ?>',
                                    '<?= htmlspecialchars($row['receiver_name']) ?>',
                                    '<?= htmlspecialchars($row['received_amt']) ?>',
                                    '<?= htmlspecialchars($row['payment_method']) ?>',
                                    '<?= htmlspecialchars($row['apv_by']) ?>',
                                    '<?= htmlspecialchars($row['notes']) ?>',
                                    '<?= htmlspecialchars($row['date']) ?>',
                                    '<?= htmlspecialchars($row['cheque_no']) ?>',
                                    '<?= htmlspecialchars($row['voucher_image']) ?>'
                                )">
                            View
                        </button>
                        <button class="btn btn-sm btn-primary"
                                onclick="openVoucherPrintModal(
                                    '<?= htmlspecialchars($row['receiver_type']) ?>',
                                    '<?= htmlspecialchars($row['receiver_name']) ?>',   <!-- fixed here -->
                                    '<?= htmlspecialchars($row['received_amt']) ?>',
                                    '<?= htmlspecialchars($row['payment_method']) ?>',
                                    '<?= htmlspecialchars($row['apv_by']) ?>',
                                    '<?= htmlspecialchars($row['notes']) ?>',
                                    '<?= htmlspecialchars($row['date']) ?>',
                                    '<?= htmlspecialchars($row['cheque_no']) ?>'
                                )">
                            🖨 Print
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
<nav>
    <ul class="pagination justify-content-end mb-2">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="javascript:void(0)" onclick="goToPage(<?= $page - 1 ?>)">
                <i class="ti-angle-left"></i>
            </a>
        </li>
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                <a class="page-link" href="javascript:void(0)" onclick="goToPage(<?= $p ?>)"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
            <a class="page-link" href="javascript:void(0)" onclick="goToPage(<?= $page + 1 ?>)">
                <i class="ti-angle-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
