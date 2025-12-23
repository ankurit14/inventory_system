<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Access only for HR or Admin
if ($_SESSION['role'] != 'hr' && $_SESSION['role'] != 'admin') {
    echo "<script>alert('Access denied');window.location='".BASE_URL."index.php';</script>";
    exit;
}

// Get Issue ID
$issue_id = intval($_GET['id']);

// Fetch master record
$sql = "
    SELECT e.*, 
           u.name AS issued_by_name,
           emp.name AS employee_name,
           o.name AS other_receiver_name
    FROM emergency_issues e
    LEFT JOIN users u ON e.issued_by = u.id
    LEFT JOIN users emp ON e.issued_to_id = emp.id AND e.issued_to_type = 'employee'
    LEFT JOIN other_receivers o ON e.issued_to_id = o.id AND e.issued_to_type = 'other'
    WHERE e.id = $issue_id
";
$data = mysqli_fetch_assoc(mysqli_query($conn, $sql));

if (!$data) {
    echo "<script>alert('Invalid Issue ID');window.location='index.php';</script>";
    exit;
}

// Issued To & Type
if ($data['issued_to_type'] == "employee") {
    $issued_type = "Employee";
    $issued_to = $data['employee_name'];
} else {
    $issued_type = "Other";
    $issued_to = $data['other_receiver_name'];
}

// Fetch issued products
$item_sql = "
    SELECT i.*, 
           c.name AS category_name,
           sc.name AS subcategory_name,
           p.name AS product_name
    FROM emergency_issue_items i
    LEFT JOIN category c ON i.category_id = c.id
    LEFT JOIN sub_category sc ON i.sub_category_id = sc.id
    LEFT JOIN products p ON i.product_id = p.id
    WHERE i.issue_id = $issue_id
";
$items = mysqli_query($conn, $item_sql);
?>

<style>
.header-box {
    background: linear-gradient(135deg, #da4453, #f6bb42);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.header-box h2 { color: #fff; text-align: center; }

.table thead th {
    background: #dc3545;
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
.table tbody tr:hover { background: #f1f5ff; }

.btn-sm {
    padding: 3px 7px;
    font-size: 13px;
}
</style>

<div class="pcoded-content">
<div class="card">

<div class="header-box">
    <h2>Emergency Issue Details</h2>
</div>

<div class="card-body">

<!-- Type -->
<div class="mb-2 row">
    <label class="col-sm-3 col-form-label"><b>Type:</b></label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" 
               value="<?= $issued_type ?>" readonly>
    </div>
</div>

<!-- Issued To -->
<div class="mb-2 row">
    <label class="col-sm-3 col-form-label"><b>Issued To:</b></label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" 
               value="<?= $issued_to ?>" readonly>
    </div>
</div>

<!-- Issued By -->
<div class="mb-2 row">
    <label class="col-sm-3 col-form-label"><b>Issued By:</b></label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" 
               value="<?= $data['issued_by_name'] ?>" readonly>
    </div>
</div>

<!-- Request For / Purpose -->
<div class="mb-2 row">
    <label class="col-sm-3 col-form-label"><b>Request For:</b></label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" 
               value="<?= $data['request_for'] ?>" readonly>
    </div>
</div>

<!-- Date -->
<div class="mb-2 row">
    <label class="col-sm-3 col-form-label"><b>Issue Date:</b></label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" 
               value="<?= date('d-M-Y h:i A', strtotime($data['issue_date'])) ?>" readonly>
    </div>
</div>

<!-- Remarks -->
<div class="mb-2 row">
    <label class="col-sm-3 col-form-label"><b>Remarks:</b></label>
    <div class="col-sm-9">
        <textarea class="form-control form-control-sm" rows="2" readonly><?= $data['remarks'] ?></textarea>
    </div>
</div>

<hr>

<h4>Issued Products</h4>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>Category</th>
            <th>Sub Category</th>
            <th>Product</th>
            <th>Unit</th>
            <th>Qty Issued</th>
        </tr>
        </thead>

        <tbody>
        <?php 
        $i = 1;
        while ($row = mysqli_fetch_assoc($items)): 
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $row['category_name'] ?></td>
                <td><?= $row['subcategory_name'] ?></td>
                <td><?= $row['product_name'] ?></td>
                <td><?= $row['unit'] ?></td>
                <td><?= $row['qty_issued'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<a href="index.php" class="btn btn-secondary">Back</a>

</div>
</div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
