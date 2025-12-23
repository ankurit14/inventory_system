<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid Asset ID'); window.location='index.php';</script>";
    exit;
}

$asset_id = intval($_GET['id']);
$errors = [];
$success = "";

/* Fetch existing asset */
$q = mysqli_query($conn, "SELECT * FROM company_assets WHERE id = $asset_id LIMIT 1");
if (mysqli_num_rows($q) === 0) {
    echo "<script>alert('Asset not found'); window.location='index.php';</script>";
    exit;
}
$asset = mysqli_fetch_assoc($q);
$old = $_POST ? $_POST : $asset;

/* FORM SUBMISSION */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function clean($v){ 
        // return trim(htmlspecialchars($v)); 
         return trim($v);
    }

    $asset_name       = clean($_POST['asset_name'] ?? '');
    $category         = clean($_POST['category'] ?? '');
    $brand            = clean($_POST['brand'] ?? '');
    $model            = clean($_POST['model'] ?? '');
    $serial_no        = clean($_POST['serial_no'] ?? '');
    $purchase_date    = $_POST['purchase_date'] ?? null;
    $purchase_price   = $_POST['purchase_price'] ?? null;
    $qty              = intval($_POST['qty'] ?? 1);
    $assigned_to      = clean($_POST['assigned_to'] ?? '');
    $current_location = clean($_POST['current_location'] ?? '');
    $floor            = clean($_POST['floor'] ?? 'Ground Floor');
    $status           = clean($_POST['status'] ?? 'Available');
    $note             = clean($_POST['note'] ?? '');
    $bill_image_name  = $asset['bill_image'];

    /* VALIDATIONS */
    if ($asset_name === "") { $errors['asset_name'] = "Asset name is required."; }
    if ($purchase_price !== "" && !is_numeric($purchase_price)) { $errors['purchase_price'] = "Price must be a valid number."; }
    if ($qty < 1) { $errors['qty'] = "Quantity must be at least 1."; }
    if ($floor === "") { $errors['floor'] = "Floor is required."; }

    /* BILL IMAGE UPLOAD */
    if (isset($_FILES['bill_image']) && $_FILES['bill_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = BASE_PATH . '/uploads/assets/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        $img_name = time() . "_" . basename($_FILES['bill_image']['name']);
        $img_path = $upload_dir . $img_name;
        if (move_uploaded_file($_FILES['bill_image']['tmp_name'], $img_path)) {
            $bill_image_name = $img_name;
        } else {
            $errors['bill_image'] = "Failed to upload bill image.";
        }
    }

    /* UPDATE DB */
    if (empty($errors)) {
        $sql = "UPDATE company_assets SET
            asset_name=?, category=?, brand=?, model=?, serial_no=?, purchase_date=?,
            purchase_price=?, qty=?, assigned_to=?, current_location=?, floor=?, status=?, note=?, bill_image=?
            WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssdissssssi",
            $asset_name, $category, $brand, $model, $serial_no,
            $purchase_date, $purchase_price, $qty, $assigned_to,
            $current_location, $floor, $status, $note, $bill_image_name, $asset_id
        );
        if (mysqli_stmt_execute($stmt)) {
            $success = "Asset updated successfully!";
            $old['bill_image'] = $bill_image_name;
        } else {
            $errors['form'] = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<style>
.header-box { background: linear-gradient(135deg, #4e73df, #1cc88a); padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
.header-box h2 { color: #fff; margin: 0; text-align: center; }
.card { border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
</style>

<div class="pcoded-content">
<div class="header-box"><h2>Edit Asset</h2></div>
<div class="container">

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if (!empty($errors['form'])): ?>
    <div class="alert alert-danger"><?= $errors['form'] ?></div>
<?php endif; ?>

<div class="card mt-3">
<div class="card-body p-4">

<form method="POST" enctype="multipart/form-data">

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Asset Name *</label>
        <input type="text" name="asset_name" class="form-control" value="<?= $old['asset_name'] ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label>Category</label>
        <input type="text" name="category" class="form-control" value="<?= $old['category'] ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label>Brand</label>
        <input type="text" name="brand" class="form-control" value="<?= $old['brand'] ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label>Model</label>
        <input type="text" name="model" class="form-control" value="<?= $old['model'] ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label>Serial No.</label>
        <input type="text" name="serial_no" class="form-control" value="<?= $old['serial_no'] ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label>Purchase Date</label>
        <input type="date" name="purchase_date" class="form-control" value="<?= $old['purchase_date'] ?>">
    </div>
    <div class="col-md-3 mb-3">
        <label>Purchase Price</label>
        <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= $old['purchase_price'] ?>">
    </div>
    <div class="col-md-3 mb-3">
        <label>Quantity *</label>
        <input type="number" name="qty" min="1" class="form-control" value="<?= $old['qty'] ?>" required>
    </div>
    <div class="col-md-3 mb-3">
        <label>Assigned To</label>
        <input type="text" name="assigned_to" class="form-control" value="<?= $old['assigned_to'] ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Current Location</label>
        <input type="text" name="current_location" class="form-control" value="<?= $old['current_location'] ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label>Floor *</label>
        <select name="floor" class="form-select" required>
            <?php
            $floors = ["Ground Floor","First Floor","Second Floor","Third Floor","Fourth Floor"];
            foreach ($floors as $f): ?>
                <option value="<?= $f ?>" <?= ($old['floor'] == $f) ? 'selected' : '' ?>><?= $f ?></option>
            <?php endforeach; ?>
        </select>
        <?php if(isset($errors['floor'])): ?>
            <small class="text-danger"><?= $errors['floor'] ?></small>
        <?php endif; ?>
    </div>
</div>

<div class="mb-3">
    <label>Status</label>
    <select name="status" class="form-select">
        <?php
        $statuses = ["Available","In Use","Maintenance","Lost","Scrap"];
        foreach ($statuses as $s): ?>
            <option value="<?= $s ?>" <?= ($old['status'] == $s) ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-3">
    <label>Notes</label>
    <textarea name="note" class="form-control"><?= $old['note'] ?></textarea>
</div>

<div class="mb-3">
    <label>Bill Image</label>
    <input type="file" name="bill_image" class="form-control" accept="image/*" id="bill_image_input">
    <div class="mt-2">
        <img id="bill_image_preview" src="<?= !empty($old['bill_image']) ? BASE_URL.'/uploads/assets/'.$old['bill_image'] : '' ?>" style="max-width:150px; max-height:150px;">
    </div>
    <?php if(isset($errors['bill_image'])): ?>
        <small class="text-danger"><?= $errors['bill_image'] ?></small>
    <?php endif; ?>
</div>

<div class="text-end">
    <a href="index.php" class="btn btn-secondary">Back</a>
    <button class="btn btn-primary">Update Asset</button>
</div>

</form>
</div></div></div>

<script>
document.getElementById('bill_image_input').addEventListener('change', function(event){
    const preview = document.getElementById('bill_image_preview');
    const file = event.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
    }
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
