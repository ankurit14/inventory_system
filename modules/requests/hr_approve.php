<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'hr') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

$req = mysqli_query($conn, "SELECT * FROM product_requests WHERE id = $id");
$request = mysqli_fetch_assoc($req);

$items = mysqli_query($conn, "
    SELECT pri.*, p.name AS product_name, p.unit 
    FROM product_request_items pri
    LEFT JOIN products p ON pri.product_id = p.id
    WHERE pri.request_id = $id
");
?>
<div class="pcoded-content">
<div class="container mt-4">
    <h3>HR Approval</h3>
    <form method="post">

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Product</th>
                <th>Requested Qty</th>
                <th>Approve Qty</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($row = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= $row['qty_requested'] ?></td>

                    <td>
                        <input type="number" class="form-control"
                               name="hr_qty[<?= $row['id'] ?>]"
                               value="<?= $row['qty_requested'] ?>" min="0" step="0.01">
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mb-3">
            <label>Remarks</label>
            <textarea class="form-control" name="remarks" required></textarea>
        </div>

        <button name="approve" class="btn btn-success">Approve</button>
        <button name="decline" class="btn btn-danger">Decline</button>
    </form>
</div>
</div>
<?php
if (isset($_POST['approve'])) {

    foreach ($_POST['hr_qty'] as $item_id => $qty) {
        mysqli_query($conn, "
            UPDATE product_request_items 
            SET qty_hr_approved = '$qty', status='hr_approved'
            WHERE id = $item_id
        ");
    }

    mysqli_query($conn, "
        UPDATE product_requests 
        SET status='hr_approved', hr_action_date=NOW(), remarks='{$_POST['remarks']}'
        WHERE id = $id
    ");

    echo "<script>alert('HR Approved Successfully');window.location='request_list.php';</script>";
}

if (isset($_POST['decline'])) {

    mysqli_query($conn, "
        UPDATE product_requests 
        SET status='declined', hr_action_date=NOW(), remarks='{$_POST['remarks']}'
        WHERE id = $id
    ");

    echo "<script>alert('Request Declined');window.location='request_list.php';</script>";
}

include(BASE_PATH.'/includes/footer.php');
?>
