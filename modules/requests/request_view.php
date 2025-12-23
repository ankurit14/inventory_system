<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

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
                    <th>Requested By</th>
                    <td><?= htmlspecialchars($request['requested_by']); ?></td>
                </tr>
                <tr>
                    <th>Request Date</th>
                    <td><?= htmlspecialchars($request['request_date']); ?></td>
                </tr>
                <tr>
                    <th>Request For</th>
                    <td><?= htmlspecialchars($request['request_for'] ?: '-') ?></td>
                </tr>
                <tr>
                    <th>Remarks</th>
                    <td><?= htmlspecialchars($request['remarks'] ?: '-') ?></td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-striped">
                <tr>
                    <th>Status</th>
                    <td>
                        <?php 
                        switch ($request['status']) {
                            case 'pending':
                                echo '<span class="badge bg-warning">Pending</span>';
                                break;
                            case 'hr_approved':
                                echo '<span class="badge bg-info">HR Approved</span>';
                                break;
                            case 'admin_approved':
                                echo '<span class="badge bg-success">Completed</span>';
                                break;
                            case 'declined':
                                echo '<span class="badge bg-danger">Declined</span>';
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>HR Action Date</th>
                    <td><?= htmlspecialchars($request['hr_action_date'] ?: '-') ?></td>
                </tr>
                <tr>
                    <th>Admin Action Date</th>
                    <td><?= htmlspecialchars($request['admin_action_date'] ?: '-') ?></td>
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
            <td><?= htmlspecialchars($row['category_name'] ?: '-') ?></td>
            <td><?= htmlspecialchars($row['sub_category_name'] ?: '-') ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= htmlspecialchars($row['qty_requested'] . ' ' . $row['unit']) ?></td>
            <td><?= htmlspecialchars($row['qty_hr_approved'] ?: 0) ?></td>
            <td><?= htmlspecialchars($row['qty_admin_approved'] ?: 0) ?></td>
            <td><?= htmlspecialchars($row['final_qty'] ?: 0) ?></td>
            <td>
                <?php
                switch ($row['status']) {

    case 'pending':
        echo '<span class="badge bg-warning">Pending</span>';
        break;

    case 'hr_approved':
        echo '<span class="badge bg-info">HR Approved</span>';
        break;

    case 'hr_declined':
        echo '<span class="badge bg-danger">HR Declined</span>';
        break;

    case 'admin_approved':
        echo '<span class="badge bg-success">Admin Approved</span>';
        break;

    case 'admin_declined':
        echo '<span class="badge bg-danger">Admin Declined</span>';
        break;

    default:
     
        echo '<span class="badge bg-secondary">Unknown</span>';
        break;
}

                ?>
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
