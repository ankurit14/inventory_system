<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

$employee_id = $_SESSION['user_id'];
$role        = $_SESSION['role']; // 'employee', 'hr', 'admin'

// Filters from GET
$employee_filter = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : null;
$product_filter  = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;
$from_date       = $_GET['from_date'] ?? null;
$to_date         = $_GET['to_date'] ?? null;

// Pagination
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page-1) * $per_page;

// Build WHERE clause
$where = [];
if($role == 'employee') {
    $where[] = "esm.employee_id = $employee_id";
} else {
    if($employee_filter) $where[] = "esm.employee_id = $employee_filter";
}
if($product_filter) $where[] = "esm.product_id = $product_filter";
if($from_date) $where[] = "DATE(esm.created_at) >= '$from_date'";
if($to_date) $where[] = "DATE(esm.created_at) <= '$to_date'";
$where_sql = $where ? "WHERE ".implode(' AND ',$where) : "";

// Count total records for pagination
$count_res = mysqli_query($conn, "
    SELECT COUNT(DISTINCT esm.employee_id, esm.product_id) AS total 
    FROM employee_stock_master esm
    $where_sql
");
$total_records = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch summary grouped by employee + product with limits
$query = "
SELECT 
    u.id AS employee_id,
    u.name AS employee_name,
    p.id AS product_id,
    p.name AS product_name,
    SUM(esm.stock_in) AS total_in,
    SUM(esm.stock_out) AS total_out,
    (SUM(esm.stock_in) - SUM(esm.stock_out)) AS balance
FROM employee_stock_master esm
LEFT JOIN users u ON esm.employee_id = u.id
LEFT JOIN products p ON esm.product_id = p.id
$where_sql
GROUP BY esm.employee_id, esm.product_id
ORDER BY u.name, p.name
LIMIT $offset, $per_page
";

$result = mysqli_query($conn, $query);

// Fetch employees and products for filter dropdowns (only for HR/Admin)
$employees_res = [];
$products_res = [];
if($role != 'employee'){
    $employees_res = mysqli_query($conn,"SELECT id,name FROM users ORDER BY name");
    $products_res  = mysqli_query($conn,"SELECT id,name FROM products ORDER BY name");
}

?>
<style>
    /* Table styling like supplier module */
.table th,
.table td {
    padding: 4px 8px !important;
    vertical-align: middle;
    font-size: 13px;
}

.btn-sm {
    padding: 2px 6px;
    font-size: 12px;
}

.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}
.header-box h2 { color:#fff; margin:0; font-size:22px; }
.header-box a.btn { background:#fff; padding:6px 12px; }

.table thead th {
    background:#2d6cdf;
    color:#fff;
    padding:6px 10px;
}
.status-btn.btn-sm {
    min-width: 80px;
}
</style>
<div class="pcoded-content">
    <div class="header-box">
        <h2>Employee Product Stock Summary</h2>
        <a href="add.php" class="btn btn-light">+ Add Distribution</a>
</div>
        <!-- Filter Form -->
       <!-- Filter Form -->
<form method="GET" class="d-flex flex-wrap justify-content-center align-items-end mb-3 gap-2">

    <?php if($role != 'employee'): ?>
    <div class="col-auto">
        <select name="employee_id" class="form-control">
            <option value="">All Employees</option>
            <?php mysqli_data_seek($employees_res,0); while($e = mysqli_fetch_assoc($employees_res)): ?>
                <option value="<?= $e['id'] ?>" <?= ($employee_filter==$e['id'])?'selected':'' ?>>
                    <?= htmlspecialchars($e['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <?php else: ?>
        <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
    <?php endif; ?>

    <div class="col-auto">
        <select name="product_id" class="form-control">
            <option value="">All Products</option>
            <?php 
            if($role != 'employee') mysqli_data_seek($products_res,0); 
            $products_list = ($role != 'employee') ? $products_res : mysqli_query($conn,"SELECT id,name FROM products ORDER BY name"); 
            ?>
            <?php while($p = mysqli_fetch_assoc($products_list)): ?>
                <option value="<?= $p['id'] ?>" <?= ($product_filter==$p['id'])?'selected':'' ?>>
                    <?= htmlspecialchars($p['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-auto">
        <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>" placeholder="From Date">
    </div>
    <div class="col-auto">
        <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>" placeholder="To Date">
    </div>

    <div class="col-auto">
        <button class="btn btn-primary">Filter</button>
        <a href="summary.php" class="btn btn-secondary">Reset</a>
    </div>

</form>

    

    <table class="table table-bordered" id="summaryTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Product</th>
                <th>Total Stock In</th>
                <th>Total Stock Out</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = $offset + 1; 
            $grand_total_in = 0;
            $grand_total_out = 0;
            $grand_balance = 0;

            while($row = mysqli_fetch_assoc($result)):
                $grand_total_in += $row['total_in'];
                $grand_total_out += $row['total_out'];
                $grand_balance += $row['balance'];
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_name']) ?></td>
               <td>
    <a href="distribution_report.php?product_id=<?= $row['product_id'] ?>&employee_id=<?= $row['employee_id'] ?>" title="Click to view Product Ledger">
        <?= htmlspecialchars($row['product_name']) ?>
        <i class="fa fa-book" style="color: #007bff; font-size: 16px; margin-left: 5px;" data-toggle="tooltip" data-placement="top" title="Product Ledger"></i>
    </a>
</td>

                <td><?= $row['total_in'] ?></td>
                <td><?= $row['total_out'] ?></td>
                <td><?= $row['balance'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight:bold; background:#f1f1f1;">
                <td colspan="3" class="text-end">Grand Total</td>
                <td><?= $grand_total_in ?></td>
                <td><?= $grand_total_out ?></td>
                <td><?= $grand_balance ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for($p=1; $p<=$total_pages; $p++): ?>
                <li class="page-item <?= ($p==$page)?'active':'' ?>">
                    <a class="page-link" href="?<?php 
                        $query_arr = $_GET; 
                        $query_arr['page'] = $p; 
                        echo http_build_query($query_arr);
                    ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
