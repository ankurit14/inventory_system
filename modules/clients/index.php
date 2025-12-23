<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Pagination
$limit = 10;
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Build Query
$where = "WHERE is_deleted = 0";
$params = [];
$types = "";

if ($search !== "") {
    $where .= " AND (name LIKE ? OR company LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    $types = "ssss";
}

if ($status !== "") {
    $where .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

// Count total rows
$count_sql = "SELECT COUNT(*) FROM clients $where";
$stmt = mysqli_prepare($conn, $count_sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $totalRows);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$totalPages = ceil($totalRows / $limit);

// Fetch rows
$sql = "SELECT * FROM clients $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
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
.header-box h2 { font-size: 24px; font-weight: 600; margin:0; color:#fff; }
.header-box a.btn { color:#1f2937; background-color:#fff; font-weight:500; border-radius:6px; text-decoration:none; padding:6px 15px; z-index:10; }

/* Filters */
.filter-container { margin-bottom:12px; }
.filter-container input, .filter-container select { padding:6px 10px; font-size:14px; border-radius:6px; border:1px solid #ccc; }
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 8px;
}
/* Table */
.table thead th { background:#2d6cdf; color:white; font-size:14px; height:38px; padding:5px 8px !important; }
.table tbody td { padding:6px 10px; font-size:14px; }
.table tbody tr:hover { background:#f1f5ff; }

/* Buttons */
.btn-sm { padding:3px 7px; font-size:13px; }
.status-btn.btn-sm { min-width:80px; }

@media (min-width: 992px) {
    .container.mt-2 {
        max-width: 1400px !important;
        margin-left: auto;
        margin-right: auto;
    }
}
</style>

<div class="pcoded-content">
    <div class="header-box">
        <h2>Clients</h2>
        <div>
            <a href="add.php" class="btn btn-light btn-sm">+ Add Client</a>
            <!-- <a href="trash.php" class="btn btn-dark btn-sm">Trash</a> -->
        </div>
    </div>

    <div class="container mt-2">
        <!-- Filters -->
        <form class="row mb-3">
            <div class="col-md-4">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search client..." class="form-control">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="Active" <?= $status=="Active"?"selected":"" ?>>Active</option>
                    <option value="Inactive" <?= $status=="Inactive"?"selected":"" ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    <div class="table-responsive">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Business</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $offset + 1; while($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['company']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                       <td><?= date("d-M-Y", strtotime($row['created_at'])) ?></td>
                    <td>
                        <button class="status-btn btn btn-sm <?= $row['status']=="Active"?"btn-success":"btn-secondary" ?>" 
                                data-id="<?= $row['id'] ?>">
                            <?= $row['status'] ?>
                        </button>
                    </td>
                 
                    <td>
                        <!-- <button onclick="openViewModal(<?= $row['id'] ?>)" class="btn btn-info btn-sm">View</button> -->
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Edit</a>
                        <form method="post" action="delete.php" style="display:inline-block" onsubmit="return confirm('Move client to trash?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <!-- <button type="submit" class="btn btn-danger btn-sm">Delete</button> -->
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($i == $offset+1): ?>
                    <tr><td colspan="8" class="text-center">No clients found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>  

        <!-- Pagination -->
       <nav>
    <ul class="pagination justify-content-center">

        <!-- First Page -->
        <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&status=<?= $status ?>">First</a>
        </li>

        <!-- Previous -->
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">« Prev</a>
        </li>

        <?php
        // Page range calculation
        $start = max(1, $page - $adjacents);
        $end   = min($totalPages, $page + $adjacents);

        // Always show page 1 & 2 if far
        if ($start > 2) {
            echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&status='.$status.'">1</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page=2&search='.urlencode($search).'&status='.$status.'">2</a></li>';
            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
        }

        // Main page loop
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page) ? 'active' : '';
            echo '<li class="page-item '.$active.'"><a class="page-link" href="?page='.$i.'&search='.urlencode($search).'&status='.$status.'">'.$i.'</a></li>';
        }

        // Last pages
        if ($end < $totalPages - 1) {
            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page='.($totalPages-1).'&search='.urlencode($search).'&status='.$status.'">'.($totalPages-1).'</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page='.$totalPages.'&search='.urlencode($search).'&status='.$status.'">'.$totalPages.'</a></li>';
        }
        ?>

        <!-- Next -->
        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">Next »</a>
        </li>

        <!-- Last Page -->
        <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">Last</a>
        </li>

    </ul>
</nav>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4>Client Details</h4>
                <button class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBody">Loading...</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// View Modal
function openViewModal(id){
    var modalEl = document.getElementById('viewModal');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();

    var viewBody = document.getElementById('viewBody');
    viewBody.innerHTML = '<div class="text-center py-3">Loading...</div>';

    fetch('view.php?id=' + encodeURIComponent(id))
        .then(response => response.text())
        .then(html => viewBody.innerHTML = html)
        .catch(err => {
            viewBody.innerHTML = '<div class="alert alert-danger">Error loading client details.</div>';
            console.error(err);
        });
}

// Status toggle
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        let currentStatus = this.textContent.trim();
        let newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
        let button = this;

        fetch('update_status.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent(newStatus) + '&type=client'
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === 'success') {
                button.textContent = newStatus;
                if(newStatus === 'Active'){
                    button.classList.remove('btn-secondary');
                    button.classList.add('btn-success');
                } else {
                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                }
            } else {
                alert('Status update failed!');
            }
        });
    });
});
</script>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
