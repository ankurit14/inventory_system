<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once BASE_PATH . '/config/db.php';
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';

$error = "";
$success = "";

/* ------------------------------------
   FORM SUBMISSION
-------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name    = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status  = $_POST['status'] ?? 'Active';

    /* ---------- BASIC VALIDATION ---------- */
    if ($name === "") {
        $error = "Client name is required.";

    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";

    } elseif ($phone !== "" && !preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be 10 digits.";
    }

    /* ---------- DUPLICATE CHECK ---------- */
    if ($error === "") {
        // 1. Email or Phone uniqueness check
        $conditions = [];
        $params = [];
        $types = "";

        if ($email !== "") {
            $conditions[] = "email = ?";
            $params[] = $email;
            $types .= "s";
        }

        if ($phone !== "") {
            $conditions[] = "phone = ?";
            $params[] = $phone;
            $types .= "s";
        }

        if (!empty($conditions)) {
            $sqlCheck = "SELECT id FROM clients WHERE " . implode(" OR ", $conditions) . " LIMIT 1";
            $stmt = mysqli_prepare($conn, $sqlCheck);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Client with same email or phone already exists!";
            }
            mysqli_stmt_close($stmt);
        }
    }

    /* ---------- NAME ONLY DUPLICATE CHECK ---------- */
    if ($error === "" && $email === "" && $phone === "") {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM clients WHERE LOWER(name) = LOWER(?) LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Client with this name already exists!";
        }
        mysqli_stmt_close($stmt);
    }

    /* ---------- INSERT CLIENT ---------- */
    if ($error === "") {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO clients (name, company, email, phone, address, status) VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "ssssss",
            $name,
            $company,
            $email,
            $phone,
            $address,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = "Client added successfully!";
            $_POST = []; // clear form
        } else {
            $error = "Database error. Please try again.";
        }
        mysqli_stmt_close($stmt);
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
.form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 30px 15px;
}
.card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
</style>

<div class="pcoded-content">

    <div class="header-box">
        <h2>Add New Client</h2>
    </div>

    <div class="form-container">

        <div class="card">
            <div class="card-body p-4">

                <?php if ($error): ?>
    <div class="alert alert-danger fade show auto-hide-alert">
        <strong>Error!</strong> <?= $error ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success fade show auto-hide-alert">
        <strong>Success!</strong> <?= $success ?>
    </div>
<?php endif; ?>

                <!-- FORM START -->
                <form method="POST" id="addClientForm">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Name *</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Enter client name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business / Company</label>
                            <input type="text" name="company" id="company" class="form-control" placeholder="Company, Shop, Hospital etc">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control" maxlength="10" placeholder="10-digit mobile number">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="2" placeholder="Enter complete address"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <button class="btn btn-primary w-100 py-2">Save Client</button>

                </form>
                <!-- FORM END -->

            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    document.getElementById("name").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9 ]/g, "");
    });

    document.getElementById("company").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9 &.,-]/g, "");
    });

    document.getElementById("phone").addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "").substring(0, 10);
    });

    document.getElementById("email").addEventListener("input", function () {
        this.value = this.value.trim();
    });
      setTimeout(function () {
        $('.auto-hide-alert').fadeOut('slow');
    }, 2000); // 2000 ms = 2 seconds

});


</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
