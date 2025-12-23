<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

$errors = [];
$success = "";
$old = $_POST ?? [];

/* -----------------------------
   FORM SUBMISSION
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    function clean($value){
        // return trim(htmlspecialchars($value));
         return trim($value);
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

    /* ---------------- VALIDATIONS ---------------- */
    if ($asset_name === "") {
        $errors['asset_name'] = "Asset name is required.";
    }

    if ($purchase_price !== "" && !is_numeric($purchase_price)) {
        $errors['purchase_price'] = "Price must be a valid number.";
    }

    if ($qty < 1) {
        $errors['qty'] = "Quantity must be at least 1.";
    }

    if ($floor === "") {
        $errors['floor'] = "Floor is required.";
    }

    /* ---------------- BILL IMAGE ---------------- */
    $bill_image = "";

    if (isset($_FILES['bill_image']) && $_FILES['bill_image']['error'] === UPLOAD_ERR_OK) {

        $upload_dir = BASE_PATH . '/uploads/assets/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $img_name = time() . "_" . basename($_FILES['bill_image']['name']);
        $img_path = $upload_dir . $img_name;

        if (move_uploaded_file($_FILES['bill_image']['tmp_name'], $img_path)) {
            $bill_image = $img_name;
        } else {
            $errors['bill_image'] = "Failed to upload bill image.";
        }
    }

    /* ---------------- INSERT ---------------- */
    if (empty($errors)) {

        $sql = "INSERT INTO company_assets 
        (asset_name, category, brand, model, serial_no, purchase_date, purchase_price, qty,
         assigned_to, current_location, floor, status, note, bill_image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssdissssss",
            $asset_name,
            $category,
            $brand,
            $model,
            $serial_no,
            $purchase_date,
            $purchase_price,
            $qty,
            $assigned_to,
            $current_location,
            $floor,
            $status,
            $note,
            $bill_image
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = "Asset added successfully!";
            $old = [];
        } else {
            $errors['form'] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<style>
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    margin: 0;
    text-align: center;
}
.card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
</style>

<div class="pcoded-content">

<div class="header-box">
    <h2>Add New Asset</h2>
</div>

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
        <label class="form-label">Asset Name *</label>
        <input type="text" name="asset_name" class="form-control"
               value="<?= $old['asset_name'] ?? '' ?>" required>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control"
               value="<?= $old['category'] ?? '' ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Brand</label>
        <input type="text" name="brand" class="form-control"
               value="<?= $old['brand'] ?? '' ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Model</label>
        <input type="text" name="model" class="form-control"
               value="<?= $old['model'] ?? '' ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Serial No.</label>
        <input type="text" name="serial_no" class="form-control"
               value="<?= $old['serial_no'] ?? '' ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Purchase Date</label>
        <input type="date" name="purchase_date" class="form-control"
               value="<?= $old['purchase_date'] ?? '' ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Purchase Price</label>
        <input type="number" step="0.01" name="purchase_price" class="form-control"
               value="<?= $old['purchase_price'] ?? '' ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Quantity *</label>
        <input type="number" name="qty" min="1" class="form-control"
               value="<?= $old['qty'] ?? 1 ?>" required>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Assigned To</label>
        <input type="text" name="assigned_to" class="form-control"
               value="<?= $old['assigned_to'] ?? '' ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Current Location</label>
        <input type="text" name="current_location" class="form-control"
               value="<?= $old['current_location'] ?? '' ?>">
    </div>

    <!-- ✅ FLOOR DROPDOWN -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Floor *</label>
        <select name="floor" class="form-select" required>
            <?php
            $floors = [
                "Ground Floor",
                "First Floor",
                "Second Floor",
                "Third Floor",
                "Fourth Floor"
            ];
            $selectedFloor = $old['floor'] ?? 'Ground Floor';
            foreach ($floors as $f):
            ?>
                <option value="<?= $f ?>" <?= ($selectedFloor == $f) ? 'selected' : '' ?>>
                    <?= $f ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if(isset($errors['floor'])): ?>
            <small class="text-danger"><?= $errors['floor'] ?></small>
        <?php endif; ?>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <?php
        $statuses = ["Available","In Use","Maintenance","Lost","Scrap"];
        foreach ($statuses as $s):
        ?>
        <option value="<?= $s ?>" <?= (($old['status'] ?? 'Available') == $s) ? 'selected' : '' ?>>
            <?= $s ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="note" class="form-control"><?= $old['note'] ?? '' ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Bill Image</label>
    <input type="file" name="bill_image" class="form-control" accept="image/*">
</div>

<div class="text-end">
    <a href="index.php" class="btn btn-secondary">Back</a>
    <button class="btn btn-primary">Save Asset</button>
</div>

</form>

</div>
</div>

</div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
