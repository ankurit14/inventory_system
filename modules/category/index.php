<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/includes/category_functions.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

$categories = get_all_categories();
$current_role = $_SESSION['role']; // login user role
?>

<style>
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
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

</style>

<div class="pcoded-content">

    <!-- Header -->
    <div class="header-box">
        <h2>Category List</h2>
        <a href="add.php" class="btn btn-light shadow-sm">+ Add Category</a>
    </div>

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
                <td><?= htmlspecialchars($row['description']) ?></td>

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
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sure to delete?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

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

// STATUS TOGGLE (Same as users module)
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

                if(newStatus === "active") {
                    button.classList.remove("btn-secondary");
                    button.classList.add("btn-success");
                } else {
                    button.classList.remove("btn-success");
                    button.classList.add("btn-secondary");
                }

            } else {
                alert("Status update failed!");
            }
        });
    });
});
</script>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
