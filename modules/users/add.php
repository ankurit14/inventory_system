<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/path.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/users_functions.php');
include(BASE_PATH . '/includes/sidebar.php');

$error = "";
$success = "";

/* ------------------------------------
   FORM SUBMISSION + VALIDATION
-------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['name']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['role'])) {
        $error = "Please fill in all required fields.";

    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && !empty($_POST['email'])) {
        $error = "Invalid email format.";

    } elseif (!empty($_POST['contact_no']) && !preg_match("/^[0-9]{10}$/", $_POST['contact_no'])) {
        $error = "Contact number must be exactly 10 digits.";

    } elseif (username_exists($_POST['username'])) {
        $error = "This username is already taken.";

    } else {

        // No image handling at all
        $_POST['profile_image'] = "";

        if (add_user($_POST, [])) {
            $success = "User added successfully!";
        } else {
            $error = "Database error. Please try again.";
        }
    }
}
?>

<style>
    .page-header h2 {
        font-weight: 600;
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 30px 15px;
    }
</style>

<div class="pcoded-content">

    <div class="page-header text-center mt-4 mb-4">
        <h2>Add New User</h2>
        <p class="text-muted">Create a new user account and assign role, department and login credentials.</p>
    </div>

    <div class="form-container">

        <div class="card">
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error!</strong> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <strong>Success!</strong> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- FORM START -->
                <form method="POST" id="addUserForm">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
                        </div>
                    </div>

                   

                    <div class="row">
                         <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address">
                    </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" id="contact_no" name="contact_no" class="form-control" maxlength="10" placeholder="10-digit mobile number">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Enter complete address"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" id="department" name="department" class="form-control" placeholder="Department name">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" id="designation" name="designation" class="form-control" placeholder="Employee designation">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select" required>
                            <option value="">Select user role</option>
                            <option value="admin">Admin</option>
                            <option value="hr">HR</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Create password" required>
                        <div id="passError" style="color:red; font-size:13px; display:none;">Password must be at least 6 characters</div>
                    </div>

                    <button class="btn btn-primary w-100 py-2">Save User</button>

                </form>
                <!-- FORM END -->

            </div>
        </div>

    </div>

</div>

<!-- ------------------ JS VALIDATION (No Image Code) ------------------ -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    document.getElementById("name").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z ]/g, "");
    });

    document.getElementById("username").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9_]/g, "");
    });

    document.getElementById("contact_no").addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "").substring(0, 10);
    });

    document.getElementById("department").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z ]/g, "");
    });

    document.getElementById("designation").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z ]/g, "");
    });

    document.getElementById("password").addEventListener("input", function () {
        document.getElementById("passError").style.display = 
            this.value.length < 6 ? "block" : "none";
    });

});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
