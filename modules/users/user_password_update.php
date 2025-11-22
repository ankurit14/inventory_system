<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');
include(BASE_PATH."/includes/header.php");
include(BASE_PATH."/includes/sidebar.php");

$user_id = $_SESSION['user_id'];

$success = "";
$error = "";

// GET CURRENT PASSWORD
$res = mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id");
$row = mysqli_fetch_assoc($res);
$db_password = $row['password'];

// UPDATE PASSWORD
if (isset($_POST['update_password'])) {

    $old_pass     = $_POST['old_password'];
    $new_pass     = $_POST['new_password'];
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

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .page-title-box {
        margin-bottom: 10px;
        padding-left: 5px;
    }
    .page-title-box h5 {
        font-weight: 600;
        margin-bottom: 3px;
    }
    .page-title-box p {
        margin: 0;
        color: #6c757d;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0px 4px 14px rgba(0,0,0,0.08);
        top: -69px;
    }

    .input-group-text {
        cursor: pointer;
    }
    .page-header-wrapper {
    position: relative;
    width: 100%;
    height: 180px; /* adjust height */
    margin-bottom: 30px;
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

.page-header-content {
    position: relative;
    z-index: 2;
    color: #fff;
    padding-top: 20px; /* vertical centering */
}

</style>


<div class="pcoded-content">

    <!-- PAGE HEADING -->
    <div class="page-header-wrapper">
    <div class="page-header-bg"></div>
    <div class="page-header-content text-center">
        <h2>Change Password</h2>
        <p class="text-dark">Manage your account security</p>
    </div>
</div>

        
    

    <!-- PROFILE ICON -->
   <!-- <div class="profile-icon-wrapper">
    <img src="https://cdn-icons-png.flaticon.com/512/1144/1144760.png" 
         class="profile-icon" 
         alt="User Icon">
</div> -->




    <!-- CARD -->
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card">
                <div class="card-body">

                    <!-- SUCCESS POPUP -->
                    <?php if ($success): ?>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Password updated successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        </script>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="post">

                        <!-- Old Password -->
                        <label>Old Password *</label>
                        <div class="input-group mb-3">
                            <input type="password" name="old_password" id="old_password" class="form-control" required>
                            <span class="input-group-text" onclick="togglePassword('old_password', this)">👁</span>
                        </div>

                        <!-- New Password -->
                        <label>New Password *</label>
                        <div class="input-group mb-1">
                            <input type="password" name="new_password" id="new_password" class="form-control" required onkeyup="checkStrength()">
                            <span class="input-group-text" onclick="togglePassword('new_password', this)">👁</span>
                        </div>

                        <!-- Strength Meter -->
                        <div class="mb-3">
                            <small>Password Strength:</small>
                            <div id="strengthBar" style="height:8px; background:#e9ecef; border-radius:5px;">
                                <div id="strength" style="height:8px; width:0%; background:red; border-radius:5px;"></div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <label>Confirm New Password *</label>
                        <div class="input-group mb-4">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <span class="input-group-text" onclick="togglePassword('confirm_password', this)">👁</span>
                        </div>

                        <button class="btn btn-primary px-3" name="update_password">Update Password</button>
                        <a href="user_profile.php" class="btn btn-secondary px-3">Back</a>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>


<!-- JS: Show/Hide Password -->
<script>
function togglePassword(fieldID, element) {
    let input = document.getElementById(fieldID);
    if (input.type === "password") {
        input.type = "text";
        element.innerHTML = "🙈";
    } else {
        input.type = "password";
        element.innerHTML = "👁";
    }
}
</script>

<!-- JS: Strength Meter -->
<script>
function checkStrength() {
    let password = document.getElementById("new_password").value;
    let strengthBar = document.getElementById("strength");
    let strength = 0;

    if (password.length > 6) strength += 20;
    if (password.match(/[a-z]/)) strength += 20;
    if (password.match(/[A-Z]/)) strength += 20;
    if (password.match(/[0-9]/)) strength += 20;
    if (password.match(/[$@#&!]/)) strength += 20;

    strengthBar.style.width = strength + "%";
    strengthBar.style.background =
        strength < 40 ? "red" :
        strength < 60 ? "orange" :
        strength < 80 ? "blue" : "green";
}
</script>

<?php include(BASE_PATH."/includes/footer.php"); ?>
