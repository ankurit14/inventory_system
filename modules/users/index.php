<?php
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/users_functions.php');
include(BASE_PATH . '/includes/sidebar.php');

// $allowed_roles = ['admin', 'hr']; // example
// if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
//     header('Location: ../../login.php');
//     exit;
// }
$current_role = $_SESSION['role'];

// Pagination settings:
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total users
$totalRows = get_users_count();
$totalPages = ceil($totalRows / $limit);

// Fetch users with pagination
$users = get_users_paginated($limit, $offset);
?>

<style>
/* ============ CONTENT WRAPPER ============ */
.pcoded-content {}

/* ============ HEADER BOX ============ */
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.header-box h2 { font-size: 24px; font-weight: 600; color: #fff; margin: 0; }
.header-box a.btn {
    color: #1f2937;
    background-color: #fff;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    padding: 6px 15px;
    margin-top: 10px;
}

/* ============ FILTER BOX ============ */
.filter-container {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.filter-container select,
.filter-container input {
    padding: 8px 12px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.filter-container select { flex: 0 0 200px; }
.filter-container input { flex: 1; }

/* ============ TABLE ============ */
.table-wrapper {  width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 8px; }
.table { width: 100%; border-collapse: collapse; min-width: 900px; }
.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    height: 38px;
    padding: 8px 12px;
    text-align: left;
    white-space: nowrap;
}
.table tbody td {
    padding: 6px 12px;
    font-size: 14px;
    white-space: nowrap;
}
.table tbody tr:hover { background: #f1f5ff; }

/* ============ BUTTONS ============ */
.btn-sm { padding: 3px 7px; font-size: 13px; }
.status-btn.btn-sm { min-width: 80px; }

/* ============ RESPONSIVE ============ */
@media (max-width: 768px) {
    .header-box { flex-direction: column; align-items: flex-start; }
    .header-box a.btn { margin-top: 10px; }
    .filter-container { flex-direction: column; }
    .filter-container select,
    .filter-container input { flex: 1 0 100%; }
}
@media (min-width: 992px) {
    .container.mt-2 {
        max-width: 1400px !important;
        margin-left: auto;
        margin-right: auto;
    }
}
</style>

<div class="pcoded-content">

    <!-- Header -->
    <div class="header-box">
        <h2>User List</h2>
        <a href="add.php" class="btn btn-light shadow-sm">+ Add User</a>
    </div>
<div class="container mt-2">
    <!-- Filter -->
    <div class="filter-container">
        <select id="role_filter" class="form-select">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="hr">HR</option>
            <option value="employee">Employee</option>
        </select>
        <input type="text" id="search" class="form-control" placeholder="Search name, username or mobile...">
    </div>

    <!-- Users Table -->
    <div class="table-wrapper">
        <table class="table table-bordered" id="usersTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Mobile</th>
                    <th>Office Mobile</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php $i = $offset + 1; while ($row = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['username'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['contact_no'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['office_mobile_no'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['department'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                    <td><?= strtoupper($row['role'] ?? '') ?></td>
                    <td>
                        <?php if(in_array($current_role, ['admin','hr'])): ?>
                            <button class="status-btn btn btn-sm 
                                    <?= $row['status']=='active'?'btn-success':'btn-secondary' ?>" 
                                    data-userid="<?= $row['id'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </button>
                        <?php else: ?>
                            <?= ucfirst($row['status'] ?? '') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    </div>

    <!-- PAGINATION -->
    <?php $adjacents = 2; ?>

<nav>
    <ul class="pagination justify-content-center">

        <!-- First -->
        <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=1">First</a>
        </li>

        <!-- Previous -->
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= max(1, $page - 1) ?>">« Prev</a>
        </li>

        <?php
        $start = max(1, $page - $adjacents);
        $end   = min($totalPages, $page + $adjacents);

        if ($start > 2) {
            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page=2">2</a></li>';
            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page) ? 'active' : '';
            echo '<li class="page-item '.$active.'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
        }

        if ($end < $totalPages - 1) {
            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page='.($totalPages-1).'">'.($totalPages-1).'</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page='.$totalPages.'">'.$totalPages.'</a></li>';
        }
        ?>

        <!-- Next -->
        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>">Next »</a>
        </li>

        <!-- Last -->
        <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $totalPages ?>">Last</a>
        </li>

    </ul>
</nav>

</div>

<script>
// ----------------------
// REAL-TIME FILTER SCRIPT
// ----------------------
const searchInput = document.getElementById("search");
const roleSelect = document.getElementById("role_filter");
const tableRows = document.querySelectorAll("#usersTable tbody tr");

searchInput.addEventListener("input", applyFilter);
roleSelect.addEventListener("change", applyFilter);

function applyFilter() {
    let search = searchInput.value.toLowerCase();
    let selectedRole = roleSelect.value.toLowerCase();

    tableRows.forEach(row => {
        let name = row.cells[1].textContent.toLowerCase();
        let username = row.cells[2].textContent.toLowerCase();
        let mobile = row.cells[3].textContent.toLowerCase();
        let role = row.cells[6].textContent.toLowerCase();

        let matchSearch =
            name.includes(search) ||
            username.includes(search) ||
            mobile.includes(search);

        let matchRole =
            selectedRole === "" || selectedRole === role;

        row.style.display = (matchSearch && matchRole) ? "" : "none";
    });
}

// ----------------------
// STATUS TOGGLE
// ----------------------
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let userid = this.dataset.userid;
        // let current = this.textContent.toLowerCase();
        let current = this.textContent.trim().toLowerCase();

        let updated = current === "active" ? "inactive" : "active";
        let button = this;

        fetch('update_status.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id='+userid+'&status='+updated
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "success"){
                button.textContent = updated.charAt(0).toUpperCase() + updated.slice(1);

                if(updated === "active"){
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

<?php include(BASE_PATH . '/includes/footer.php'); ?>
