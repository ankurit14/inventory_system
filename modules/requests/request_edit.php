<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// ----------------------
// VALIDATE REQUEST ID
// ----------------------
if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid Request'); window.location='request_list.php';</script>";
    exit;
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// ----------------------
// FETCH MAIN REQUEST
// ----------------------
$req = mysqli_query($conn, "
    SELECT * FROM product_requests 
    WHERE id=$id AND request_by=$user_id
");

$request = mysqli_fetch_assoc($req);

if (!$request) {
    echo "<script>alert('Request not found'); window.location='request_list.php';</script>";
    exit;
}

// ----------------------
// ALLOW EDIT ONLY IF STATUS = pending
// ----------------------
if ($request['status'] != 'pending') {
    echo "<script>alert('You can edit only PENDING requests'); window.location='request_list.php';</script>";
    exit;
}

// ----------------------
// FETCH REQUEST ITEMS
// ----------------------
$items = mysqli_query($conn, "
    SELECT pri.*, 
           c.name AS category_name,
           sc.name AS subcategory_name,
           p.name AS product_name
    FROM product_request_items pri
    LEFT JOIN category c ON pri.category_id = c.id
    LEFT JOIN sub_category sc ON pri.sub_category_id = sc.id
    LEFT JOIN products p ON pri.product_id = p.id
    WHERE pri.request_id = $id
");

// ----------------------
// UPDATE REQUEST
// ----------------------
if (isset($_POST['update_request'])) {

    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    // Update main request
    mysqli_query($conn, "
        UPDATE product_requests 
        SET remarks='$remarks'
        WHERE id=$id
    ");

    // Delete old items
    mysqli_query($conn, "DELETE FROM product_request_items WHERE request_id=$id");

    // Insert updated items
    if (!empty($_POST['product_id'])) {

        foreach ($_POST['product_id'] as $index => $product_id) {

            if ($product_id == "" || $_POST['qty'][$index] == "") continue;

            $category_id     = intval($_POST['category_id'][$index]);
            $sub_category_id = intval($_POST['sub_category_id'][$index]);
            $product_id      = intval($product_id);
            $qty             = intval($_POST['qty'][$index]);

            // Fetch product unit
            $u = mysqli_query($conn, "SELECT unit FROM products WHERE id=$product_id");
            $u_row = mysqli_fetch_assoc($u);
            $unit = $u_row ? $u_row['unit'] : '';

            // Insert new item
            mysqli_query($conn, "
                INSERT INTO product_request_items 
                (request_id, category_id, sub_category_id, product_id, unit, qty_requested)
                VALUES
                ($id, $category_id, $sub_category_id, $product_id, '$unit', $qty)
            ");
        }
    }

    echo "<script>alert('Request Updated Successfully!'); window.location='request_list.php';</script>";
    exit;
}
?>

<div class="pcoded-content">
<div class="card">
    <div class="card-header">
        <h4>Edit Product Request</h4>
    </div>

    <div class="card-body">
        <form method="post">

            <!-- USER INFO -->
            <div class="mb-3">
                <label><b>Requested By:</b></label>
                <input type="text" class="form-control" value="<?= $_SESSION['name']; ?>" readonly>
            </div>

            <!-- REMARKS -->
            <div class="mb-3">
                <label>Remarks / Reason</label>
                <textarea name="remarks" class="form-control" required><?= $request['remarks']; ?></textarea>
            </div>

            <hr>

            <h5>Edit Products</h5>

            <table class="table table-bordered" id="itemTable">
                <thead>
                <tr>
                    <th width="20%">Category</th>
                    <th width="20%">Sub Category</th>
                    <th width="30%">Product</th>
                    <th width="15%">Qty</th>
                    <th width="15%">Action</th>
                </tr>
                </thead>

                <tbody>

                <?php while ($row = mysqli_fetch_assoc($items)): ?>
                    <tr>

                        <!-- CATEGORY -->
                        <td>
                            <select class="form-control category" name="category_id[]" required>
                                <option value="">Select</option>
                                <?php
                                $cat = mysqli_query($conn, "SELECT id,name FROM category WHERE status='active'");
                                while ($c = mysqli_fetch_assoc($cat)):
                                ?>
                                    <option value="<?= $c['id'] ?>" <?= ($c['id'] == $row['category_id']) ? "selected" : "" ?>>
                                        <?= $c['name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>

                        <!-- SUBCATEGORY -->
                        <td>
                            <select class="form-control subcategory" name="sub_category_id[]" required>
                                <option value="<?= $row['sub_category_id']; ?>"><?= $row['subcategory_name']; ?></option>
                            </select>
                        </td>

                        <!-- PRODUCT -->
                        <td>
                            <select class="form-control product" name="product_id[]" required>
                                <option value="<?= $row['product_id']; ?>"><?= $row['product_name']; ?></option>
                            </select>
                        </td>

                        <!-- QTY -->
                        <td>
                            <input type="number" name="qty[]" class="form-control"
                                   value="<?= $row['qty_requested']; ?>" min="1" required>
                        </td>

                        <td>
                            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                        </td>

                    </tr>
                <?php endwhile; ?>

                </tbody>
            </table>

            <button type="button" class="btn btn-primary" id="addRow">+ Add More</button>
            <br><br>

            <button type="submit" name="update_request" class="btn btn-success">Update Request</button>
        </form>
    </div>
</div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<script>
// ---------------------------
// ADD NEW ROW
// ---------------------------
$("#addRow").click(function () {
    let row = `
        <tr>
            <td>
                <select class="form-control category" name="category_id[]" required>
                    <option value="">Select</option>
                    <?php
                    $cat = mysqli_query($conn, "SELECT id,name FROM category WHERE status='active'");
                    while ($c = mysqli_fetch_assoc($cat)):
                    ?>
                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </td>

            <td>
                <select class="form-control subcategory" name="sub_category_id[]" required>
                    <option value="">Select Category First</option>
                </select>
            </td>

            <td>
                <select class="form-control product" name="product_id[]" required>
                    <option value="">Select Subcategory First</option>
                </select>
            </td>

            <td>
                <input type="number" name="qty[]" class="form-control" min="1" required>
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>
        </tr>
    `;

    $("#itemTable tbody").append(row);
});

// ---------------------------
// REMOVE ROW
// ---------------------------
$(document).on("click", ".removeRow", function () {
    $(this).closest("tr").remove();
});

// ---------------------------
// LOAD SUBCATEGORIES
// ---------------------------
$(document).on("change", ".category", function () {

    let category_id = $(this).val();
    let row = $(this).closest("tr");

    $.ajax({
        url: "ajax_get_subcategories.php",
        type: "POST",
        data: { category_id: category_id },
        success: function (data) {
            row.find(".subcategory").html(data);
            row.find(".product").html("<option value=''>Select Subcategory First</option>");
        }
    });
});

// ---------------------------
// LOAD PRODUCTS
// ---------------------------
$(document).on("change", ".subcategory", function () {

    let sub_category_id = $(this).val();
    let row = $(this).closest("tr");

    $.ajax({
        url: "ajax_get_products.php",
        type: "POST",
        data: { sub_category_id: sub_category_id },
        success: function (data) {
            row.find(".product").html(data);
        }
    });
});
</script>
