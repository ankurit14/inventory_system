<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
global $conn;

$login_employee_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // 'employee', 'hr', 'admin'

// Filters
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : null; // IMPORTANT
$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : null; // optional
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
$from_date = $_GET['from_date'] ?? null;
$to_date   = $_GET['to_date'] ?? null;

// Fetch product name
$product_name = '';
if($product_id){
    $prod_res = mysqli_query($conn, "SELECT name FROM products WHERE id = $product_id LIMIT 1");
    if($prod_res && mysqli_num_rows($prod_res) > 0){
        $product_name = mysqli_fetch_assoc($prod_res)['name'];
    } else {
        $product_name = "Unknown Product";
    }
}

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = [];

// Employee restriction
if($role == 'employee'){
    $where[] = "esm.employee_id = $login_employee_id";
}else{
    // For HR/Admin, optionally filter by employee_id from GET
    if($employee_id) $where[] = "esm.employee_id = $employee_id";
}

// Product restriction
if($product_id) $where[] = "esm.product_id = $product_id";

// Client/date filters
if($client_id) $where[] = "esm.client_id = $client_id";
if($from_date) $where[] = "DATE(esm.created_at) >= '$from_date'";
if($to_date) $where[] = "DATE(esm.created_at) <= '$to_date'";

$where_sql = $where ? "WHERE ".implode(' AND ',$where) : "";

// Fetch rows
$query = "
SELECT 
    esm.product_id,
    p.name AS product_name,
    esm.employee_id,
    u.name AS employee_name,
    esm.client_id,
    c.name AS client_name,
    esm.stock_in,
    esm.stock_out,
    (esm.stock_in - esm.stock_out) AS balance,
    esm.created_at
FROM employee_stock_master esm
LEFT JOIN users u ON esm.employee_id = u.id
LEFT JOIN products p ON esm.product_id = p.id
LEFT JOIN clients c ON esm.client_id = c.id
$where_sql
ORDER BY esm.created_at DESC, p.name, u.name, c.name
LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $query);

// Pagination count
$count_query = "SELECT COUNT(*) as total FROM employee_stock_master esm $where_sql";
$count_res = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
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
    <div class="header-box d-flex justify-content-between align-items-center mb-3">
        <h2>Distribution Ledger for Product : <?= htmlspecialchars($product_name) ?></h2>
        <a href="javascript:history.back()" class="btn btn-light">← Back</a>
    </div>

    <!-- Client Filter -->
    <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>"> <!-- keep product filter fixed -->
        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id) ?>"> <!-- optional -->
        <div class="col-md-4">
            <label>Client</label>
            <select name="client_id" class="form-control">
                <option value="">All Clients</option>
                <?php
                $clients_res = mysqli_query($conn,"SELECT id,name FROM clients ORDER BY name");
                while($c = mysqli_fetch_assoc($clients_res)):
                ?>
                    <option value="<?= $c['id'] ?>" <?= ($client_id==$c['id'])?'selected':'' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>From Date</label>
            <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label>To Date</label>
            <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Employee</th>
                    <th>Client</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
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
    $grand_total_in += $row['stock_in'];
    $grand_total_out += $row['stock_out'];
    $grand_balance += $row['balance'];
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
    <td><?= htmlspecialchars($row['product_name']) ?></td>
    <td><?= htmlspecialchars($row['employee_name']) ?></td>
    <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
    <td><?= $row['stock_in'] ?></td>
    <td><?= $row['stock_out'] ?></td>
    <td><?= $row['balance'] ?></td>
</tr>
<?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="table-success font-weight-bold">
                    <td colspan="5" class="text-end">Grand Total</td>
                    <td><?= $grand_total_in ?></td>
                    <td><?= $grand_total_out ?></td>
                    <td><?= $grand_balance ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for($p=1;$p<=$total_pages;$p++): ?>
                <li class="page-item <?= ($p==$page)?'active':'' ?>">
                    <a class="page-link" href="?<?php 
                        $qs = $_GET;
                        $qs['page']=$p;
                        echo http_build_query($qs);
                    ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
