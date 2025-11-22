<?php
// stock_ledger.php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Get product_id
$product_id = intval($_GET['product_id']);

// Filters
$name_filter = isset($_GET['name']) ? trim($_GET['name']) : "";
$from_date   = isset($_GET['from']) ? trim($_GET['from']) : "";
$to_date     = isset($_GET['to']) ? trim($_GET['to']) : "";


$pq = mysqli_query($conn, "SELECT name FROM products WHERE id = $product_id");
$prod = mysqli_fetch_assoc($pq);
$product_name = $prod ? $prod['name'] : "Unknown Product";

// MAIN SQL Subquery (Base)
$base_sql = "
    SELECT
        sm.*,

        CASE 
            WHEN sm.note = 'Purchased' 
                THEN s.name        -- Supplier Name
            ELSE u2.name           -- User Name
        END AS entry_name

    FROM stock_master sm

    LEFT JOIN purchases p 
        ON sm.note = 'Purchased' AND sm.ref_id = p.id

    LEFT JOIN suppliers s 
        ON p.supplier_id = s.id

    LEFT JOIN product_requests pr 
        ON sm.note != 'Purchased' AND sm.ref_id = pr.id

    LEFT JOIN users u2 
        ON pr.request_by = u2.id

    WHERE sm.product_id = $product_id
";

// MAIN WHERE for filters
$where = " WHERE 1=1 ";

// Name filter (Supplier/User)
if ($name_filter != "") {
    $name_safe = mysqli_real_escape_string($conn, $name_filter);
    $where .= " AND entry_name LIKE '%$name_safe%' ";
}

// Date filters
if ($from_date != "" && $to_date != "") {
    $where .= " AND DATE(created_at) BETWEEN '$from_date' AND '$to_date' ";
} elseif ($from_date != "") {
    $where .= " AND DATE(created_at) >= '$from_date' ";
} elseif ($to_date != "") {
    $where .= " AND DATE(created_at) <= '$to_date' ";
}

// Final SQL
$sql = "
    SELECT * FROM (
        $base_sql
    ) AS final_table
    $where
    ORDER BY created_at DESC
";

$res = mysqli_query($conn, $sql);

// Total counters
$total_in = 0;
$total_out = 0;
?>
<style>
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    align-items: center;
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    text-align: center;
}

.header-box h5 {
    color: #fff;
    margin: 0;
    font-size: 20px;
    font-weight: 200;
}
.header-box a.btn {
    color: #1f2937;
    background-color: #fff;
    padding: 6px 15px;
    border-radius: 6px;
    text-decoration: none;
}

.filter-container input, .filter-container select {
    padding: 6px 10px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.table thead th {
    background: #2d6cdf;
    color: #fff;
    font-size: 14px;
}

.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    padding: 4px 6px !important;
    height: 30px !important;
    line-height: 14px;
}
.table tbody td {
    font-size: 14px;
    padding: 6px 10px;
}
.table tbody tr:hover {
    background: #f1f5ff;
}

.btn-sm {
    padding: 3px 7px;
    font-size: 13px;
}
.status-btn {
    min-width: 80px;
}
.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    padding: 4px 6px !important;
    height: 30px !important;
    line-height: 14px;
}
.page-header-bg {
    position: absolute;
    top: 5px;
    left: 0;
    width: 100%;
    height: 50%;
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    z-index: 1;
    border-radius: 8px;
}

</style>
<div class="pcoded-content">
    <div class="header-box">
    <h2 style="color: #ffffff; margin: 0;">
        Stock Ledger for Product Name >>
        <span style="
            color: #ffffff;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.35);
        ">
            <?= ucwords($product_name) ?>
        </span>
    </h2>
</div>
<div class="container mt-4">



    

    <!-- FILTER FORM -->
    <form method="get" class="row mb-3">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">

        <div class="col-md-3">
            <label>User / Supplier Name</label>
            <input type="text" name="name" value="<?= $name_filter ?>" class="form-control" placeholder="Search by Name">
        </div>

        <div class="col-md-3">
            <label>From Date</label>
            <input type="date" name="from" value="<?= $from_date ?>" class="form-control">
        </div>

        <div class="col-md-3">
            <label>To Date</label>
            <input type="date" name="to" value="<?= $to_date ?>" class="form-control">
        </div>

        <div class="col-md-3">
            <label>&nbsp;</label>
            <div>
                <button class="btn btn-primary mt-1">Filter</button>
                <a href="stock_ledger.php?product_id=<?= $product_id ?>" class="btn btn-secondary mt-1">Reset</a>
            </div>
        </div>
    </form>

    <!-- TABLE -->
    <table border="1" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Stock In</th>
            <th>Stock Out</th>
            <th>Source</th>
            <!-- <th>Ref ID</th> -->
            <th>User/Supplier Name</th>
            <th>Note</th>
            <th>Date</th>
        </tr>
        </thead>

        <?php $i = 1; while ($row = mysqli_fetch_assoc($res)): ?>
        <?php
            $total_in  += $row['stock_in'];
            $total_out += $row['stock_out'];
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['stock_in'] ?></td>
            <td><?= $row['stock_out'] ?></td>
            <td><?= $row['source'] ?></td>
            <!-- <td><?= $row['ref_id'] ?></td> -->
            <td><?= $row['entry_name'] ?: '-' ?></td>
            <td><?= htmlspecialchars($row['note']) ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- TOTALS -->
    <div class="alert alert-info mt-3">
        <strong>Total Stock In:</strong> <?= $total_in ?> &nbsp;&nbsp;&nbsp;
        <strong>Total Stock Out:</strong> <?= $total_out ?>
    </div>

</div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
