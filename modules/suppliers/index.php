<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
include('supplier_functions.php');

$suppliers = get_all_suppliers();
$current_role = $_SESSION['role'] ?? 'user';
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
        <h2>Suppliers</h2>
        <a href="add.php" class="btn btn-light">+ Add Supplier</a>
    </div>

    <table class="table table-bordered table-striped" id="supplierTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Status</th>
                <th width="130">Action</th>
            </tr>
        </thead>

        <tbody>
        <?php $i=1; while($row = mysqli_fetch_assoc($suppliers)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <?php if(in_array($current_role, ['admin','hr'])): ?>
                        <button class="status-btn btn btn-sm 
                            <?= $row['status']=='active'?'btn-success':'btn-secondary' ?>"
                            data-id="<?= $row['id'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </button>
                    <?php else: ?>
                        <span class="badge bg-<?= $row['status']=='active'?'success':'secondary' ?>">
                            <?= $row['status'] ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    <a onclick="return confirm('Delete supplier?')" 
                       href="delete.php?id=<?= $row['id'] ?>" 
                       class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<script>
// STATUS TOGGLE BUTTON
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        let currentStatus = this.textContent.toLowerCase();
        let newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        let button = this;

        fetch('toggle_status.php?id=' + id)
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "success") {
                button.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                button.classList.toggle('btn-success');
                button.classList.toggle('btn-secondary');
            } else {
                alert("Status update failed!");
            }
        });
    });
});
</script>
