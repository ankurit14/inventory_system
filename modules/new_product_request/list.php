<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// simple role check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','purchase','hr'])) {
    echo "<div class='pcoded-content'><div class='card'><div class='card-body'><h4>Access denied</h4></div></div></div>";
    include(BASE_PATH.'/includes/footer.php');
    exit;
}

// fetch requests
$query = "
    SELECT npr.*, u.name as requester_name
    FROM new_product_requests npr
    LEFT JOIN users u ON u.id = npr.requested_by
    ORDER BY npr.created_at DESC
";
$res = mysqli_query($conn, $query);
?>
<style>
.table th, .table td {
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
.status-btn.btn-sm { min-width: 80px; }
</style>

<div class="pcoded-content">
    <div class="header-box">
        <h2>New Product Requests</h2>
        <a href="add.php" class="btn btn-light">+ Add Product</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Qty</th>
                        <th>Reason</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="min-width:200px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while ($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlentities($row['product_name']) ?></td>
                        <td><?= intval($row['qty']) ?></td>
                        
                        
                        
                        <!-- <td><?= nl2br(htmlentities($row['reason'])) ?></td> -->



                        <td>
    <!-- Main Reason (Normal) -->
    <?php if (!empty($row['reason'])): ?>
        <div>
            <strong>Reason:</strong><br>
            <?= nl2br(htmlentities($row['reason'])) ?>
        </div>
    <?php endif; ?>

    <!-- Rejected Reason (Highlighted Separately) -->
    <?php if ($row['status'] === 'rejected' && stripos($row['reason'], 'Rejected Reason:') !== false): ?>
        <?php 
            // extract only the rejected reason
            $parts = explode('Rejected Reason:', $row['reason']);
            $reject_reason = trim($parts[1] ?? '');
        ?>
        <div style="margin-top:8px; padding:8px; background:#ffe3e3; border-left:4px solid #ff0000; color:#900; border-radius:3px;">
            <strong>Rejected Reason:</strong><br>
            <?= nl2br(htmlentities($reject_reason)) ?>
        </div>
    <?php endif; ?>
</td>


                        
                        
                        
                        
                        
                        
                        <td><?= htmlentities($row['requester_name'] ?? 'Unknown') ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <!-- <a class="btn btn-sm btn-primary" href="/inventory_system/modules/new_product_request/approve.php?id=<?= $row['id'] ?>">Approve & Add</a> -->


                                <a class="btn btn-sm btn-primary" 
   href="<?php echo BASE_URL . 'modules/new_product_request/approve.php?id=' . $row['id']; ?>">
   Approve & Add
</a>


                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="openRejectModal(<?= $row['id'] ?>)">
                                    Reject
                                </button>

                            <?php else: ?>
                                <?php if ($row['status'] === 'approved' && $row['product_id']): ?>
                                    <span class="badge bg-success">Product has been added successfully</span>
                                <?php else: ?>
                                    <span class="text-muted">No actions</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- REJECT MODAL -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
<form action="<?php echo BASE_URL . 'modules/new_product_request/reject.php'; ?>" method="post">

      <!-- <form action="/inventory_system/modules/new_product_request/reject.php" method="post"> -->
        <div class="modal-header">
          <h4 class="modal-title">Reject Request</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="reject_id">

          <label>Reason (optional)</label>
          <textarea name="reason" class="form-control" rows="3"
            placeholder="Enter rejection reason (optional)"></textarea>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Submit Reject</button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<!-- REQUIRED BOOTSTRAP JS (THIS FIXES YOUR MODAL ISSUE) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function openRejectModal(id) {
    document.getElementById('reject_id').value = id;
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
