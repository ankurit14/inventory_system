<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
include('sub_category_functions.php');

$subcategories = get_all_subcategories();
$current_role = $_SESSION['role'];
?>

<style>
/* Same styling as Category Module */
.header-box {
    background-color: #1f2937;
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.header-box h2 {
    font-size: 24px;
    font-weight: 600;
    margin: 0;
    color: #fff;
}
.header-box a.btn {
    color: #1f2937;
    background-color: #fff;
    font-weight: 500;
    border-radius: 6px;
    padding: 6px 15px;
    text-decoration: none;
}

/* Filters */
.filter-container input,
.filter-container select {
    padding: 6px 10px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* Table */
.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    height: 34px;
    padding: 5px 8px !important;
}
.table tbody td {
    padding: 6px 10px;
    font-size: 14px;
}

/* Buttons */
.btn-sm {
    padding: 3px 7px;
    font-size: 13px;
}
.status-btn.btn-sm {
    min-width: 80px;
}
</style>


<div class="pcoded-content">

    <!-- HEADER -->
    <div class="header-box">
        <h2>Sub Category List</h2>
        <a href="add.php" class="btn btn-light btn-lg">+ Add Sub Category</a>
    </div>

    <!-- FILTERS -->
    <div class="filter-container row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" id="search" class="form-control" placeholder="Search sub category...">
        </div>
        <div class="col-md-3">
            <select id="status_filter" class="form-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- TABLE -->
    <table class="table table-bordered" id="subCategoryTable">
        <thead>
            <tr>
                <th style="height:32px;">#</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Description</th>
                <th>Status</th>
                <th width="140">Action</th>
            </tr>
        </thead>

        <tbody>
            <?php $i = 1; while($row = mysqli_fetch_assoc($subcategories)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>
                <?php if(in_array($current_role,['admin','hr'])): ?>
                    <button class="status-btn btn btn-sm 
                        <?= $row['status']=='active'?'btn-success':'btn-secondary' ?>"
                        data-id="<?= $row['id'] ?>">
                        <?= ucfirst($row['status']) ?>
                    </button>
                <?php else: ?>
                    <?= ucfirst($row['status']) ?>
                <?php endif; ?>
                </td>

                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>

                    <a href="delete.php?id=<?= $row['id'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this sub category?')">Delete</a>
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

    document.querySelectorAll("#subCategoryTable tbody tr").forEach(row => {
        let cat = row.cells[1].textContent.toLowerCase();
        let subcat = row.cells[2].textContent.toLowerCase();
        let desc = row.cells[3].textContent.toLowerCase();
        let stat = row.cells[4].textContent.toLowerCase();

        let matchSearch = cat.includes(search) || subcat.includes(search) || desc.includes(search);
        let matchStatus = (status === "" || stat === status);

        row.style.display = (matchSearch && matchStatus) ? "" : "none";
    });
}


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

                button.textContent = 
                    newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                button.classList.toggle('btn-success');
                button.classList.toggle('btn-secondary');

            } else {
                alert("Status update failed!");
            }
        });

    });
});
</script>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
