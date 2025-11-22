<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
include('product_functions.php');
include('../category/category_functions.php');
include('../sub_category/sub_category_functions.php');

$errors = [];
$success = "";
$old = $_POST ?? [];

// Fetch all categories for dropdown
$categories = get_all_categories();
$units = ['pcs', 'kg', 'liters', 'meter']; // predefined units

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['category_id'])) $errors['category_id'] = "Category is required!";
    if (empty($_POST['sub_category_id'])) $errors['sub_category_id'] = "Sub Category is required!";
    if (empty($_POST['name'])) $errors['name'] = "Product name is required!";
    if (empty($_POST['sku'])) $errors['sku'] = "SKU is required!";
    if (empty($_POST['unit'])) $errors['unit'] = "Unit is required!";

    if (empty($errors)) {
        if (add_product($_POST)) {
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

 <div class="header-box">
    <h2 style="color: #ffffff; margin: 0;">
        Add Product
    </h2>
</div>

    <!-- <h3 class="text-center my-3">Add Product</h3> -->
    <div class="card p-4" style="max-width:700px; margin:auto;">

        <?php if(isset($errors['form'])): ?>
            <div class="alert alert-danger"><?= $errors['form'] ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" id="productForm" novalidate>

            <!-- Category -->
            <div class="mb-3">
                <label><strong>Category *</strong></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>" <?= isset($old['category_id']) && $old['category_id']==$cat['id'] ? 'selected' : '' ?>>
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
                    <?php foreach($units as $u): ?>
                        <option value="<?= $u ?>" <?= isset($old['unit']) && $old['unit']==$u ? 'selected' : '' ?>><?= $u ?></option>
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

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
$(document).ready(function(){

    // Load subcategories if category pre-selected
    let selectedSub = <?= json_encode($old['sub_category_id'] ?? '') ?>;
    let selectedCategory = $('#category_id').val();
    if(selectedCategory){
        loadSubCategories(selectedCategory, selectedSub);
    }

    $('#category_id').change(function(){
        loadSubCategories($(this).val(), '');
        generateSKU();
    });

    $('#sub_category_id, #name').on('input change', function(){
        generateSKU();
    });

    function loadSubCategories(category_id, selectedSub=''){
        if(category_id){
            $.get('get_sub_categories.php', {category_id: category_id}, function(res){
                $('#sub_category_id').html(res);
                if(selectedSub) $('#sub_category_id').val(selectedSub);
                generateSKU();
            });
        } else {
            $('#sub_category_id').html('<option value="">Select Sub Category</option>');
        }
    }

    function generateSKU(){
        let cat = $('#category_id option:selected').text().trim();
        let sub = $('#sub_category_id option:selected').text().trim();
        let name = $('#name').val().trim();
        if(name){
            // Best approach: use first 3 letters of category/sub + name without spaces, all uppercase
            let sku = cat.substring(0,3).toUpperCase() + '-' + sub.substring(0,3).toUpperCase() + '-' + name.replace(/\s+/g,'').toUpperCase();
            $('#sku').val(sku);
        } else {
            $('#sku').val('');
        }
    }

    // Real-time validation like supplier
    $('#name, #sku, #unit, #category_id, #sub_category_id').on('input change', function(){
        let errorId = '#' + $(this).attr('id') + 'Error';
        if($(this).val().trim() === '') $(this).addClass('is-invalid').removeClass('is-valid').siblings(errorId).removeClass('d-none');
        else $(this).removeClass('is-invalid').addClass('is-valid').siblings(errorId).addClass('d-none');
    });

});
</script>
