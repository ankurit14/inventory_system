<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');
include(BASE_PATH."/includes/header.php");
include(BASE_PATH."/includes/sidebar.php");

$user_id = $_SESSION['user_id'];

$success = "";
$error = "";

// CURRENT PASSWORD
$res = mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id");
$row = mysqli_fetch_assoc($res);
$db_password = $row['password'];

// UPDATE PASSWORD
if (isset($_POST['update_password'])) {

    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (!password_verify($old_pass, $db_password)) {
        $error = "Old password is incorrect.";

    } elseif ($new_pass != $confirm_pass) {
        $error = "New passwords do not match.";

    } elseif (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";

    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);

        mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id=$user_id");
        $success = "Password updated successfully!";
    }
}
?>

<div class="pcoded-content">

    <!-- Page Header -->
    <div class="page-header">

    <div class="page-header text-center mt-4 mb-4">
        <h2>Change Password</h2>
        <p class="text-muted">Update your login password securely</p>
    </div>


        <!-- <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h5 class="m-b-10">Change Password</h5>
                    <p class="text-muted">Update your login password securely</p>
                </div>
            </div>
        </div> -->
    </div>

    <!-- Password Change Card -->
    <div class="row">
        <div class="col-md-6 offset-md-3">

            <div class="card">
                <!-- <div class="card-header">
                    <h5>Update Password</h5>
                </div> -->

                <div class="card-body">

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="post">

                        <div class="form-group mb-3">
                            <label>Old Password *</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>New Password *</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary px-4" name="update_password">Update Password</button>
                        <a href="user_profile.php" class="btn btn-secondary">Back</a>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<?php include(BASE_PATH."/includes/footer.php"); ?>
