<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH.'/includes/category_functions.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

$categories = get_all_categories();
$current_role = $_SESSION['role']; // login user role
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

    <!-- Header -->
    <div class="header-box">
        <h2>Category List</h2>
        <a href="add.php" class="btn btn-light shadow-sm">+ Add Category</a>
    </div>

    <!-- MESSAGE SECTION -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'used'): ?>
            <div class="alert alert-danger alert-msg">❌ This category cannot be deleted because it is used in Sub Categories.</div>
        <?php elseif ($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-msg">✔ Category deleted successfully.</div>
        <?php elseif ($_GET['msg'] == 'error'): ?>
            <div class="alert alert-danger alert-msg">❌ Error deleting category.</div>
        <?php elseif ($_GET['msg'] == 'invalid'): ?>
            <div class="alert alert-warning alert-msg">⚠ Invalid category ID.</div>
        <?php endif; ?>
    <?php endif; ?>
    <!-- END MESSAGE SECTION -->

    <!-- Filter Section -->
    <div class="filter-container row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" id="search" class="form-control" placeholder="Search category name...">
        </div>
        <div class="col-md-3">
            <select id="status_filter" class="form-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Category Table -->
    <table class="table table-bordered" id="categoryTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php $i=1; while($row=mysqli_fetch_assoc($categories)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description'] ?? '') ?></td>

                <td>
                    <?php if(in_array($current_role, ['admin','hr'])): ?>
                        <button 
                            class="status-btn btn btn-sm <?= $row['status']=='active'?'btn-success':'btn-secondary' ?>" 
                            data-id="<?= $row['id'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </button>
                    <?php else: ?>
                        <?= ucfirst($row['status']) ?>
                    <?php endif; ?>
                </td>

                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    <!-- <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sure to delete?')">Delete</a> -->
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// SEARCH + FILTER
document.getElementById("search").addEventListener("keyup", filterTable);
document.getElementById("status_filter").addEventListener("change", filterTable);

function filterTable() {
    let search = document.getElementById("search").value.toLowerCase();
    let status = document.getElementById("status_filter").value.toLowerCase();

    document.querySelectorAll("#categoryTable tbody tr").forEach(row => {
        let name = row.cells[1].textContent.toLowerCase();
        let desc = row.cells[2].textContent.toLowerCase();
        let catStatus = row.cells[3].textContent.toLowerCase();

        let matchSearch = name.includes(search) || desc.includes(search);
        let matchStatus = status === "" || catStatus === status;

        row.style.display = (matchSearch && matchStatus) ? "" : "none";
    });
}

// STATUS UPDATE
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function() {

        let id = this.dataset.id;
        let currentStatus = this.textContent.trim().toLowerCase();
        let newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        let button = this;

        fetch("toggle_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id + "&status=" + newStatus
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "success") {
                button.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                button.classList.toggle("btn-success");
                button.classList.toggle("btn-secondary");
            } else {
                alert("Status update failed!");
            }
        });
    });
});

// AUTO HIDE ALERTS AFTER 2 SECONDS
$(document).ready(function(){
    $(".alert-msg").delay(2000).fadeOut(500);
});
</script>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
