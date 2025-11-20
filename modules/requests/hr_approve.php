<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'hr') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch Request
$req = mysqli_query($conn, "SELECT * FROM product_requests WHERE id = $id");
$request = mysqli_fetch_assoc($req);

if (!$request) {
    echo "<script>alert('Request not found');window.location='request_list.php';</script>";
    exit;
}

// Allow only when status: pending OR hr_approved
if (!in_array($request['status'], ['pending', 'hr_approved'])) {
    echo "<script>alert('HR can edit only before Admin approval');window.location='request_list.php';</script>";
    exit;
}

// Fetch all product items
$items = mysqli_query($conn, "
    SELECT pri.*, p.name AS product_name, p.unit 
    FROM product_request_items pri
    LEFT JOIN products p ON pri.product_id = p.id
    WHERE pri.request_id = $id
");
?>
<div class="pcoded-content">
<div class="container mt-4">

    <h3>HR Approval (Request #<?= $request['request_no'] ?>)</h3>

    <form method="post">

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Product</th>
                <th>Requested Qty</th>
                <th>HR Approved Qty</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($row = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_name']) ?> (<?= $row['unit'] ?>)</td>

                    <td><?= $row['qty_requested'] ?></td>

                    <td>
                        <input type="number" class="form-control"
                               name="hr_qty[<?= $row['id'] ?>]"
                               value="<?= ($request['status'] == 'hr_approved') ? $row['qty_hr_approved'] : $row['qty_requested'] ?>"
                               min="0" step="0.01" required>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mb-3">
            <label>Remarks</label>
            <textarea class="form-control" name="remarks" required><?= $request['remarks'] ?></textarea>
        </div>

        <button name="approve" class="btn btn-success">Approve / Update</button>
        <button name="decline" class="btn btn-danger">Decline</button>

    </form>
</div>
</div>

<?php
// -------------------- HR APPROVE LOGIC --------------------
if (isset($_POST['approve'])) {

    // Update all item quantities
    foreach ($_POST['hr_qty'] as $item_id => $qty) {
        $qty = floatval($qty);

        mysqli_query($conn, "
            UPDATE product_request_items 
            SET qty_hr_approved = '$qty', status='hr_approved'
            WHERE id = $item_id
        ");
    }

    // Update status only if pending
    if ($request['status'] == 'pending') {
        $update_status = "status='hr_approved',";
    } else {
        $update_status = ""; // keep status same
    }

    // Update main request
    mysqli_query($conn, "
        UPDATE product_requests 
        SET $update_status hr_action_date = NOW(), remarks = '".mysqli_real_escape_string($conn, $_POST['remarks'])."'
        WHERE id = $id
    ");

    echo "<script>alert('HR Approval Updated Successfully');window.location='request_list.php';</script>";
    exit;
}

// -------------------- HR DECLINE LOGIC --------------------
if (isset($_POST['decline'])) {

    mysqli_query($conn, "
        UPDATE product_requests 
        SET status='hr_declined', hr_action_date = NOW(), remarks = '".mysqli_real_escape_string($conn, $_POST['remarks'])."'
        WHERE id = $id
    ");

    echo "<script>alert('Request Declined by HR');window.location='request_list.php';</script>";
    exit;
}

include(BASE_PATH.'/includes/footer.php');
?>
