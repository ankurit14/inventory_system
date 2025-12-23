<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Pagination Settings
$limit = 10;
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total rows
$count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM other_receivers"));
$totalRows = $count['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch records
$res = mysqli_query($conn, "SELECT * FROM other_receivers ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>
<style>
    /* Reduce table row height */
.table th,
.table td {
    padding: 4px 8px !important;  /* adjust top/bottom and left/right padding */
    vertical-align: middle;       /* optional, to center content vertically */
    font-size: 13px;              /* slightly smaller font if needed */
}

/* Optional: reduce button size to match smaller rows */
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
        <h2>Other Receivers</h2>
        <a href="add.php" class="btn btn-light shadow-sm">+ Add New Receiver</a>
    </div>

<div class="table-responsive">
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    <?php $i = $offset + 1; while($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['mobile']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= $row['status'] ? 'Active' : 'Inactive' ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                <!-- <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</a> -->
            </td>
        </tr>
    <?php endwhile; ?>

    <?php if($i == $offset + 1): ?>
        <tr><td colspan="6" class="text-center">No records found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>

<!-- PAGINATION -->
<?php $adjacents = 2; ?>

<nav>
<ul class="pagination justify-content-center">

    <!-- First -->
    <li class="page-item <?= ($page == 1 ? 'disabled' : '') ?>">
        <a class="page-link" href="?page=1">First</a>
    </li>

    <!-- Prev -->
    <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
        <a class="page-link" href="?page=<?= $page - 1 ?>">« Prev</a>
    </li>

    <?php
    $start = max(1, $page - $adjacents);
    $end   = min($totalPages, $page + $adjacents);

    if ($start > 2) {
        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
        echo '<li class="page-item"><a class="page-link" href="?page=2">2</a></li>';
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        echo '<li class="page-item '.($i == $page ? 'active' : '').'">
                <a class="page-link" href="?page='.$i.'">'.$i.'</a>
              </li>';
    }

    if ($end < $totalPages - 1) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        echo '<li class="page-item"><a class="page-link" href="?page='.($totalPages-1).'">'.($totalPages-1).'</a></li>';
        echo '<li class="page-item"><a class="page-link" href="?page='.$totalPages.'">'.$totalPages.'</a></li>';
    }
    ?>

    <!-- Next -->
    <li class="page-item <?= ($page >= $totalPages ? 'disabled' : '') ?>">
        <a class="page-link" href="?page=<?= $page + 1 ?>">Next »</a>
    </li>

    <!-- Last -->
    <li class="page-item <?= ($page == $totalPages ? 'disabled' : '') ?>">
        <a class="page-link" href="?page=<?= $totalPages ?>">Last</a>
    </li>

</ul>
</nav>

</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
