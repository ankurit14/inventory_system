<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// ----------------------
// SAVE REQUEST
// ----------------------
if (isset($_POST['submit_request'])) {

    $user_id = $_SESSION['user_id'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    // 1️⃣ Insert main request
    $sql = "INSERT INTO product_requests (request_by, remarks) 
            VALUES ($user_id, '$remarks')";
    mysqli_query($conn, $sql);

    $request_id = mysqli_insert_id($conn);

    // 2️⃣ Insert items
    if (!empty($_POST['product_id'])) {

        foreach ($_POST['product_id'] as $index => $product_id) {

            if ($product_id == "" || $_POST['qty'][$index] == "") continue;

            $category_id     = intval($_POST['category_id'][$index]);
            $sub_category_id = intval($_POST['sub_category_id'][$index]);
            $product_id      = intval($product_id);
            $qty             = intval($_POST['qty'][$index]);

            // Fetch product unit from products table
            $u = mysqli_query($conn, "SELECT unit FROM products WHERE id=$product_id");
            $u_row = mysqli_fetch_assoc($u);
            $unit = $u_row ? $u_row['unit'] : '';

            // Save final row
            $sql_item = "
                INSERT INTO product_request_items 
                (request_id, category_id, sub_category_id, product_id, unit, qty_requested) 
                VALUES 
                ($request_id, $category_id, $sub_category_id, $product_id, '$unit', $qty)
            ";

            mysqli_query($conn, $sql_item);
        }
    }

    echo "<script>alert('Product Request Submitted Successfully!');window.location='request_list.php';</script>";
    exit;
}
?>
<style>
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    align-items: center;
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    text-align: center;
}

.header-box h5 {
    color: #fff;
    margin: 0;
    font-size: 20px;
    font-weight: 200;
}
.header-box a.btn {
    color: #1f2937;
    background-color: #fff;
    padding: 6px 15px;
    border-radius: 6px;
    text-decoration: none;
}

.filter-container input, .filter-container select {
    padding: 6px 10px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.table thead th {
    background: #2d6cdf;
    color: #fff;
    font-size: 14px;
}
.table tbody td {
    font-size: 14px;
    padding: 6px 10px;
}
.table tbody tr:hover {
    background: #f1f5ff;
}

.btn-sm {
    padding: 3px 7px;
    font-size: 13px;
}
.status-btn {
    min-width: 80px;
}
.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    padding: 4px 6px !important;
    height: 30px !important;
    line-height: 14px;
}
.page-header-bg {
    position: absolute;
    top: 5px;
    left: 0;
    width: 100%;
    height: 50%;
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    z-index: 1;
    border-radius: 8px;
}

</style>

<div class="pcoded-content">
<div class="card">

 <div class="header-box">
    <h2 style="color: #ffffff; margin: 0;">
        Create Product Request 
    </h2>
</div>
    <!-- <div class="card-header">
        <h4></h4>
    </div> -->

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
                <textarea name="remarks" class="form-control" placeholder="Why do you need these products?" required></textarea>
            </div>

            <hr>

            <h5>Add Products</h5>

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
                <tr>
                    <!-- CATEGORY -->
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

                    <!-- SUB CATEGORY -->
                    <td>
                        <select class="form-control subcategory" name="sub_category_id[]" required>
                            <option value="">Select Category First</option>
                        </select>
                    </td>

                    <!-- PRODUCTS -->
                    <td>
                        <select class="form-control product" name="product_id[]" required>
                            <option value="">Select Subcategory First</option>
                        </select>
                    </td>

                    <!-- QTY -->
                    <td>
                        <input type="number" name="qty[]" class="form-control" min="1" required>
                    </td>

                    <td>
                        <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                    </td>
                </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-primary" id="addRow">+ Add More</button>
            <br><br>

            <button type="submit" name="submit_request" class="btn btn-success">Submit Request</button>
        </form>
    </div>
</div>
</div>

<?php 
include(BASE_PATH.'/includes/footer.php');
?>


<script>
// ---------------------------
// ADD ROW
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
// LOAD SUBCATEGORY
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
