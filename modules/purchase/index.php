<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
include('purchase_functions.php');

$purchases = get_all_purchases();
?>

<style>
.table th, .table td { padding: 4px 8px !important; font-size: 13px; }
.header-box { background:#1f2937; padding:15px 20px; border-radius:8px; display:flex; justify-content:space-between; }
.header-box h2 { color:#fff; margin:0; font-size:22px; }
.btn-sm { padding:2px 6px; font-size:12px; }
.table thead th { background:#2d6cdf; color:#fff; }
</style>

<div class="pcoded-content">

    <div class="header-box mb-3">
        <h2>Purchase List</h2>
        <a href="add.php" class="btn btn-light">+ Add Purchase</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Supplier</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th width="140">Action</th>
        </tr>
        </thead>

        <tbody>
        <?php $i=1; while($row = mysqli_fetch_assoc($purchases)): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['supplier_name'] ?></td>
            <td><?= $row['purchase_date'] ?></td>
            <td>₹ <?= number_format($row['total_amount'],2) ?></td>
            <td>
                <span class="badge bg-<?= $row['status']=='completed' ? 'success' : 'secondary' ?>">
                    <?= ucfirst($row['status']) ?>
                </span>
            </td>
            <td>
                <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">View</a>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this purchase?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
