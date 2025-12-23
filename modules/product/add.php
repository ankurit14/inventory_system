<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');
include('product_functions.php');
include('../category/category_functions.php');
include('../sub_category/sub_category_functions.php');

$errors = [];
$success = "";
$old = $_POST ?? [];

// If request ID comes from Approve & Add
if (!empty($_GET['req_id']) && intval($_GET['req_id']) > 0) {
    $req_id = intval($_GET['req_id']);

    // Fetch request details
    $req_sql = mysqli_query($conn, 
        "SELECT * FROM new_product_requests WHERE id=$req_id"
    );

    if ($req_sql && mysqli_num_rows($req_sql) > 0) {
        $req = mysqli_fetch_assoc($req_sql);

        // Pre-fill fields
        $old['name'] = $req['product_name'];
        $old['description'] = $req['description'] ?? '';

        // Pre-select dropdowns if you stored category/sub-category in request
        if (!empty($req['category_id'])) {
            $old['category_id'] = $req['category_id'];
        }
        if (!empty($req['sub_category_id'])) {
            $old['sub_category_id'] = $req['sub_category_id'];
        }
    }
}



// Fetch all categories for dropdown
$categories = get_all_categories();
$units = ['pcs', 'kg', 'liters', 'meter']; // predefined units

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     if (empty($_POST['category_id'])) $errors['category_id'] = "Category is required!";
//     if (empty($_POST['sub_category_id'])) $errors['sub_category_id'] = "Sub Category is required!";
//     if (empty($_POST['name'])) $errors['name'] = "Product name is required!";
//     if (empty($_POST['sku'])) $errors['sku'] = "SKU is required!";
//     if (empty($_POST['unit'])) $errors['unit'] = "Unit is required!";

//     if (empty($errors)) {
//         if (add_product($_POST)) {
//             $success = "Product added successfully!";
//             $old = [];
//         } else {
//             $errors['form'] = "Something went wrong!";
//         }
//     }
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['category_id'])) $errors['category_id'] = "Category is required!";
    if (empty($_POST['sub_category_id'])) $errors['sub_category_id'] = "Sub Category is required!";
    if (empty($_POST['name'])) $errors['name'] = "Product name is required!";
    if (empty($_POST['sku'])) $errors['sku'] = "SKU is required!";
    if (empty($_POST['unit'])) $errors['unit'] = "Unit is required!";

    if (empty($errors)) {

        if (add_product($_POST)) {

            // ---------------------------------------------------------
            // ✅ NEW CODE: Approve & Add Flow (update request row)
            // ---------------------------------------------------------
            if (!empty($_GET['req_id']) && intval($_GET['req_id']) > 0) {
                $req_id = intval($_GET['req_id']);
                $new_product_id = intval(mysqli_insert_id($conn));

                mysqli_query(
                    $conn,
                    "UPDATE new_product_requests 
                     SET status='approved', product_id=$new_product_id 
                     WHERE id=$req_id"
                );
            }
            // ---------------------------------------------------------

            $success = "Product added successfully!";
            $old = [];

        } else {
            $errors['form'] = "Something went wrong!";
        }
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
        <h2 style="color: #ffffff; margin: 0;">Add Product</h2>
    </div>

    <div class="card p-4" style="max-width:700px; margin:auto;">

                <?php if ($error): ?>
                    <div class="alert alert-danger fade show auto-hide-alert">
                        <strong>Error!</strong> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success fade show auto-hide-alert">
                        <strong>Success!</strong> <?= $success ?>
                    </div>
                <?php endif; ?>

        <form method="POST" id="productForm" novalidate>

            <!-- Category -->
            <div class="mb-3">
                <label><strong>Category *</strong></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php
                    mysqli_data_seek($categories, 0);
                    while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>" <?= isset($old['category_id']) && $old['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <small id="categoryError" class="text-danger d-none">Category is required!</small>
            </div>

            
       

            <!-- Sub Category -->
            <div class="mb-3">
                <label><strong>Sub Category *</strong></label>
                <select name="sub_category_id" id="sub_category_id" class="form-control" required>
                    <option value="">Select Sub Category</option>
                </select>
                <small id="subCategoryError" class="text-danger d-none">Sub Category is required!</small>
            </div>

              

            <!-- Product Name -->
            <div class="mb-3">
                <label><strong>Product Name *</strong></label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                <small id="nameError" class="text-danger d-none">Product name is required!</small>
            </div>

            <!-- SKU -->
            <div class="mb-3">
                <label><strong>SKU *</strong></label>
                <input type="text" name="sku" id="sku" class="form-control" readonly value="<?= htmlspecialchars($old['sku'] ?? '') ?>" required>
                <small id="skuError" class="text-danger d-none">SKU is required!</small>
            </div>

            <!-- Unit -->
            <div class="mb-3">
                <label><strong>Unit *</strong></label>
                <select name="unit" id="unit" class="form-control" required>
                    <option value="">Select Unit</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u ?>" <?= isset($old['unit']) && $old['unit'] == $u ? 'selected' : '' ?>><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
                <small id="unitError" class="text-danger d-none">Unit is required!</small>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label><strong>Description</strong></label>
                <textarea name="description" class="form-control"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <button class="btn btn-primary w-100">Add Product</button>
        </form>


     

    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>

<script>
    if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {

    // ---------- Select2: CATEGORY ----------
    $('#category_id').select2({
        tags: true,
        placeholder: "Select or Add Category",
        width: "100%",
        createTag: function(params) {
            let term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: "new:" + term,
                text: '➕ Add "' + term + '"',
                newOption: true
            };
        },
        templateResult: function(data) {
            if (data.newOption) {
                return $('<span style="color:#28a745;font-weight:600;">' + data.text + '</span>');
            }
            return data.text;
        }
    });

    // When new category selected
    $('#category_id').on('select2:select', function(e) {
        let data = e.params.data;

        // If it's a NEW category
        if (data.id.startsWith("new:")) {
            let newName = data.id.replace("new:", "");

            $.post("ajax_add_category.php", {
                action: "add_category",
                name: newName
            }, function(res) {

                if (res.success) {
                    // Remove temporary tag
                    $("#category_id option[value='" + data.id + "']").remove();

                    // Add new ID from DB
                    let newOption = new Option(res.data.name, res.data.id, true, true);
                    $("#category_id").append(newOption).trigger("change");

                    // Load subcategories fresh
                    loadSubCategories(res.data.id);

                } else {
                    alert(res.message);
                }

            }, "json");
        } else {
            // Normal existing category
            loadSubCategories(data.id);
        }
    });



    // ---------- Select2: SUB CATEGORY ----------
    $('#sub_category_id').select2({
        tags: true,
        placeholder: "Select or Add Sub Category",
        width: "100%",
        createTag: function(params) {
            let term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: "new:" + term,
                text: '➕ Add "' + term + '"',
                newOption: true
            };
        },
        templateResult: function(data) {
            if (data.newOption) {
                return $('<span style="color:#28a745;font-weight:bold;">' + data.text + '</span>');
            }
            return data.text;
        }
    });

    // When new sub category selected
    $('#sub_category_id').on('select2:select', function(e) {
        let data = e.params.data;

        if (data.id.startsWith("new:")) {
            let newName = data.id.replace("new:", "");
            let categoryId = $('#category_id').val();

            if (!categoryId) {
                alert("Select category first!");
                $('#sub_category_id').val("").trigger("change");
                return;
            }

            $.post("ajax_add_category.php", {
                action: "add_sub_category",
                category_id: categoryId,
                name: newName
            }, function(res) {

                if (res.success) {
                    $("#sub_category_id option[value='" + data.id + "']").remove();

                    let newOption = new Option(res.data.name, res.data.id, true, true);
                    $("#sub_category_id").append(newOption).trigger("change");

                    generateSKU();

                } else {
                    alert(res.message);
                }

            }, "json");
        } else {
            generateSKU();
        }
    });

    

    // ---------- Load Sub Categories ----------
    function loadSubCategories(category_id, selectedSub = '') {
        $.get('get_sub_categories.php', { category_id }, function(res) {
            $('#sub_category_id').html(res);
            if (selectedSub) $('#sub_category_id').val(selectedSub);
            $('#sub_category_id').trigger("change");
        });
    }


    // ---------- SKU Generator ----------
    function generateSKU() {
        let cat = $('#category_id option:selected').text().trim();
        let sub = $('#sub_category_id option:selected').text().trim();
        let name = $('#name').val().trim();

        if (cat.includes('➕')) return; // ignore temporary tags
        if (sub.includes('➕')) return;

        if (name && cat && sub) {
            let sku = cat.substring(0, 3).toUpperCase() + '-' +
                      sub.substring(0, 3).toUpperCase() + '-' +
                      name.replace(/\s+/g, '').toUpperCase();

            $('#sku').val(sku);
        }
    }

    $('#name, #sub_category_id, #category_id').on('change input', generateSKU);

 setTimeout(function () {
        $('.auto-hide-alert').fadeOut('slow');
    }, 2000); 
           
        });
    }

    
</script>