<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Access Control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','hr','employee'])) {
    header("Location: ../login.php");
    exit;
}

$role = $_SESSION['role'];

// Build query based on role
if ($role == 'employee') {
    $user_id = intval($_SESSION['user_id']); // Ensure integer
    $query = "
        SELECT pr.*, u.name AS user_name 
        FROM product_requests pr
        LEFT JOIN users u ON pr.request_by = u.id
        WHERE pr.request_by = $user_id
        ORDER BY pr.id DESC
    ";
} else {
    // Admin and HR see all requests
    $query = "
        SELECT pr.*, u.name AS user_name 
        FROM product_requests pr
        LEFT JOIN users u ON pr.request_by = u.id
        ORDER BY pr.id DESC
    ";
}

$res = mysqli_query($conn, $query);
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
        Product Request 
    </h2>
</div>
    <div class="container mt-4">



     
        <!-- <h3>Product Request List</h3> -->

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
                        <?php elseif ($status == 'hr_declined'): ?>
                            <span class="badge bg-danger">HR Declined</span>
                        <?php elseif ($status == 'admin_declined'): ?>
                            <span class="badge bg-danger">Admin Declined</span>
                        <?php endif; ?>
                    </td>

                    <td><?= $row['hr_action_date'] ?: '-' ?></td>
                    <td><?= $row['admin_action_date'] ?: '-' ?></td>

                    <td>
                        <a href="request_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">View</a>

                        <!-- HR can edit only when status is hr_approved -->
                        <?php if ($role == 'hr' && $status == 'hr_approved'): ?>
                            <a href="hr_approve.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit HR Approval</a>
                        <?php endif; ?>

                        <!-- Employee can edit only their own pending requests -->
                        <?php if ($role == 'employee' && $row['request_by'] == $_SESSION['user_id'] && $status == 'pending'): ?>
                            <a href="request_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <?php endif; ?>

                        <!-- HR approval button for pending requests -->
                        <?php if ($role == 'hr' && $status == 'pending'): ?>
                            <a href="hr_approve.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">HR Approve</a>
                        <?php endif; ?>

                        <!-- Admin approval button for hr_approved requests -->
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
