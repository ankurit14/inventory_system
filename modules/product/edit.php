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

// Get product id
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Product ID");
}
$id = intval($_GET['id']);

// Fetch product
$res = get_product($id);
$product = mysqli_fetch_assoc($res);
if(!$product) die("Product not found");

// Old data: use $_POST if form submitted, else database values
$old = $_POST ?: $product;

// Categories and Units (same as Add Product)
$categories = get_all_categories();
$units = ['pcs','kg','liters','meter'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['category_id'])) $errors['category_id'] = "Category is required!";
    if (empty($_POST['sub_category_id'])) $errors['sub_category_id'] = "Sub Category is required!";
    if (empty($_POST['name'])) $errors['name'] = "Product name is required!";
    if (empty($_POST['sku'])) $errors['sku'] = "SKU is required!";
    if (empty($_POST['unit'])) $errors['unit'] = "Unit is required!";

    if(empty($errors)){
        if(update_product($id, $_POST)){
            $success = "Product updated successfully!";
            $old = $_POST;
        } else {
            $errors['form'] = "Something went wrong!";
        }
    }
}
?>

<div class="pcoded-content">
    <h3 class="text-center my-3">Edit Product</h3>
    <div class="card p-4" style="max-width:700px;margin:auto;">

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
                        <option value="<?= $cat['id'] ?>" <?= ($old['category_id']==$cat['id'])?'selected':'' ?>>
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

            <!-- Name -->
            <div class="mb-3">
                <label><strong>Product Name *</strong></label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($old['name']) ?>" required>
                <small id="nameError" class="text-danger d-none">Product name is required!</small>
            </div>

            <!-- SKU -->
            <div class="mb-3">
                <label><strong>SKU *</strong></label>
                <input type="text" name="sku" id="sku" class="form-control" value="<?= htmlspecialchars($old['sku']) ?>" required readonly>
                <small id="skuError" class="text-danger d-none">SKU is required!</small>
            </div>

            <!-- Unit -->
            <div class="mb-3">
                <label><strong>Unit *</strong></label>
                <select name="unit" id="unit" class="form-control" required>
                    <option value="">Select Unit</option>
                    <?php foreach($units as $u): ?>
                        <option value="<?= $u ?>" <?= ($old['unit'] == $u) ? 'selected' : '' ?>><?= ucfirst($u) ?></option>
                    <?php endforeach; ?>
                </select>
                <small id="unitError" class="text-danger d-none">Unit is required!</small>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label><strong>Description</strong></label>
                <textarea name="description" class="form-control"><?= htmlspecialchars($old['description']) ?></textarea>
            </div>

            <button class="btn btn-primary w-100">Update Product</button>
        </form>
    </div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
$(document).ready(function(){

    // Load subcategories on page load
    let selectedCategory = $('#category_id').val();
    let selectedSub = <?= json_encode($old['sub_category_id']) ?>;
    if(selectedCategory){
        $.get('get_sub_categories.php', {category_id: selectedCategory}, function(res){
            $('#sub_category_id').html(res);
            $('#sub_category_id').val(selectedSub);
        });
    }

    // Change category -> reload subcategories
    $('#category_id').change(function(){
        let cat_id = $(this).val();
        $.get('get_sub_categories.php',{category_id: cat_id}, function(res){
            $('#sub_category_id').html(res);
            generateSKU();
        });
    });

    // Change subcategory or name -> update SKU
    $('#sub_category_id,#name').on('input change', generateSKU);

    function generateSKU(){
        let cat = $('#category_id option:selected').text().trim();
        let sub = $('#sub_category_id option:selected').text().trim();
        let name = $('#name').val().trim();
        if(name){
            $('#sku').val(cat.substring(0,3).toUpperCase() + '-' + sub.substring(0,3).toUpperCase() + '-' + name.replace(/\s+/g,'').toUpperCase());
        } else {
            $('#sku').val('');
        }
    }

});
</script>
