<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
include('supplier_functions.php');

$errors = [];
$success = "";
$old = $_POST ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['name'])) $errors['name'] = "Supplier name is required!";
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
        $errors['email'] = "Invalid email format!";
    if (!empty($_POST['phone']) && !preg_match('/^[0-9]{10}$/', $_POST['phone'])) 
        $errors['phone'] = "Phone must be 10 digits!";

    if (empty($errors)) {
        if (add_supplier($_POST)) {
            $success = "Supplier added successfully!";
            $old = [];
        } else {
            $errors['form'] = "Something went wrong!";
        }
    }
}
?>

<div class="pcoded-content">

<h2 class="text-center my-3">Add Supplier</h2>

<div class="card p-4" style="max-width: 700px; margin:auto;">

<?php if(isset($errors['form'])): ?>
    <div class="alert alert-danger"><?= $errors['form'] ?></div>
<?php endif; ?>

<?php if($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" id="supplierForm" novalidate>

    <!-- Supplier Name -->
    <div class="form-group mb-3">
        <label><strong>Supplier Name *</strong></label>
        <input type="text" id="name" name="name" class="form-control"
               value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
        <small id="nameError" class="text-danger d-none">Supplier name is required!</small>
    </div>

    <!-- Phone -->
    <div class="form-group mb-3">
        <label><strong>Phone (10 digits)</strong></label>
        <input type="text" id="phone" name="phone" class="form-control"
               maxlength="10"
               value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
        <small id="phoneError" class="text-danger d-none">Phone must be 10 digits!</small>
    </div>

    <!-- Email -->
    <div class="form-group mb-3">
        <label><strong>Email</strong></label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        <small id="emailError" class="text-danger d-none">Invalid email format!</small>
    </div>

    <!-- Address -->
    <div class="form-group mb-3">
        <label><strong>Address</strong></label>
        <textarea name="address" class="form-control"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
    </div>

    <!-- GST -->
    <div class="form-group mb-3">
        <label><strong>GST No</strong></label>
        <input type="text" name="gst_no" class="form-control"
               value="<?= htmlspecialchars($old['gst_no'] ?? '') ?>">
    </div>

    <button class="btn btn-primary w-100 mt-2">Save Supplier</button>

</form>

</div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<!-- 🔥 REAL TIME VALIDATION SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    // Supplier Name
    document.getElementById('name').addEventListener('input', function() {
        let val = this.value.trim();
        let err = document.getElementById('nameError');

        if (val.length === 0) {
            this.classList.add("is-invalid");
            err.classList.remove("d-none");
        } else {
            this.classList.remove("is-invalid");
            err.classList.add("d-none");
        }
    });

    // Phone validation
    document.getElementById('phone').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, ''); // digits only
        let err = document.getElementById('phoneError');

        if (this.value.length !== 0 && this.value.length !== 10) {
            this.classList.add("is-invalid");
            err.classList.remove("d-none");
        } else {
            this.classList.remove("is-invalid");
            err.classList.add("d-none");
        }
    });

    // Email validation
    document.getElementById('email').addEventListener('input', function() {
        let email = this.value;
        let err = document.getElementById('emailError');

        let pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email.length > 0 && !pattern.test(email)) {
            this.classList.add("is-invalid");
            err.classList.remove("d-none");
        } else {
            this.classList.remove("is-invalid");
            err.classList.add("d-none");
        }
    });

});
</script>
