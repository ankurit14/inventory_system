<?php
// edit.php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

include('../suppliers/supplier_functions.php');
include('../category/category_functions.php');
include('../sub_category/sub_category_functions.php');
include('../product/product_functions.php');
include('../stock/stock_functions.php');
include('purchase_functions.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Purchase ID missing!");
}
$purchase_id = intval($_GET['id']);

$purchase = get_purchase_by_id($purchase_id);
if (!$purchase) die("Purchase not found!");

$suppliers = get_all_suppliers();
$categories = get_all_categories();
$items = get_purchase_items($purchase_id);
$all_products = mysqli_query($conn, "SELECT id, name FROM products ORDER BY name ASC");
?>

<style>
/* small styling to match your layout */
.table th, .table td { padding: 6px !important; font-size:13px; }
.header-box { display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg,#4e73df,#1cc88a); padding:15px; color:#fff; border-radius:8px; margin-bottom:20px; }
.form-control[readonly] { background-color:#f8f9fb; }
</style>

<div class="pcoded-content">
    <div class="header-box">
        <h2>Edit Purchase #<?= $purchase_id ?></h2>
        <a href="index.php" class="btn btn-light">Back</a>
    </div>

    <?php if(isset($_GET['success']) && $_GET['success']=='1'): ?>
        <div class="alert alert-success">Purchase updated successfully!</div>
    <?php endif; ?>

    <form id="editPurchaseForm" method="POST" action="save_edit.php">
        <input type="hidden" name="purchase_id" value="<?= $purchase_id ?>">

        <div class="card p-3 mb-3">
            <h5>Purchase Info</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Supplier *</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">Select Supplier</option>
                        <?php while($s = mysqli_fetch_assoc($suppliers)): ?>
                            <option value="<?= $s['id'] ?>" <?= $purchase['supplier_id']==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Purchase Date *</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= $purchase['purchase_date'] ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Status *</label>
                    <select name="status" class="form-control">
                        <option value="pending" <?= $purchase['status']=='pending'?'selected':'' ?>>Pending</option>
                        <option value="completed" <?= $purchase['status']=='completed'?'selected':'' ?>>Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Existing items (editable) -->
        <div class="card p-3 mb-3">
            <h5>Existing Items</h5>
            <table class="table table-bordered" id="existingItems">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th width="100">Qty</th>
                        <th width="120">Unit Price</th>
                        <th width="120">Total</th>
                        <th width="80">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($it = mysqli_fetch_assoc($items)): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($it['product_name']) ?>
                                <input type="hidden" name="item_existing_id[]" value="<?= $it['id'] ?>">
                                <input type="hidden" name="item_existing_product_id[]" value="<?= $it['product_id'] ?>">
                            </td>
                            <td><?= htmlspecialchars($it['unit']) ?></td>
                            <td><input class="form-control qty" type="number" step="0.01" min="0" name="item_existing_qty[]" value="<?= $it['qty'] ?>"></td>
                            <td><input class="form-control price" type="number" step="0.01" min="0" name="item_existing_price[]" value="<?= $it['unit_price'] ?>"></td>
                            <td><input class="form-control line_total" type="text" name="item_existing_total[]" value="<?= $it['total'] ?>" readonly></td>
                            <td class="text-center"><input type="checkbox" name="item_delete[]" value="<?= $it['id'] ?>"></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Add new items -->
        <div class="card p-3 mb-3">
            <h5>Add New Items</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Category</label>
                    <select id="category" class="form-control">
                        <option value="">Select Category</option>
                        <?php mysqli_data_seek($categories,0); while($c = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Sub Category</label>
                    <select id="subcategory" class="form-control">
                        <option value="">Select Sub Category</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Product</label>
                    <select id="product" class="form-control">
                        <option value="">Select Product</option>
                    </select>
                </div>
            </div>

            <div class="row align-items-end">
                <div class="col-md-2 mb-2">
                    <label>Unit</label>
                    <input id="unit" class="form-control" readonly>
                </div>
                <div class="col-md-2 mb-2">
                    <label>Qty</label>
                    <input id="qty" class="form-control" type="number" step="0.01" min="0">
                </div>
                <div class="col-md-2 mb-2">
                    <label>Unit Price</label>
                    <input id="price" class="form-control" type="number" step="0.01" min="0">
                </div>
                <div class="col-md-2 mb-2">
                    <label>Total</label>
                    <input id="total" class="form-control" readonly>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="button" id="addNewItem" class="btn btn-primary form-control">Add Item</button>
                </div>
            </div>

            <hr>

            <table class="table table-bordered" id="newItemsTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>

        <div class="text-end mb-4">
            <label class="me-3">Grand Total: </label>
            <input type="text" id="grandTotal" name="grand_total" class="form-control d-inline-block" style="width:150px" readonly value="<?= number_format($purchase['total_amount'],2,'.','') ?>">
        </div>

        <div class="text-end">
            <button class="btn btn-success" type="submit">Save Changes</button>
        </div>
    </form>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>

<script>
$(function() {
    // load subcategories when category changes
    $('#category').change(function() {
        let cat = $(this).val();
        if (!cat) {
            $('#subcategory').html('<option value="">Select Sub Category</option>');
            $('#product').html('<option value="">Select Product</option>');
            return;
        }
        $.get('../sub_category/get_sub_categories.php', {category_id: cat}, function(data) {
            $('#subcategory').html(data);
        });
    });

    // load products by subcategory
    $('#subcategory').change(function() {
        let sub = $(this).val();
        if (!sub) {
            $('#product').html('<option value="">Select Product</option>');
            return;
        }
        $.get('../product/get_products_by_sub.php', {sub_id: sub}, function(data) {
            $('#product').html(data);
        });
    });

    // autofill unit on product select
    $('#product').change(function() {
        let pid = $(this).val();
        if (!pid) { $('#unit').val(''); return; }
        $.get('ajax_get_product_unit.php', {product_id: pid}, function(unit) {
            $('#unit').val(unit);
        });
    });

    // calc total for new item
    $('#qty,#price').on('input', function() {
        let q = parseFloat($('#qty').val()) || 0;
        let p = parseFloat($('#price').val()) || 0;
        $('#total').val((q*p).toFixed(2));
    });

    // add new item to newItemsTable
    let newIndex = 0;
    $('#addNewItem').click(function() {
        let pid = $('#product').val();
        if (!pid) { alert('Select product'); return; }
        let pname = $('#product option:selected').text();
        let unit = $('#unit').val();
        let qty = $('#qty').val();
        let price = $('#price').val();
        let total = $('#total').val();
        if (!qty || qty <= 0) { alert('Enter qty'); return; }

        let row = `<tr>
            <td>${pname}<input type="hidden" name="new_items[${newIndex}][product_id]" value="${pid}"></td>
            <td>${unit}<input type="hidden" name="new_items[${newIndex}][unit]" value="${unit}"></td>
            <td>${qty}<input type="hidden" name="new_items[${newIndex}][qty]" value="${qty}"></td>
            <td>${price}<input type="hidden" name="new_items[${newIndex}][unit_price]" value="${price}"></td>
            <td>${total}<input type="hidden" name="new_items[${newIndex}][total]" value="${total}"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeNew">X</button></td>
        </tr>`;

        $('#newItemsTable tbody').append(row);
        newIndex++;

        // reset new item controls
        $('#category').val(''); $('#subcategory').html('<option value="">Select Sub Category</option>'); $('#product').html('<option value="">Select Product</option>');
        $('#unit').val(''); $('#qty').val(''); $('#price').val(''); $('#total').val('');
        recalcGrand();
    });

    $(document).on('click', '.removeNew', function() {
        $(this).closest('tr').remove(); recalcGrand();
    });

    // recalc totals when existing items change
    function recalcExisting() {
        let grand = 0;
        $('#existingItems tbody tr').each(function() {
            let qty = parseFloat($(this).find('.qty').val()) || 0;
            let price = parseFloat($(this).find('.price').val()) || 0;
            let total = qty * price;
            $(this).find('.line_total').val(total.toFixed(2));
            grand += total;
        });
        // add new items totals
        $('#newItemsTable tbody tr').each(function() {
            let t = parseFloat($(this).find('input[name$="[total]"]').val()) || 0;
            grand += t;
        });
        $('#grandTotal').val(grand.toFixed(2));
    }
    $(document).on('input', '#existingItems .qty, #existingItems .price', recalcExisting);

    function recalcGrand() { recalcExisting(); }
    recalcGrand();

    // recalc when page loaded
    recalcGrand();

    // remove existing row checkbox will be processed on server; recalc removing visually
    $(document).on('change', 'input[name="item_delete[]"]', function() {
        // don't remove row client-side — server will delete. But we recalc ignoring checked rows:
        let grand = 0;
        $('#existingItems tbody tr').each(function() {
            if ($(this).find('input[name="item_delete[]"]').prop('checked')) return; // skip
            let qty = parseFloat($(this).find('.qty').val()) || 0;
            let price = parseFloat($(this).find('.price').val()) || 0;
            grand += qty*price;
        });
        $('#newItemsTable tbody tr').each(function() {
            let t = parseFloat($(this).find('input[name$="[total]"]').val()) || 0;
            grand += t;
        });
        $('#grandTotal').val(grand.toFixed(2));
    });

});


$('#editPurchaseForm').submit(function(e){
    e.preventDefault(); // prevent default form submit

    let formData = $(this).serialize(); // get all form data

    $.ajax({
        url: "save_edit.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(resp){
            if(resp.status === "success"){
                alert("Purchase updated successfully!");
                window.location.reload();
            } else {
                alert("Error: " + resp.message);
            }
        },
        error: function(xhr){
            console.log(xhr.responseText);
            alert("Server Error: " + xhr.status);
        }
    });
});
</script>
