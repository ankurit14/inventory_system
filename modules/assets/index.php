<?php
// modules/assets/index.php
session_start();
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/config/db.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

// ================= FILTERS =================
$search_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$category    = isset($_GET['category']) ? trim($_GET['category']) : '';
$status      = isset($_GET['status']) ? trim($_GET['status']) : '';
$floor       = isset($_GET['floor']) ? trim($_GET['floor']) : '';

// ================= PAGINATION SETUP =================
$limit = 10; // Records per page
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// ================= BUILD QUERY =================
$where  = " WHERE 1=1 ";
$params = [];
$types  = "";

// asset name
if ($search_name !== '') {
    $where .= " AND asset_name LIKE ? ";
    $params[] = "%$search_name%";
    $types .= "s";
}

// category
if ($category !== '') {
    $where .= " AND category = ? ";
    $params[] = $category;
    $types .= "s";
}

// status
if ($status !== '') {
    $where .= " AND status = ? ";
    $params[] = $status;
    $types .= "s";
}

// floor
if ($floor !== '') {
    $where .= " AND floor = ? ";
    $params[] = $floor;
    $types .= "s";
}

// ================= TOTAL COUNT FOR PAGINATION =================
$count_sql = "SELECT COUNT(*) as total FROM company_assets $where";
$count_stmt = mysqli_prepare($conn, $count_sql);
if($types !== ''){
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_res = mysqli_stmt_get_result($count_stmt);
$totalRows = mysqli_fetch_assoc($count_res)['total'];
$totalPages = ceil($totalRows / $limit);

// ================= MAIN DATA =================
$sql = "SELECT * FROM company_assets $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);

if($types === ''){
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
} else {
    // bind types + limit + offset
    $bind_types = $types . "ii";
    $bind_params = array_merge($params, [$limit, $offset]);
    mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_params);
}

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

// ================= FILTER DROPDOWNS =================
$cats_res = mysqli_query(
    $conn,
    "SELECT DISTINCT category FROM company_assets WHERE category IS NOT NULL AND category<>''"
);

$floors_res = mysqli_query(
    $conn,
    "SELECT DISTINCT floor FROM company_assets WHERE floor IS NOT NULL AND floor<>'' ORDER BY floor"
);

$stat_res = mysqli_query(
    $conn,
    "SELECT 'Available' AS s UNION SELECT 'In Use' UNION SELECT 'Maintenance' UNION SELECT 'Lost' UNION SELECT 'Scrap'"
);
?>

<style>
.header-box{
    background:linear-gradient(135deg,#4e73df,#1cc88a);
    padding:15px 20px;
    border-radius:8px;
    margin-bottom:20px;
}
.header-box h2{
    color:#fff;
    margin:0;
    font-size:24px;
    font-weight:600;
}
.asset-thumb{
    width:40px;
    height:40px;
    object-fit:cover;
    border-radius:50%;
    cursor:pointer;
}
.table thead th{
    background:#2d6cdf;
    color:#fff;
}
.btn-close{
    font-size:1.2rem;
    font-weight:bold;
    color:#fff;
}

/* ===== Professional Pagination ===== */
.pagination-modern .page-link {
    border-radius: 4px !important;
    margin: 0 2px;
    padding: 6px 12px;
    color: #2d6cdf;
    border: 1px solid #dee2e6;
    font-weight: 500;
    transition: all 0.2s ease;
}
.pagination-modern .page-link:hover {
    background: #2d6cdf;
    color: #fff;
}
.pagination-modern .page-item.active .page-link {
    background: linear-gradient(135deg,#4e73df,#1cc88a);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.pagination-modern .page-item.disabled .page-link {
    color: #aaa;
    background: #f8f9fa;
    cursor: not-allowed;
}
</style>

<div class="pcoded-content">
    <div class="header-box">
        <h2>Company Assets</h2>
    </div>

    <div class="container mt-4">

        <!-- ================= FILTER FORM ================= -->
        <form method="get" class="row g-2 mb-3">

            <div class="col-md-3">
                <label class="form-label">Asset Name</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($search_name) ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category" class="form-control">
                    <option value="">All</option>
                    <?php while ($c = mysqli_fetch_assoc($cats_res)): ?>
                        <option value="<?= htmlspecialchars($c['category']) ?>"
                            <?= ($category === $c['category']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['category']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Floor</label>
                <select name="floor" class="form-control">
                    <option value="">All</option>
                    <?php while ($f = mysqli_fetch_assoc($floors_res)): ?>
                        <option value="<?= htmlspecialchars($f['floor']) ?>"
                            <?= ($floor === $f['floor']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['floor']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <?php while ($s = mysqli_fetch_assoc($stat_res)): ?>
                        <option value="<?= $s['s'] ?>"
                            <?= ($status === $s['s']) ? 'selected' : '' ?>>
                            <?= $s['s'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100">Apply</button>
            </div>

            <div class="col-md-1 align-self-end text-end">
                <a href="add.php" class="btn btn-success w-100">Add</a>
            </div>

        </form>

        <!-- ================= TABLE ================= -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Bill</th>
                    <th>Asset</th>
                    <th>Category</th>
                    <th>Floor</th>
                    <th>Serial</th>
                    <th>Location</th>
                    <th>Assigned</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php $i = $offset + 1; while ($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <?php
                            $imgPath = BASE_PATH.'/uploads/assets/'.$row['bill_image'];
                            $urlPath = BASE_URL.'uploads/assets/'.$row['bill_image'];
                            if ($row['bill_image'] && file_exists($imgPath)):
                            ?>
                                <img src="<?= $urlPath ?>" class="asset-thumb"
                                     onclick="openImageModal('<?= $urlPath ?>')">
                            <?php else: ?>-<?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['asset_name']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= htmlspecialchars($row['floor']) ?: '-' ?></td>
                        <td><?= htmlspecialchars($row['serial_no']) ?: '-' ?></td>
                        <td><?= htmlspecialchars($row['current_location']) ?: '-' ?></td>
                        <td><?= htmlspecialchars($row['assigned_to']) ?: '-' ?></td>
                        <td><?= $row['purchase_price'] ? '₹ '.number_format($row['purchase_price'],2) : '-' ?></td>
                        <td><?= $row['qty'] ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <button class="btn btn-info btn-sm"
                                onclick='openViewModal(<?= json_encode($row['id']) ?>)'>
                                View
                            </button>
                            <a href="edit.php?id=<?= $row['id'] ?>"
                               class="btn btn-warning btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($i === $offset+1): ?>
                    <tr><td colspan="12" class="text-center">No records found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ================= PROFESSIONAL PAGINATION ================= -->
        <?php if($totalPages > 1): ?>
        <nav class="mt-4">
        <ul class="pagination justify-content-center pagination-modern">

            <!-- First -->
            <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>1])) ?>">First</a>
            </li>

            <!-- Prev -->
            <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">‹ Prev</a>
            </li>

            <!-- Page Numbers -->
            <?php
            $start = max(1, $page-2);
            $end   = min($totalPages, $page+2);
            for($p=$start;$p<=$end;$p++):
            ?>
            <li class="page-item <?= ($p==$page)?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">Next ›</a>
            </li>

            <!-- Last -->
            <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$totalPages])) ?>">Last</a>
            </li>

        </ul>
        </nav>
        <?php endif; ?>

    </div>
</div>

<!-- ================= VIEW MODAL ================= -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">Asset Details</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
            </div>
            <div class="modal-body" id="viewBody">Loading...</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="editLink" class="btn btn-warning">Edit</a>
            </div>
        </div>
    </div>
</div>

<!-- ================= IMAGE MODAL WITH PRINT ================= -->
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h4 class="modal-title">Bill Image</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalImage" src="" class="img-fluid">
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" onclick="printModalImage()">Print</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openViewModal(id){
    const modal = new bootstrap.Modal(document.getElementById('viewModal'));
    modal.show();
    document.getElementById('viewBody').innerHTML = 'Loading...';

    fetch('view.php?id=' + encodeURIComponent(id))
        .then(r => r.text())
        .then(html => {
            document.getElementById('viewBody').innerHTML = html;
            document.getElementById('editLink').href = 'edit.php?id=' + id;
        });
}

function openImageModal(url){
    const imgModal = new bootstrap.Modal(document.getElementById('imageModal'));
    document.getElementById('modalImage').src = url;
    imgModal.show();
}

function printModalImage(){
    const imgSrc = document.getElementById('modalImage').src;
    const w = window.open('');
    w.document.write('<img src="'+imgSrc+'" style="width:100%">');
    w.document.close();
    w.focus();
    w.print();
    w.close();
}
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
