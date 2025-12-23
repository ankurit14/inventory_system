<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Only HR or Admin
if ($_SESSION['role'] != 'hr' && $_SESSION['role'] != 'admin') {
    echo "<script>alert('Access Denied');window.location='".BASE_URL."index.php';</script>";
    exit;
}

// Fetch list
$sql = "
    SELECT e.*, 
           u.name AS issued_by_name,
           emp.name AS employee_name,
           o.name AS other_receiver_name
    FROM emergency_issues e
    LEFT JOIN users u ON e.issued_by = u.id
    LEFT JOIN users emp ON e.issued_to_id = emp.id AND e.issued_to_type='employee'
    LEFT JOIN other_receivers o ON e.issued_to_id = o.id AND e.issued_to_type='other'
    ORDER BY e.id DESC
";
$result = mysqli_query($conn, $sql);
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
.table tbody tr:hover {
    background: #f1f5ff;
}

.action-btns .btn {
    margin-right: 4px;
    margin-bottom: 3px;
}
</style>


<div class="pcoded-content">
<div class="card">

<div class="header-box">
    <h2>Emergency Issue List</h2>
</div>

<div class="card-body">

    <a href="add.php" class="btn btn-primary mb-3">+ New Emergency Issue</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Issued To</th>
                <th>Issued By</th>
                <th>Remarks</th>
                <th>Date</th>
                <th>Items</th>
                <th width="22%">Action</th>
            </tr>
            </thead>

            <tbody>
            <?php 
            $count = 1;
            while ($row = mysqli_fetch_assoc($result)): 

                // Determine issue type & name
                if ($row['issued_to_type'] == 'employee') {
                    $issued_type = "Employee";
                    $issued_to = $row['employee_name'];
                } else {
                    $issued_type = "Other";
                    $issued_to = $row['other_receiver_name'];  // ✔ Correct name from other_receivers
                }

                // Item count
                $iid = $row['id'];
                $countItems = mysqli_fetch_assoc(
                    mysqli_query($conn, "SELECT COUNT(*) AS total FROM emergency_issue_items WHERE issue_id=$iid")
                )['total'];
            ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><b><?= $issued_type ?></b></td>
                    <td><?= $issued_to ?></td>
                    <td><?= $row['issued_by_name'] ?></td>
                    <td><?= $row['remarks'] ?></td>
                    <td><?= date("d-M-Y h:i A", strtotime($row['issue_date'])) ?></td>
                    <td><?= $countItems ?> Items</td>

                    <td class="action-btns">
                        <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="print.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-secondary btn-sm">Print</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>

        </table>
    </div>

</div>
</div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
