<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

// simple auth: ensure logged in
if (!isset($_SESSION['user_id'])) {
    // header("Location: /inventory_system/login.php");
    header("Location: " . BASE_URL . "login.php");
exit;

    exit;
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
        font-size: 24px;
        font-weight: 600;
        text-align: center;
    }

    .card-custom {
        background: #ffffff;
        border-radius: 8px;
        padding: 25px;
        max-width: 650px;
        margin: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    label {
        font-weight: 600;
        margin-bottom: 5px;
    }
</style>


<div class="pcoded-content">

    <div class="header-box">
        <h2>Create New Product Request</h2>
    </div>

    <div class="card-custom">

        <form method="post" action="save.php">

            <!-- Requested By -->
            <div class="mb-3">
                <label>Requested By</label>
                <input type="text" class="form-control" 
                    value="<?= htmlentities($_SESSION['name']) ?>" readonly>
            </div>

            <!-- Product Name -->
            <div class="mb-3">
                <label>Product Name (Not in Product List) *</label>
                <input type="text" name="product_name" class="form-control" 
                       placeholder="e.g. USB Type C to HDMI Converter" required>
            </div>

            <!-- Quantity -->
            <div class="mb-3">
                <label>Approx Quantity *</label>
                <input type="number" name="qty" class="form-control" 
                       min="1" value="1" required>
            </div>

            <!-- Reason -->
            <div class="mb-3">
                <label>Reason / Remarks *</label>
                <textarea name="reason" class="form-control" rows="3"
                    placeholder="Why do you need this item?" required></textarea>
            </div>

            <button type="submit" class="btn btn-success w-100">
                Submit Request
            </button>

        </form>
    </div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
