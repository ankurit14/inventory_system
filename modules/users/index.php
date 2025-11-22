<?php
include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/path.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/users_functions.php');
include(BASE_PATH . '/includes/sidebar.php');

$users = get_all_users();
$current_role = $_SESSION['role']; // login user role
?>

<style>
/* Header */
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
    text-decoration: none;
    padding: 6px 15px;
    z-index: 10;
}

/* Filter Box */
.filter-container {
    margin-bottom: 12px;
}
.filter-container input, .filter-container select {
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
    height: 38px;
    padding: 5px 8px !important;
}
.table tbody td {
    padding: 6px 10px;
    font-size: 14px;
}
.table tbody tr:hover {
    background: #f1f5ff;
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



 <div class="header-box">
        <h2>User List</h2>
        <a href="add.php" class="btn btn-light shadow-sm">+ Add User</a>
    </div>


<!--  -->





    

    <!-- Header Row -->
    <!-- <div class="header-box">
        <h2>User List</h2>
        <a href="add.php" class="btn btn-light btn-lg shadow-sm">+ Add User</a>
    </div> -->

    <!-- Filter -->
    <div class="filter-container row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" id="search" class="form-control" placeholder="Search name, username or mobile...">
        </div>
        <div class="col-md-3">
            <select id="role_filter" class="form-select">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="hr">HR</option>
                <option value="employee">Employee</option>
            </select>
        </div>
    </div>

    <!-- Users Table -->
    <table class="table table-bordered" id="usersTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Username</th>
                <th>Mobile</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php $i = 1; while ($row = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['contact_no']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><?= htmlspecialchars($row['designation']) ?></td>
                <td><?= strtoupper($row['role']) ?></td>
                <td>
                    <?php if(in_array($current_role, ['admin','hr'])): ?>
                        <button class="status-btn btn btn-sm <?= $row['status']=='active'?'btn-success':'btn-secondary' ?>" 
                                data-userid="<?= $row['id'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </button>
                    <?php else: ?>
                        <?= ucfirst($row['status']) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<script>
// SEARCH + ROLE FILTER
document.getElementById("search").addEventListener("keyup", filterTable);
document.getElementById("role_filter").addEventListener("change", filterTable);

function filterTable() {
    let search = document.getElementById("search").value.toLowerCase();
    let role = document.getElementById("role_filter").value.toLowerCase();

    document.querySelectorAll("#usersTable tbody tr").forEach(row => {
        let name = row.cells[1].textContent.toLowerCase();
        let username = row.cells[2].textContent.toLowerCase();
        let mobile = row.cells[3].textContent.toLowerCase();
        let userRole = row.cells[6].textContent.toLowerCase();

        let matchSearch = name.includes(search) || username.includes(search) || mobile.includes(search);
        let matchRole = role === "" || userRole === role;

        row.style.display = (matchSearch && matchRole) ? "" : "none";
    });
}

// STATUS TOGGLE BUTTON
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let userid = this.dataset.userid;
        let currentStatus = this.textContent.toLowerCase();
        let newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        let button = this;

        fetch('update_status.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id='+userid+'&status='+newStatus
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === 'success') {
                button.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                button.classList.toggle('btn-success');
                button.classList.toggle('btn-secondary');
            } else {
                alert('Status update failed!');
            }
        });
    });
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
