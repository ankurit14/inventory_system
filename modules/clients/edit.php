<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once BASE_PATH . '/config/db.php';
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';

$error = "";
$success = "";

/* ------------------------------------
   GET CLIENT ID
-------------------------------------- */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<div class='alert alert-danger'>Invalid client ID.</div>";
    exit;
}

/* ------------------------------------
   FETCH CLIENT
-------------------------------------- */
$stmt = mysqli_prepare($conn, "SELECT * FROM clients WHERE id=? AND is_deleted=0");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$client = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$client) {
    echo "<div class='alert alert-danger'>Client not found.</div>";
    exit;
}

/* ------------------------------------
   FORM SUBMISSION + VALIDATION
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

    /* ---------- DUPLICATE EMAIL / PHONE CHECK ---------- */
    if ($error === "") {

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

            $sql = "SELECT id FROM clients 
                    WHERE id != ? AND (" . implode(" OR ", $conditions) . ")
                    LIMIT 1";

            $stmt = mysqli_prepare($conn, $sql);

            $types = "i" . $types;
            $params = array_merge([$id], $params);

            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Client with same email or phone already exists!";
            }

            mysqli_stmt_close($stmt);
        }
    }

    /* ---------- NAME ONLY DUPLICATE ---------- */
    if ($error === "" && $email === "" && $phone === "") {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM clients 
             WHERE id != ? AND LOWER(name) = LOWER(?) 
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "is", $id, $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Client with this name already exists!";
        }

        mysqli_stmt_close($stmt);
    }

    /* ---------- UPDATE CLIENT ---------- */
    if ($error === "") {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE clients 
             SET name=?, company=?, email=?, phone=?, address=?, status=? 
             WHERE id=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssi",
            $name,
            $company,
            $email,
            $phone,
            $address,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {

            $success = "Client updated successfully!";

            // Refresh client data
            $client = [
                'name'    => $name,
                'company' => $company,
                'email'   => $email,
                'phone'   => $phone,
                'address' => $address,
                'status'  => $status
            ];

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
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    text-align: center;
    margin: 0;
}
.form-container {
    max-width: 800px;
    margin: auto;
}
.card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
</style>

<div class="pcoded-content">

    <div class="header-box">
        <h2>Edit Client</h2>
    </div>

    <div class="form-container">
        <div class="card">
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger auto-hide-alert">
                        <strong>Error!</strong> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success auto-hide-alert">
                        <strong>Success!</strong> <?= $success ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Client Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= htmlspecialchars($client['name']) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Business / Company</label>
                            <input type="text" name="company" class="form-control"
                                   value="<?= htmlspecialchars($client['company']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($client['email']) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" maxlength="10" class="form-control"
                                   value="<?= htmlspecialchars($client['phone']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?= htmlspecialchars($client['address']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" <?= $client['status']=="Active"?"selected":"" ?>>Active</option>
                            <option value="Inactive" <?= $client['status']=="Inactive"?"selected":"" ?>>Inactive</option>
                        </select>
                    </div>

                    <button class="btn btn-primary w-100">Update Client</button>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
setTimeout(function () {
    document.querySelectorAll('.auto-hide-alert').forEach(el => el.style.display = 'none');
}, 2000);
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
