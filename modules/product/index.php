<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
include('product_functions.php');

$products = get_all_products();
$current_role = $_SESSION['role'] ?? 'user';
?>

<style>
    /* Table styling like supplier module */
.table th,
.table td {
    padding: 4px 8px !important;
    vertical-align: middle;
    font-size: 13px;
}

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
        <h2>Products</h2>
        <a href="add.php" class="btn btn-light">+ Add Product</a>
    </div>

    <table class="table table-bordered table-striped" id="productTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Unit</th>
                <th>Status</th>
                <th width="150">Action</th>
            </tr>
        </thead>

        <tbody>
        <?php $i=1; while($row = mysqli_fetch_assoc($products)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= htmlspecialchars($row['sub_category_name']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td><?= htmlspecialchars($row['unit']) ?></td>
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
                    <a onclick="return confirm('Delete product?')" 
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

        fetch('toggle_status.php?id=' + id + '&type=product') // type=product to differentiate in toggle_status.php
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
    