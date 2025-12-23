<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
include_once __DIR__ . '/../../config/path.php';

include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

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

    .filter-container input,
    .filter-container select {
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


    .table td,
    .table th {
        padding: 6px !important;
    }

    .row-item {
        background: #f7f7f7;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .add-new-form {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        border-radius: 6px;
        padding: 15px;
        margin-top: 10px;
        display: none;
    }

    .add-new-form.show {
        display: block;
        animation: slideDown 0.3s ease-in-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-row-inline {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 10px;
        align-items: flex-end;
    }

    @media (max-width: 576px) {
        .form-row-inline {
            grid-template-columns: 1fr;
        }
    }

    .alert-inline {
        padding: 0.75rem;
        margin-bottom: 10px;
        border-radius: 4px;
        font-size: 0.875rem;
    }
</style>

<div class="pcoded-content">
    <div class="header-box">
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
                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                        <option value="">Select Supplier</option>
                        <?php
                        mysqli_data_seek($suppliers, 0);
                        while ($s = mysqli_fetch_assoc($suppliers)): ?>
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
                        <?php
                        mysqli_data_seek($categories, 0);
                        while ($c = mysqli_fetch_assoc($categories)): ?>
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

        <div class="text-right mx-2">
            <button type="submit" class="btn btn-success">Save Purchase</button>
        </div>
    </form>

    <!-- Add Supplier Inline Form -->
    <div class="add-new-form" id="addSupplierForm" style="margin: 20px auto; max-width: 700px;">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title text-info mb-3"><i class="fa fa-plus"></i> Add New Supplier</h6>
                <div class="alert alert-danger d-none alert-inline" id="supplierInlineError"></div>
                <div class="alert alert-success d-none alert-inline" id="supplierInlineSuccess"></div>
                <div class="form-row-inline">
                    <div class="flex-grow-1">
                        <label class="form-label small mb-2"><strong>Supplier Name</strong></label>
                        <input type="text" id="newSupplierName" class="form-control form-control-sm" placeholder="e.g., ABC Supplies">
                    </div>
                    <div class="flex-grow-1">
                        <label class="form-label small mb-2"><strong>Contact Person</strong></label>
                        <input type="text" id="newSupplierContact" class="form-control form-control-sm" placeholder="e.g., John Doe">
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label small mb-2"><strong>Address</strong></label>
                    <input type="text" id="newSupplierAddress" class="form-control form-control-sm" placeholder="e.g., 123 Main St">
                </div>
                <button type="button" class="btn btn-success btn-sm mt-3" id="saveSupplierBtnInline"><i class="fa fa-plus"></i> Add Supplier</button>
                <button type="button" class="btn btn-secondary btn-sm mt-3" id="cancelSupplierBtnInline"><i class="fa fa-times"></i> Cancel</button>
            </div>
        </div>
    </div>

    <!-- Add Category Inline Form -->
    <div class="add-new-form" id="addCategoryForm" style="margin: 20px auto; max-width: 700px;">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title text-info mb-3"><i class="fa fa-plus"></i> Add New Category</h6>
                <div class="alert alert-danger d-none alert-inline" id="categoryInlineError"></div>
                <div class="alert alert-success d-none alert-inline" id="categoryInlineSuccess"></div>
                <div class="form-row-inline">
                    <div>
                        <label class="form-label small mb-2"><strong>Category Name</strong></label>
                        <input type="text" id="newCategoryName" class="form-control form-control-sm" placeholder="e.g., Electronics">
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="saveCategoryBtnInline"><i class="fa fa-plus"></i> Add</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cancelCategoryBtnInline"><i class="fa fa-times"></i> Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Sub Category Inline Form -->
    <div class="add-new-form" id="addSubCategoryForm" style="margin: 20px auto; max-width: 700px;">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title text-info mb-3"><i class="fa fa-plus"></i> Add New Sub Category</h6>
                <div class="alert alert-danger d-none alert-inline" id="subCategoryInlineError"></div>
                <div class="alert alert-success d-none alert-inline" id="subCategoryInlineSuccess"></div>
                <div class="mb-3">
                    <label class="form-label small"><strong>Parent Category:</strong></label>
                    <p class="mb-0 text-muted small" id="selectedCategoryDisplay">-</p>
                </div>
                <div class="form-row-inline">
                    <div>
                        <label class="form-label small mb-2"><strong>Sub Category Name</strong></label>
                        <input type="text" id="newSubCategoryName" class="form-control form-control-sm" placeholder="e.g., Laptops">
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="saveSubCategoryBtnInline"><i class="fa fa-plus"></i> Add</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cancelSubCategoryBtnInline"><i class="fa fa-times"></i> Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Inline Form -->
    <div class="add-new-form" id="addProductForm" style="margin: 20px auto; max-width: 700px;">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title text-info mb-3"><i class="fa fa-plus"></i> Add New Product</h6>
                <div class="alert alert-danger d-none alert-inline" id="productInlineError"></div>
                <div class="alert alert-success d-none alert-inline" id="productInlineSuccess"></div>
                <div class="mb-3">
                    <label class="form-label small"><strong>Sub Category:</strong></label>
                    <p class="mb-0 text-muted small" id="selectedSubCategoryDisplay">-</p>
                </div>
                <div class="form-row-inline">
                    <div>
                        <label class="form-label small mb-2"><strong>Product Name</strong></label>
                        <input type="text" id="newProductName" class="form-control form-control-sm" placeholder="e.g., Dell Laptop">
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="saveProductBtnInline"><i class="fa fa-plus"></i> Add</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cancelProductBtnInline"><i class="fa fa-times"></i> Cancel</button>
                </div>
                <div class="mt-3">
                    <label class="form-label small mb-2"><strong>Unit</strong></label>
                    <select id="newProductUnit" class="form-control form-control-sm">
                        <option value="">Select Unit</option>
                        <option value="pcs">pcs</option>
                        <option value="kg">kg</option>
                        <option value="liters">liters</option>
                        <option value="meter">meter</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {

        let rowIndex = 0;

        function initSelect2() {
            $('#supplier_id, #category, #subcategory, #product').select2({
                width: '100%',
                dropdownParent: $('body')
            });
        }

        initSelect2();
        setupEventHandlers();

        function setupEventHandlers() {

            // Supplier add-new handler (only if option exists in HTML)
            $('#supplier_id').on('change', function() {
                if ($(this).val() === '__add_new__') {
                    $(this).val('').trigger('change');
                    $('#addSupplierForm').addClass('show');
                }
            });

            // Category load subcategories
            $('#category').on('change', function() {
                const val = $(this).val();

                if (val === '__add_new__') {
                    $(this).val('').trigger('change');
                    $('#addCategoryForm').addClass('show');
                    return;
                }

                if (val) loadSubCategories(val);
            });

            // Subcategory load products
            $('#subcategory').on('change', function() {
                const val = $(this).val();

                if (val === '__add_new__') {
                    $(this).val('').trigger('change');
                    $('#selectedCategoryDisplay').text($('#category option:selected').text());
                    $('#addSubCategoryForm').addClass('show');
                    return;
                }

                if (val) loadProducts(val);
            });

            // Product load unit
            $('#product').on('change', function() {
                const val = $(this).val();

                if (val === '__add_new__') {
                    $(this).val('').trigger('change');
                    $('#selectedSubCategoryDisplay').text($('#subcategory option:selected').text());
                    $('#addProductForm').addClass('show');
                    return;
                }

                if (val) loadProductUnit(val);
            });
        }

        // Load subcategories
        function loadSubCategories(category_id) {
            $.get("../sub_category/get_sub_categories.php", { category_id }, function(data) {
                $("#subcategory").html(data);

                // Reinitialize Select2 the correct way
                $('#subcategory').select2({
                    width: '100%',
                    dropdownParent: $('body')
                });
            });
        }

        // Load products
        function loadProducts(sub_id) {
            $.get("../product/get_products_by_sub.php", { sub_id }, function(data) {
                $("#product").html(data);

                $('#product').select2({
                    width: '100%',
                    dropdownParent: $('body')
                });
            });
        }

        // Load product unit
        function loadProductUnit(product_id) {
            $.get("../product/get_product_unit.php", { product_id }, function(unit) {
                $("#unit").val(unit);
            });
        }

        // Qty * Price auto total
        $("#qty, #price").on("input", function() {
            let q = parseFloat($("#qty").val()) || 0;
            let p = parseFloat($("#price").val()) || 0;
            $("#total").val((q * p).toFixed(2));
        });

        // Add item to table
        $("#addItem").click(function() {
            let pid = $("#product").val();
            let pname = $("#product option:selected").text();

            if (!pid || pid === '__add_new__') return alert("Select a valid product!");
            if ($("#qty").val() <= 0) return alert("Enter quantity!");

            let unit = $("#unit").val();
            let qty = $("#qty").val();
            let price = $("#price").val();
            let total = $("#total").val();

            let row = `
                <tr>
                    <td>${pname}<input type="hidden" name="items[${rowIndex}][product_id]" value="${pid}"></td>
                    <td>${unit}</td>
                    <td>${qty}<input type="hidden" name="items[${rowIndex}][qty]" value="${qty}"></td>
                    <td>${price}<input type="hidden" name="items[${rowIndex}][unit_price]" value="${price}"></td>
                    <td>${total}<input type="hidden" name="items[${rowIndex}][total]" value="${total}"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                </tr>
            `;

            $("#itemTable tbody").append(row);
            rowIndex++;

            // Reset item section
            $("#subcategory").html('<option value="">Select Sub Category</option>');
            $("#product").html('<option value="">Select Product</option>');
            $("#unit, #qty, #price, #total").val('');
        });

        // Remove item
        $(document).on("click", ".removeRow", function() {
            $(this).closest("tr").remove();
        });

        /* INLINE FORMS ------------------------------------------ */

        // Supplier inline add
        $('#saveSupplierBtnInline').on('click', function() {
            const name = $('#newSupplierName').val().trim();

            if (!name) return $('#supplierInlineError').text("Name required").removeClass("d-none");

            $.post('ajax_add_items.php', {
                action: 'add_supplier',
                name,
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            }, function(res) {
                if (res.success) {
                    const opt = new Option(res.data.name, res.data.id, true, true);
                    $('#supplier_id').append(opt).trigger('change');
                    $('#cancelSupplierBtnInline').click();
                }
            }, 'json');
        });

        // Category inline add
        $('#saveCategoryBtnInline').on('click', function() {
            const name = $('#newCategoryName').val().trim();

            if (!name) return $('#categoryInlineError').text("Name required").removeClass("d-none");

            $.post('ajax_add_items.php', {
                action: 'add_category',
                name,
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            }, function(res) {
                if (res.success) {
                    const opt = new Option(res.data.name, res.data.id, true, true);
                    $('#category').append(opt).trigger('change');
                    $('#cancelCategoryBtnInline').click();
                }
            }, 'json');
        });

        // Sub-category inline add
        $('#saveSubCategoryBtnInline').on('click', function() {
            const name = $('#newSubCategoryName').val().trim();
            const cat = $('#category').val();

            if (!cat) return $('#subCategoryInlineError').text("Select category").removeClass("d-none");
            if (!name) return $('#subCategoryInlineError').text("Name required").removeClass("d-none");

            $.post('ajax_add_items.php', {
                action: 'add_sub_category',
                category_id: cat,
                name,
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            }, function(res) {
                if (res.success) {
                    const opt = new Option(res.data.name, res.data.id, true, true);
                    $('#subcategory').append(opt).trigger('change');
                    $('#cancelSubCategoryBtnInline').click();
                }
            }, 'json');
        });

        // Product inline add
        $('#saveProductBtnInline').on('click', function() {
            const name = $('#newProductName').val().trim();
            const unit = $('#newProductUnit').val();
            const sub = $('#subcategory').val();
            const cat = $('#category').val();

            if (!sub) return $('#productInlineError').text("Select sub-category").removeClass("d-none");
            if (!name) return $('#productInlineError').text("Name required").removeClass("d-none");
            if (!unit) return $('#productInlineError').text("Unit required").removeClass("d-none");

            $.post('ajax_add_items.php', {
                action: 'add_product',
                category_id: cat,
                sub_category_id: sub,
                name,
                unit,
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            }, function(res) {
                if (res.success) {
                    const opt = new Option(res.data.name, res.data.id, true, true);
                    $('#product').append(opt).trigger('change');
                    $('#unit').val(res.data.unit);
                    $('#cancelProductBtnInline').click();
                }
            }, 'json');
        });
    });
}
</script>


<?php include(BASE_PATH . '/includes/footer.php'); ?>