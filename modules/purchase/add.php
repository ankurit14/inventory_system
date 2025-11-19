<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

include('../suppliers/supplier_functions.php');
include('../category/category_functions.php');
include('../sub_category/sub_category_functions.php');
include('../product/product_functions.php');

include('../stock/stock_functions.php');   // ⬅ STOCK FUNCTION ADDED
include('purchase_functions.php');

$errors = [];
$success = "";

$suppliers = get_all_suppliers();
$categories = get_all_categories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $supplier_id = $_POST['supplier_id'];
    $purchase_date = $_POST['purchase_date'];
    $status = $_POST['status'];
    $items = $_POST['items'] ?? [];

    if (empty($supplier_id)) $errors['supplier'] = "Supplier is required";
    if (empty($purchase_date)) $errors['date'] = "Date is required";
    if (count($items) == 0) $errors['items'] = "Add at least 1 item";

    // Calculate total
    $total_amount = 0;
    foreach ($items as $it) {
        $total_amount += floatval($it['total']);
    }

    if (empty($errors)) {

        // Insert purchase
        $purchase_id = insert_purchase($supplier_id, $purchase_date, $total_amount, $status);

        // Insert purchase items + Stock Movement
        foreach ($items as $it) {

            // Skip invalid items
            if (
                !isset($it['product_id']) ||
                !isset($it['qty']) ||
                !isset($it['unit_price']) ||
                !isset($it['total'])
            ) continue;

            // Insert purchase item
            insert_purchase_item(
                $purchase_id,
                intval($it['product_id']),
                floatval($it['qty']),
                floatval($it['unit_price']),
                floatval($it['total'])
            );

            // Insert stock movement
            insert_stock(
                intval($it['product_id']),     // product ID
                floatval($it['qty']),          // stock_in
                0,                             // stock_out
                'purchase',                    // source
                $purchase_id,                  // ref id
                'Purchased'                    // note
            );
        }

        $success = "Purchase added successfully!";
    }
}
?>


<style>
.table td, .table th { padding:6px !important; }
.row-item { background:#f7f7f7; padding:10px; border-radius:5px; margin-bottom:5px; }
</style>

<div class="pcoded-content">
    <div class="header-box mb-3">
        <h2>Add Purchase</h2>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" id="purchaseForm">

       <div class="card p-3 mb-3">
    <h5>Purchase Info</h5>

    <div class="row">

        <div class="col-md-4 mb-3">
            <label><strong>Supplier *</strong></label>
            <select name="supplier_id" class="form-control" required>
                <option value="">Select Supplier</option>
                <?php while($s = mysqli_fetch_assoc($suppliers)): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label><strong>Purchase Date *</strong></label>
            <input type="date" name="purchase_date" class="form-control" required>
        </div>

        <div class="col-md-4 mb-3">
            <label><strong>Status *</strong></label>
            <select name="status" class="form-control">
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
            </select>
        </div>

    </div>
</div>

        <!-- ITEM SECTION -->
        <div class="card p-3 mb-3">
            <h5>Add Items</h5>

           <div class="row">

    <div class="col-md-4 mb-3">
        <label><strong>Category</strong></label>
        <select id="category" class="form-control">
            <option value="">Select Category</option>
            <?php while($c = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label><strong>Sub Category</strong></label>
        <select id="subcategory" class="form-control">
            <option value="">Select Sub Category</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label><strong>Product</strong></label>
        <select id="product" class="form-control">
            <option value="">Select Product</option>
        </select>
    </div>

</div>


           <div class="row align-items-end">

    <div class="col-md-2 mb-2">
        <label>Unit</label>
        <input type="text" id="unit" class="form-control" readonly>
    </div>

    <div class="col-md-2 mb-2">
        <label>Qty</label>
        <input type="number" id="qty" class="form-control">
    </div>

    <div class="col-md-2 mb-2">
        <label>Unit Price</label>
        <input type="number" id="price" class="form-control">
    </div>

    <div class="col-md-2 mb-2">
        <label>Total</label>
        <input type="number" id="total" class="form-control" readonly>
    </div>

    <div class="col-md-2 mb-2">
        <label>&nbsp;</label> <!-- Empty label for alignment -->
        <button type="button" id="addItem" class="btn btn-primary form-control">
            Add Item
        </button>
    </div>

</div>




            <hr>

            <table class="table table-bordered mt-3" id="itemTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Total</th>
                        <th width="60">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>

        <button class="btn btn-success w-100">Save Purchase</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let rowIndex = 0;

// Load subcategories when category changes
$("#category").change(function(){
    let id = $(this).val();
    $("#subcategory").html("<option>Loading...</option>");
    $("#product").html('<option value="">Select Product</option>');

    $.get("../sub_category/get_sub_categories.php", { category_id: id }, function(data){
        $("#subcategory").html(data);
    }).fail(function(){
        alert("Error loading subcategories");
        $("#subcategory").html('<option value="">Select Sub Category</option>');
    });
});

// Load products when subcategory changes
$("#subcategory").change(function(){
    let id = $(this).val();
    $("#product").html("<option>Loading...</option>");

    $.get("../product/get_products_by_sub.php", { sub_id: id }, function(data){
        $("#product").html(data);
    }).fail(function(){
        alert("Error loading products");
        $("#product").html('<option value="">Select Product</option>');
    });
});

// Load product unit
$("#product").change(function(){
    let pid = $(this).val();
    $.get("../product/get_product_unit.php", { product_id: pid }, function(unit){
        $("#unit").val(unit);
    });
});

// Calculate total
$("#qty, #price").on("input", function(){
    let q = parseFloat($("#qty").val()) || 0;
    let p = parseFloat($("#price").val()) || 0;
    $("#total").val(q * p);
});

// Add item row
$("#addItem").click(function(){
    let pid = $("#product").val();
    let pname = $("#product option:selected").text();
    let unit = $("#unit").val();
    let qty = $("#qty").val();
    let price = $("#price").val();
    let total = $("#total").val();

    if (!pid || qty <= 0) {
        alert("Select product and enter qty!");
        return;
    }

    let row = `
    <tr>
        <td>${pname}<input type="hidden" name="items[${rowIndex}][product_id]" value="${pid}"></td>
        <td>${unit}</td>
        <td><input type="hidden" name="items[${rowIndex}][qty]" value="${qty}">${qty}</td>
        <td><input type="hidden" name="items[${rowIndex}][unit_price]" value="${price}">${price}</td>
        <td><input type="hidden" name="items[${rowIndex}][total]" value="${total}">${total}</td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
    </tr>
    `;

    $("#itemTable tbody").append(row);
    rowIndex++;

    // Reset all fields for new item
    $("#category").val('');
    $("#subcategory").html('<option value="">Select Sub Category</option>');
    $("#product").html('<option value="">Select Product</option>');
    $("#unit").val('');
    $("#qty").val('');
    $("#price").val('');
    $("#total").val('');
});


// Remove row
$(document).on("click", ".removeRow", function(){
    $(this).closest("tr").remove();
});
</script>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
