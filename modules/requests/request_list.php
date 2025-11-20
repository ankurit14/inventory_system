<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Access Control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','hr','user'])) {
    header("Location: ../login.php");
    exit;
}

$role = $_SESSION['role'];
?>
<div class="pcoded-content">
<div class="container mt-4">
    <h3>Product Request List</h3>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Request No</th>
            <th>Requested By</th>
            <th>Date</th>
            <th>Status</th>
            <th>HR Approval</th>
            <th>Admin Approval</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $res = mysqli_query($conn, "
            SELECT pr.*, u.name AS user_name 
            FROM product_requests pr
            LEFT JOIN users u ON pr.request_by = u.id
            ORDER BY pr.id DESC
        ");

        $i = 1;
        while ($row = mysqli_fetch_assoc($res)):
            $status = $row['status'];
        ?>

        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['request_no']) ?></td>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= $row['request_date'] ?></td>

            <td>
                <?php if ($status == 'pending'): ?>
                    <span class="badge bg-warning">Pending</span>
                <?php elseif ($status == 'hr_approved'): ?>
                    <span class="badge bg-info">Awaiting Admin</span>
                <?php elseif ($status == 'admin_approved'): ?>
                    <span class="badge bg-success">Completed</span>
                <?php elseif ($status == 'declined'): ?>
                    <span class="badge bg-danger">Declined</span>
                <?php endif; ?>
            </td>

            <td><?= $row['hr_action_date'] ?: '-' ?></td>
            <td><?= $row['admin_action_date'] ?: '-' ?></td>

            <td>
                <a href="request_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">View</a>

                <?php if ($role == 'hr' && $status == 'pending'): ?>
                    <a href="hr_approve.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">HR Approve</a>
                <?php endif; ?>

                <?php if ($role == 'admin' && $status == 'hr_approved'): ?>
                    <a href="admin_approve.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">Admin Approve</a>
                <?php endif; ?>

            </td>

        </tr>

        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>
<?php 
include(BASE_PATH.'/includes/footer.php');
?>
