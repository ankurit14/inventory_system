<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/path.php');
// include(BASE_PATH . '/includes/db.php');
include(BASE_PATH . '/includes/users_functions.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

// Logged-in user ID
$user_id = $_SESSION['user_id'];

// Fetch user details
$user = get_user_by_id($user_id);

$error = "";
$success = "";

/* -------------------------------------------
    UPDATE PROFILE FORM SUBMIT
-------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['name'])) {
        $error = "Full name is required.";

    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && !empty($_POST['email'])) {
        $error = "Invalid email format.";

    } elseif (!empty($_POST['contact_no']) && !preg_match("/^[0-9]{10}$/", $_POST['contact_no'])) {
        $error = "Contact number must be 10 digits.";

    } else {

        // Prepare update data
        $updateData = [
            'name'        => $_POST['name'],
            'email'       => $_POST['email'],
            'contact_no'  => $_POST['contact_no'],
            'address'     => $_POST['address'],
            'department'  => $_POST['department'],
            'designation' => $_POST['designation']
        ];

        if (update_user_profile($user_id, $updateData)) {
            $success = "Profile updated successfully!";
            $user = get_user_by_id($user_id); // reload updated data
        } else {
            $error = "Something went wrong. Try again.";
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
        <h2>My Profile</h2>
        <p class="text-muted">Update your personal information</p>
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
                <form method="POST">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="<?= htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username (cannot change)</label>
                            <input type="text" class="form-control" value="<?= $user['username']; ?>" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" id="contact_no" name="contact_no" maxlength="10"
                                   class="form-control" value="<?= htmlspecialchars($user['contact_no']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" id="department" name="department" class="form-control"
                                   value="<?= htmlspecialchars($user['department']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" id="designation" name="designation" class="form-control"
                                   value="<?= htmlspecialchars($user['designation']); ?>">
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 py-2">Update Profile</button>

                </form>
                <!-- FORM END -->

            </div>
        </div>

    </div>

</div>


<!-- JS VALIDATION -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    document.getElementById("name").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z ]/g, "");
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

});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
