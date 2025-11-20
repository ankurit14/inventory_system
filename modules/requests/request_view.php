<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Access Control
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

// Get Request Info
$req = mysqli_query($conn, "
    SELECT pr.*, u.name AS requested_by
    FROM product_requests pr
    LEFT JOIN users u ON pr.request_by = u.id
    WHERE pr.id = $id
");

if (mysqli_num_rows($req) == 0) {
    echo "<h4 class='text-danger text-center mt-5'>Request not found!</h4>";
    exit;
}

$request = mysqli_fetch_assoc($req);

// Get Items
$items = mysqli_query($conn, "
    SELECT pri.*, 
           c.name AS category_name, 
           sc.name AS sub_category_name,
           p.name AS product_name, 
           p.unit
    FROM product_request_items pri
    LEFT JOIN category c ON pri.category_id = c.id
    LEFT JOIN sub_category sc ON pri.sub_category_id = sc.id
    LEFT JOIN products p ON pri.product_id = p.id
    WHERE pri.request_id = $id
");
?>
<div class="pcoded-content">
<div class="container mt-4 mb-5">

    <h3>Request Details</h3>
    <hr>

    <div class="row">

        <div class="col-md-6">
            <table class="table table-striped">
                <tr>
                    <th>Request No</th>
                    <td><?= $request['request_no'] ?></td>
                </tr>
                <tr>
                    <th>Requested By</th>
                    <td><?= $request['requested_by'] ?></td>
                </tr>
                <tr>
                    <th>Request Date</th>
                    <td><?= $request['request_date'] ?></td>
                </tr>
                <tr>
                    <th>Remarks</th>
                    <td><?= $request['remarks'] ?: '-' ?></td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-striped">

                <tr>
                    <th>Status</th>
                    <td>
                        <?php if ($request['status'] == 'pending'): ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php elseif ($request['status'] == 'hr_approved'): ?>
                            <span class="badge bg-info">HR Approved</span>
                        <?php elseif ($request['status'] == 'admin_approved'): ?>
                            <span class="badge bg-success">Completed</span>
                        <?php elseif ($request['status'] == 'declined'): ?>
                            <span class="badge bg-danger">Declined</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th>HR Action Date</th>
                    <td><?= $request['hr_action_date'] ?: '-' ?></td>
                </tr>

                <tr>
                    <th>Admin Action Date</th>
                    <td><?= $request['admin_action_date'] ?: '-' ?></td>
                </tr>

            </table>
        </div>

    </div>

    <hr>
    <h4>Requested Items</h4>

    <table class="table table-bordered mt-3">
        <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Category</th>
            <th>Sub Category</th>
            <th>Product</th>
            <th>Requested Qty</th>
            <th>HR Approved</th>
            <th>Admin Approved</th>
            <th>Final Qty</th>
            <th>Status</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $i = 1;
        while ($row = mysqli_fetch_assoc($items)):
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['category_name'] ?: '-' ?></td>
            <td><?= $row['sub_category_name'] ?: '-' ?></td>
            <td><?= $row['product_name'] ?></td>

            <td><?= $row['qty_requested'] . ' ' . $row['unit'] ?></td>
            <td><?= $row['qty_hr_approved'] ?></td>
            <td><?= $row['qty_admin_approved'] ?></td>
            <td><?= $row['final_qty'] ?></td>

            <td>
                <?php if ($row['status'] == 'pending'): ?>
                    <span class="badge bg-warning">Pending</span>
                <?php elseif ($row['status'] == 'hr_approved'): ?>
                    <span class="badge bg-info">HR Approved</span>
                <?php elseif ($row['status'] == 'admin_approved'): ?>
                    <span class="badge bg-success">Done</span>
                <?php elseif ($row['status'] == 'declined'): ?>
                    <span class="badge bg-danger">Declined</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a href="request_list.php" class="btn btn-secondary mt-3">Back</a>
</div>
</div>

<?php 
include(BASE_PATH.'/includes/footer.php');
?>
?>
