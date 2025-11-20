<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch request
$req = mysqli_query($conn, "SELECT * FROM product_requests WHERE id=$id");
$request = mysqli_fetch_assoc($req);

if (!$request) {
    die("Invalid Request ID");
}

// If HR/Admin already approved → User cannot edit
if (in_array($request['status'], ['hr_approved', 'admin_approved', 'declined'])) {
    echo "<div class='alert alert-danger mt-3'>You cannot edit this request because approval already started.</div>";
    include(BASE_PATH.'/includes/footer.php');
    exit;
}

// Fetch request items
$items = mysqli_query($conn, "
    SELECT pri.*, p.name AS product_name, p.unit, p.stock 
    FROM product_request_items pri
    LEFT JOIN products p ON pri.product_id = p.id
    WHERE pri.request_id = $id
");

// Fetch product list
$products = mysqli_query($conn, "SELECT id, name, stock FROM products ORDER BY name ASC");

?>
<div class="pcoded-content">
<div class="container mt-4">
    <h3>Edit Request</h3>

    <form method="post">

        <table class="table table-bordered" id="productTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td>
                        <select name="product_id[]" class="form-control" required>
                            <option value="">-- Select Product --</option>
                            <?php
                            $pRes = mysqli_query($conn, "SELECT * FROM products ORDER BY name ASC");
                            while ($p = mysqli_fetch_assoc($pRes)): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= ($p['id'] == $row['product_id']) ? 'selected' : '' ?>>
                                    <?= $p['name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>

                    <td>
                        <input type="text" class="form-control stockBox" value="<?= $row['stock'] ?>" readonly>
                    </td>

                    <td>
                        <input type="number" name="qty[]" class="form-control qtyBox"
                               value="<?= $row['qty_requested'] ?>" min="0.01" step="0.1" required>
                    </td>

                    <td>
                        <button type="button" class="btn btn-danger removeRow">X</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button type="button" class="btn btn-primary" id="addRow">+ Add Item</button>

        <div class="mb-3 mt-3">
            <label>Reason</label>
            <textarea name="reason" class="form-control" required><?= $request['reason'] ?></textarea>
        </div>

        <button class="btn btn-success" name="update">Update Request</button>
    </form>

</div>
</div>

<script>
document.getElementById('addRow').addEventListener('click', function () {
    let html = `
        <tr>
            <td>
                <select name="product_id[]" class="form-control productSelect" required>
                    <option value="">-- Select Product --</option>
                    <?php
                    mysqli_data_seek($products, 0);
                    while ($p = mysqli_fetch_assoc($products)): ?>
                        <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </td>

            <td>
                <input type="text" class="form-control stockBox" value="0" readonly>
            </td>

            <td>
                <input type="number" name="qty[]" class="form-control qtyBox" min="0.01" step="0.1" required>
            </td>

            <td>
                <button type="button" class="btn btn-danger removeRow">X</button>
            </td>
        </tr>
    `;

    document.querySelector("#productTable tbody").insertAdjacentHTML('beforeend', html);
});

document.addEventListener('click', function(e){
    if (e.target.classList.contains('removeRow')) {
        e.target.closest("tr").remove();
    }
});
</script>

<?php
/* -------------------------------
    UPDATE REQUEST (POST)
-------------------------------- */
if (isset($_POST['update'])) {

    // Basic request update
    mysqli_query($conn, "
        UPDATE product_requests 
        SET reason='".mysqli_real_escape_string($conn, $_POST['reason'])."', 
            status='pending'
        WHERE id=$id
    ");

    // Delete previous items
    mysqli_query($conn, "DELETE FROM product_request_items WHERE request_id=$id");

    // Insert new items
    foreach ($_POST['product_id'] as $index => $pid) {

        $qty = floatval($_POST['qty'][$index]);
        $pid = intval($pid);

        mysqli_query($conn, "
            INSERT INTO product_request_items(request_id, product_id, qty_requested, status)
            VALUES ('$id', '$pid', '$qty', 'pending')
        ");
    }

    echo "<script>alert('Request Updated Successfully');window.location='request_view.php?id=$id';</script>";
}

include(BASE_PATH.'/includes/footer.php');
?>
