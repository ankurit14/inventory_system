<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');
include('../suppliers/supplier_functions.php');
include('../category/category_functions.php');
include('../sub_category/sub_category_functions.php');
include('../product/product_functions.php');
include('../stock/stock_functions.php');
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
                    <select name="supplier_id" id="supplierDropdown" class="form-control" required>
                        <option value="">Select Supplier</option>
                        <?php while ($s = mysqli_fetch_assoc($suppliers)): ?>
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
                        // Reset pointer for categories
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
                    <input type="number" id="qty" class="form-control" min="0" step="0.01">
                </div>

                <div class="col-md-2 mb-2">
                    <label>Unit Price</label>
                    <input type="number" id="price" class="form-control" min="0" step="0.01">
                </div>

                <div class="col-md-2 mb-2">
                    <label>Total</label>
                    <input type="number" id="total" class="form-control" readonly>
                </div>

                <div class="col-md-2 mb-2">
                    <label>&nbsp;</label>
                    <button type="button" id="addItem" class="btn btn-primary form-control" style="width: 100px;">
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

        <div class="text-end mx-2">
            <button class="btn btn-success" style="width: 150px;">Save Purchase</button>
        </div>

    </form>
</div>


<?php include(BASE_PATH . '/includes/footer.php'); ?>

<script>
    if (typeof jQuery !== 'undefined') {

        jQuery(document).ready(function($) {
            console.log("jQuery version: " + $.fn.jquery);
            // INITIALIZE SELECT2 PROPERLY
            $("#supplierDropdown, #category, #subcategory, #product").select2({
                width: "100%",
                tags: true,
                createTag: function(params) {
                    let term = $.trim(params.term);
                    if (term === "") return null;

                    return {
                        id: "new:" + term,
                        text: term,
                        newOption: true
                    };
                }
            });


            let rowIndex = 0;

            // ---------- SUPPLIER ----------
            $("#supplierDropdown").on("select2:select", function(e) {
                let data = e.params.data;

                if (data.newOption) {
                    let name = data.text;

                    $.post("ajax_add_supplier.php", {
                        name: name
                    }, function(id) {
                        if (id > 0) {
                            // replace temporary option with real DB ID
                            let newOption = new Option(name, id, true, true);
                            $("#supplierDropdown")
                                .append(newOption)
                                .trigger("change");
                        }
                    });
                }
            });


            // ---------- CATEGORY ----------
            $("#category").on("select2:select", function(e) {
                let data = e.params.data;

                if (data.newOption) {
                    let name = data.text;

                    $.post("ajax_add_category.php", {
                        name: name
                    }, function(id) {
                        if (id > 0) {
                            let op = new Option(name, id, true, true);
                            $("#category").append(op).trigger("change");
                        }
                    });
                }
            });

            // When category changes → load all subcategories
            $("#category").on("change", function() {
                let catId = $(this).val();
                if (catId) {
                    loadSubcategories(catId);
                } else {
                    $("#subcategory").html('<option value="">Select Sub Category</option>');
                }
            });



            $("#saveCategory").click(function() {
                let name = $("#categoryName").val();

                $.post("ajax_add_category.php", {
                    name: name
                }, function(id) {
                    if (id > 0) {
                        $("#category").append(`<option value="${id}" selected>${name}</option>`);
                        $("#addCategoryModal").modal("hide");
                        $("#categoryName").val("");

                        $("#category").trigger("change.select2");

                        loadSubcategories(id);
                    }
                });
            });


            // ---------- SUBCATEGORY ----------
            $("#subcategory").on("select2:select", function(e) {
                let data = e.params.data;

                if (data.newOption) {
                    let name = data.text;
                    let categoryId = $("#category").val();

                    if (!categoryId) return alert("Pick a category first");

                    $.post("ajax_add_subcategory.php", {
                        name: name,
                        category_id: categoryId
                    }, function(id) {
                        if (id > 0) {
                            let op = new Option(name, id, true, true);
                            $("#subcategory").append(op).trigger("change");
                        }
                    });
                }
            });


            // When subcategory changes → load products
$("#subcategory").on("change", function () {
    let subId = $(this).val();
    if (subId) {
        loadProducts(subId);
    } else {
        $("#product").html('<option value="">Select Product</option>');
    }
});

let skipUnitAutoFill = false;

// Auto-fill unit whenever a product is selected
$("#product").on("change", function () {
    if (skipUnitAutoFill) {
        skipUnitAutoFill = false;
        return;
    }

    let pid = $(this).val();
    if (!pid || pid.startsWith("new:")) return;

    $.get("ajax_get_product_unit.php", { product_id: pid }, function (unit) {
        $("#unit").val(unit).trigger("change-unit-locked");

    });
});

// When adding new product
$("#product").on("select2:select", function (e) {
    let data = e.params.data;

    if (data.newOption) {
        let name = data.text.trim();
        let cat = $("#category").val();
        let sub = $("#subcategory").val();

        let unit = prompt("Enter unit (pcs, kg, box, etc):");
        if (!unit) return;

        // Auto-generate SKU
        let catText = $('#category option:selected').text().trim();
        let subText = $('#subcategory option:selected').text().trim();
        let sku = catText.substring(0, 3).toUpperCase()
                + '-' +
                subText.substring(0, 3).toUpperCase()
                + '-' +
                name.replace(/\s+/g, '').toUpperCase();

        $.post("ajax_add_product.php", {
            name: name,
            category_id: cat,
            sub_category_id: sub,
            unit: unit,
            sku: sku
        }, function (id) {
            if (id > 0) {
                let op = new Option(name, id, true, true);
                $("#product").append(op).trigger("change");

                // Autofill unit in form
                $("#unit").val(unit);
                $("#sku").val(sku);
            }
        });
    }
});

            // ------- Loading Subcategories -------
            function loadSubcategories(catId) {
                $.get("get_sub_categories.php", {
                    category_id: catId
                }, function(data) {

                    $("#subcategory").html(data);

                    $("#subcategory").trigger("change.select2");
                });
            }

            // ------- Loading Products -------
            function loadProducts(subId) {
                $.get("../product/get_products_by_sub.php", {
                    sub_id: subId
                }, function(data) {
                    $("#product").html(data);
                    $("#product").trigger("change.select2");
                });
            }

            // Calculate total
            $("#qty, #price").on("input", function() {
                let q = parseFloat($("#qty").val()) || 0;
                let p = parseFloat($("#price").val()) || 0;
                $("#total").val((q * p).toFixed(2));
            });

            // Add item row
            $("#addItem").click(function() {
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
            $(document).on("click", ".removeRow", function() {
                $(this).closest("tr").remove();
            });

            // Close modal on escape
            $(document).on('keydown', function(e) {
                if (e.key === "Escape") {
                    $('.modal').modal('hide');
                }
            });
      
            function generateSKU() {
    let cat = $('#category_id option:selected').text().trim();
    let sub = $('#sub_category_id option:selected').text().trim();
    let name = $('#name').val().trim();

    if (cat && sub && name) {
        let sku = 
            cat.substring(0, 3).toUpperCase() + "-" +
            sub.substring(0, 3).toUpperCase() + "-" +
            name.replace(/\s+/g, '').toUpperCase();

        $('#sku').val(sku);
    } else {
        $('#sku').val('');
    }
}

$('#category_id, #sub_category_id, #name').on('change keyup', generateSKU);


      
        });

    }
</script>