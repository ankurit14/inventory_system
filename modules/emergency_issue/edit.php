<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// --------------------
// SECURITY
// --------------------
if ($_SESSION['role'] != 'hr' && $_SESSION['role'] != 'admin') {
    echo "<script>alert('Access Denied');window.location='".BASE_URL."index.php';</script>";
    exit;
}

$issue_id = intval($_GET['id']);

// --------------------------------------
// FETCH ISSUE MASTER
// --------------------------------------
$issue = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM emergency_issues WHERE id = $issue_id
"));

// --------------------------------------
// FETCH ITEMS
// --------------------------------------
$item_query = "
    SELECT i.*, 
           c.name AS category_name,
           sc.name AS subcategory_name,
           p.name AS product_name,
           p.unit AS product_unit
    FROM emergency_issue_items i
    LEFT JOIN category c ON i.category_id = c.id
    LEFT JOIN sub_category sc ON i.sub_category_id = sc.id
    LEFT JOIN products p ON i.product_id = p.id
    WHERE i.issue_id = $issue_id
";
$items = mysqli_query($conn, $item_query);

// --------------------------------------
// FETCH CATEGORIES (for dynamic JS row)
// --------------------------------------
$categories = [];
$cat = mysqli_query($conn, "SELECT id,name FROM category WHERE status='active'");
while($c = mysqli_fetch_assoc($cat)){
    $categories[] = $c;
}
$categories_json = json_encode($categories);

// --------------------------------------
// UPDATE FORM SUBMITTED
// --------------------------------------
if (isset($_POST['update_issue'])) {

    foreach ($_POST['product_id'] as $index => $product_id) {

        if ($product_id == "" || empty($_POST['qty'][$index])) continue;

        $category_id     = intval($_POST['category_id'][$index] ?? 0);
        $sub_category_id = intval($_POST['sub_category_id'][$index] ?? 0);
        $product_id      = intval($product_id);
        $qty             = floatval($_POST['qty'][$index] ?? 0);

        $item_id = $_POST['item_id'][$index] ?? 'new';

        // Fetch unit
        $u = mysqli_query($conn, "SELECT unit FROM products WHERE id=$product_id");
        $u_row = mysqli_fetch_assoc($u);
        $unit = $u_row['unit'] ?? '';

        if ($item_id == 'new') {
            // --------------------------
            // ADD NEW ITEM
            // --------------------------
            mysqli_query($conn, "
                INSERT INTO emergency_issue_items 
                (issue_id, category_id, sub_category_id, product_id, unit, qty_issued) 
                VALUES 
                ($issue_id, $category_id, $sub_category_id, $product_id, '$unit', $qty)
            ");

            // Reduce stock
            mysqli_query($conn, "
                INSERT INTO stock_master 
                (product_id, stock_in, stock_out, source, ref_id, note)
                VALUES ($product_id, 0, $qty, 'emergency_issue', $issue_id, 'New item added')
            ");

        } else {
            // --------------------------
            // EXISTING ITEM UPDATE
            // --------------------------
            $old_row = mysqli_fetch_assoc(mysqli_query($conn, "
                SELECT qty_issued, product_id 
                FROM emergency_issue_items 
                WHERE id = $item_id
            "));

            $old_qty = $old_row['qty_issued'] ?? 0;
            $old_product_id = $old_row['product_id'] ?? 0;

            $diff = $qty - $old_qty;

            if ($diff > 0) {
                mysqli_query($conn, "
                    INSERT INTO stock_master 
                    (product_id, stock_in, stock_out, source, ref_id, note)
                    VALUES ($product_id, 0, $diff, 'emergency_issue', $issue_id, 'Qty increased')
                ");
            } elseif ($diff < 0) {
                mysqli_query($conn, "
                    INSERT INTO stock_master 
                    (product_id, stock_in, stock_out, source, ref_id, note)
                    VALUES ($product_id, ".abs($diff).", 0, 'emergency_issue', $issue_id, 'Qty decreased')
                ");
            }

            mysqli_query($conn, "
                UPDATE emergency_issue_items 
                SET category_id=$category_id, sub_category_id=$sub_category_id, product_id=$product_id, qty_issued=$qty, unit='$unit' 
                WHERE id=$item_id
            ");
        }
    }

    // Delete removed items
    if (!empty($_POST['delete_item'])) {
        foreach ($_POST['delete_item'] as $del_id) {
            $row = mysqli_fetch_assoc(mysqli_query($conn, "
                SELECT product_id, qty_issued FROM emergency_issue_items WHERE id = $del_id
            "));

            $product_id = $row['product_id'] ?? 0;
            $qty = $row['qty_issued'] ?? 0;

            // Rollback stock
            mysqli_query($conn, "
                INSERT INTO stock_master 
                (product_id, stock_in, stock_out, source, ref_id, note)
                VALUES ($product_id, $qty, 0, 'emergency_issue', $issue_id, 'Item deleted')
            ");

            mysqli_query($conn, "DELETE FROM emergency_issue_items WHERE id = $del_id");
        }
    }

    echo "<script>alert('Issue updated successfully'); window.location='view.php?id=$issue_id';</script>";
    exit;
}
?>


<style>
/* Header */
.header-box {
    background: linear-gradient(135deg, #da4453, #f6bb42);
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.header-box h2 {
    font-size: 24px;
    font-weight: 600;
    margin: 0;
    color: #fff;
}
.header-box a.btn {
    color: #1f2937;
    background-color: #fff;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    padding: 6px 15px;
    z-index: 10;
}

/* Filter Box */
.filter-container {
    margin-bottom: 12px;
}
.filter-container input, .filter-container select {
    padding: 6px 10px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* Table */
.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    height: 38px;
    padding: 5px 8px !important;
}
.table tbody td {
    padding: 6px 10px;
    font-size: 14px;
}
.table tbody tr:hover {
    background: #f1f5ff;
}

/* Buttons */
.btn-sm {
    padding: 3px 7px;
    font-size: 13px;
}
.status-btn.btn-sm {
    min-width: 80px;
}
</style>

<div class="pcoded-content">


<div class="header-box">
        <h2>Edit Emergency Issue</h2>
        <a href="index.php" class="btn btn-light shadow-sm">Back</a>
    </div>


<form method="get" action="view.php" class="mb-3">
    <input type="hidden" name="id" value="<?= $issue_id ?>">
    <!-- <button type="submit" class="btn btn-secondary">Back</button> -->
</form>

<!-- <a href="view.php?id=<?= $issue_id ?>" class="btn btn-secondary mb-3">Back</a> -->

<form method="post">
<table class="table table-bordered" id="itemTable">
<tr>
    <th>Category</th>
    <th>Sub Category</th>
    <th>Product</th>
    <th>Unit</th>
    <th>Qty</th>
    <th>Delete</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($items)): ?>
<tr>
    <td>
        <select name="category_id[]" class="category form-control" required>
            <option value="">Select</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id']==$row['category_id']?'selected':'' ?>><?= $c['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </td>

    <td>
        <select name="sub_category_id[]" class="subcategory form-control" required>
            <option value="<?= $row['sub_category_id'] ?>" selected><?= $row['subcategory_name'] ?></option>
        </select>
    </td>

    <td>
        <select name="product_id[]" class="product form-control" required>
            <option value="<?= $row['product_id'] ?>" selected><?= $row['product_name'] ?></option>
        </select>
    </td>

    <td><input type="text" class="unit form-control" value="<?= $row['product_unit'] ?>" readonly></td>

    <td>
        <input type="hidden" name="item_id[]" value="<?= $row['id'] ?>">
        <input type="number" name="qty[]" value="<?= $row['qty_issued'] ?? 1 ?>" min="1" class="form-control" required>
    </td>

    <td>
        <input type="checkbox" name="delete_item[]" value="<?= $row['id'] ?>"> Remove
    </td>
</tr>
<?php endwhile; ?>
</table>

<button type="button" class="btn btn-primary" id="addRow">+ Add More Products</button>
<br><br>
<button type="submit" name="update_issue" class="btn btn-success">Update Issue</button>
</form>

</div>
<?php include(BASE_PATH.'/includes/footer.php'); ?>
<script>
let categories = <?= $categories_json ?>;

$("#addRow").click(function () {
    let catOptions = '<option value="">Select</option>';
    categories.forEach(function(c){
        catOptions += `<option value="${c.id}">${c.name}</option>`;
    });

    let row = `
    <tr>
        <td>
            <select name="category_id[]" class="category form-control" required>
                ${catOptions}
            </select>
        </td>
        <td>
            <select name="sub_category_id[]" class="subcategory form-control" required>
                <option value="">Select Category First</option>
            </select>
        </td>
        <td>
            <select name="product_id[]" class="product form-control" required>
                <option value="">Select Subcategory First</option>
            </select>
        </td>
        <td><input type="text" class="unit form-control" readonly></td>
        <td>
            <input type="hidden" name="item_id[]" value="new">
            <input type="number" name="qty[]" min="1" class="form-control" required>
        </td>
        <td><button type="button" class="removeRow">X</button></td>
    </tr>`;
    $("#itemTable").append(row);
});

// Remove Row
$(document).on("click", ".removeRow", function () {
    $(this).closest("tr").remove();
});

// Load Subcategory
$(document).on("change", ".category", function () {
    let category_id = $(this).val();
    let row = $(this).closest("tr");

    $.post("ajax_get_subcategories.php", { category_id: category_id }, function (data) {
        row.find(".subcategory").html(data);
        row.find(".product").html("<option value=''>Select Subcategory First</option>");
    });
});

// Load Products
$(document).on("change", ".subcategory", function () {
    let sub_category_id = $(this).val();
    let row = $(this).closest("tr");

    $.post("ajax_get_products.php", { sub_category_id: sub_category_id }, function (data) {
        row.find(".product").html(data);
    });
});

// Load Unit
$(document).on("change", ".product", function () {
    let pid = $(this).val();
    let row = $(this).closest("tr");

    $.post("ajax_get_unit.php", { product_id: pid }, function (unit) {
        row.find(".unit").val(unit);
    });
});
</script>
