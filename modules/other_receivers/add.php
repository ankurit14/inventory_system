<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/users_functions.php');
include(BASE_PATH . '/includes/sidebar.php');

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
// Clear session messages after reading
unset($_SESSION['error'], $_SESSION['success']);
?>
<style>
.form-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    margin: 0;
    font-size: 24px;
}
.alert {
    text-align: center;
    margin: 20px auto;
    width: 80%;
}
</style>

<div class="pcoded-content">

    <div class="header-box">
        <h2>Add New Receiver</h2>
    </div>

   

    <div class="form-container card p-4">

     <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show auto-hide-alert">
            <strong>Error!</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show auto-hide-alert">
            <strong>Success!</strong> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>


        
        <form action="save.php" method="post" id="receiverForm" onsubmit="return validateReceiver();">
            <input type="hidden" name="action" value="add">

            <div class="form-group mb-3">
                <label>Name *</label>
                <input type="text" name="name" id="name" class="form-control" oninput="validateName()" required>
                <small class="text-danger" id="nameError"></small>
            </div>

            <div class="form-group mb-3">
                <label>Mobile</label>
                <input type="text" name="mobile" id="mobile" class="form-control" maxlength="10" oninput="validateMobile()">
                <small class="text-danger" id="mobileError"></small>
            </div>

            <div class="form-group mb-3">
                <label>Address</label>
                <input type="text" name="address" id="address" class="form-control">
                <small class="text-danger" id="addressError"></small>
            </div>

            <div class="form-group mb-3">
                <label>Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Select Status</option>
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
                <small class="text-danger" id="statusError"></small>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-success">Save</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<script>
// ------------------------
// NAME VALIDATION
// ------------------------
function validateName() {
    let name = document.getElementById("name").value.trim();
    let nameError = document.getElementById("nameError");
    if(name === "") {
        nameError.innerHTML = "Name is required.";
        return false;
    }
    if(!/^[A-Za-z ]+$/.test(name)) {
        nameError.innerHTML = "Name must contain only alphabets.";
        return false;
    }
    nameError.innerHTML = "";
    return true;
}

// ------------------------
// MOBILE VALIDATION
// ------------------------
function validateMobile() {
    let mobile = document.getElementById("mobile").value;
    let mobileError = document.getElementById("mobileError");

    mobile = mobile.replace(/[^0-9]/g,"");
    document.getElementById("mobile").value = mobile;

    if(mobile.length === 0) {
        mobileError.innerHTML = "";
        return true;
    }

    if(!/^[6-9]/.test(mobile[0])) {
        mobileError.innerHTML = "Mobile must start with 6,7,8,9.";
        return false;
    }

    if(mobile.length !== 10) {
        mobileError.innerHTML = "Mobile must be 10 digits.";
        return false;
    }

    mobileError.innerHTML = "";
    return true;
}

// ------------------------
// FINAL FORM VALIDATION
// ------------------------
function validateReceiver() {
    let nameValid = validateName();
    let mobileValid = validateMobile();
    let isValid = true;

    let status = document.getElementById("status").value;
    document.getElementById("statusError").innerHTML = "";
    if(status === "") {
        document.getElementById("statusError").innerHTML = "Please select status.";
        isValid = false;
    }

    return nameValid && mobileValid && isValid;
}

// ------------------------
// Auto-hide alerts after 2 seconds
// ------------------------
setTimeout(function() {
    document.querySelectorAll('.auto-hide-alert').forEach(el => el.style.display = 'none');
}, 2000);
</script>
