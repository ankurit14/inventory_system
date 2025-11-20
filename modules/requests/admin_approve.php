<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch request
$req = mysqli_query($conn, "SELECT * FROM product_requests WHERE id=$id");
$request = mysqli_fetch_assoc($req);

// Fetch items
$items = mysqli_query($conn, "
    SELECT pri.*, p.name AS product_name, p.unit 
    FROM product_request_items pri
    LEFT JOIN products p ON pri.product_id = p.id
    WHERE pri.request_id = $id
");
?>

<div class="pcoded-content">
<div class="container mt-4">
    <h3>Admin Final Approval</h3>

    <form method="post">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>HR Approved Qty</th>
                    <th>Final Qty</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($row = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= $row['product_name'] ?></td>

                    <td><?= $row['qty_hr_approved'] ?></td>

                    <td>
                        <input type="number" class="form-control"
                               name="final_qty[<?= $row['id'] ?>]"
                               value="<?= $row['qty_hr_approved'] ?>"
                               min="0" step="0.01">
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mb-3">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control" required></textarea>
        </div>

        <button class="btn btn-success" name="approve">Final Approve</button>
        <button class="btn btn-danger" name="decline">Decline</button>

    </form>
</div>
</div>

<?php
/* ======================================================
   FINAL APPROVAL (ADMIN)
   ====================================================== */
if (isset($_POST['approve'])) {

    foreach ($_POST['final_qty'] as $item_id => $qty) {

        $qty = floatval($qty);

        // Get product for stock deduction check
        $item = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT * FROM product_request_items WHERE id=$item_id
        "));

        $product_id = $item['product_id'];

        // 1) Update final qty
        mysqli_query($conn, "
            UPDATE product_request_items
            SET qty_admin_approved = $qty,
                final_qty = $qty,
                status = 'admin_approved'
            WHERE id = $item_id
        ");

        // 2) Deduct stock (ONLY ONCE)
        if ($qty > 0 && $item['stock_deducted'] == 0) {

            mysqli_query($conn, "
                INSERT INTO stock_master (product_id, stock_out, source, ref_id, note)
                VALUES ($product_id, $qty, 'issue', $id, 'Issued on admin approval')
            ");

            mysqli_query($conn, "
                UPDATE product_request_items
                SET stock_deducted = 1
                WHERE id = $item_id
            ");
        }
    }

    // Update main request
    mysqli_query($conn, "
        UPDATE product_requests 
        SET status='admin_approved',
            admin_action_date=NOW(),
            remarks='" . mysqli_real_escape_string($conn, $_POST['remarks']) . "'
        WHERE id=$id
    ");

    echo "<script>alert('Final Approval Done & Stock Deducted');window.location='request_list.php';</script>";
    exit;
}

/* ======================================================
   DECLINE REQUEST (ADMIN)
   ====================================================== */
if (isset($_POST['decline'])) {

    mysqli_query($conn, "
        UPDATE product_requests 
        SET status='admin_declined',
            admin_action_date=NOW(),
            remarks='" . mysqli_real_escape_string($conn, $_POST['remarks']) . "'
        WHERE id=$id
    ");

    echo "<script>alert('Request Declined');window.location='request_list.php';</script>";
    exit;
}

include(BASE_PATH.'/includes/footer.php');
?>
