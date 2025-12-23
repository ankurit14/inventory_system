<?php
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

// Check ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid Asset ID.</div>";
    exit;
}

$id = intval($_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM company_assets WHERE id = $id");

if (mysqli_num_rows($res) == 0) {
    echo "<div class='alert alert-danger'>Asset not found.</div>";
    exit;
}

$asset = mysqli_fetch_assoc($res);
$billImgPath = BASE_URL . 'uploads/assets/' . $asset['bill_image'];
?>

<div class="container mt-3">

    <!-- BILL IMAGE -->
    <?php if ($asset['bill_image'] && file_exists(BASE_PATH . '/uploads/assets/' . $asset['bill_image'])): ?>
        <div class="text-center mb-4">
            <img src="<?= $billImgPath ?>" class="img-fluid rounded shadow" style="max-height:200px; cursor:pointer;" onclick="window.open('<?= $billImgPath ?>', '_blank')">
        </div>
    <?php endif; ?>

    <!-- ASSET DETAILS IN 4 COLUMNS -->
    <div class="row g-3">

        <div class="col-md-3">
            <strong>Asset Name:</strong>
            <p><?= htmlspecialchars($asset['asset_name']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Category:</strong>
            <p><?= htmlspecialchars($asset['category']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Brand:</strong>
            <p><?= htmlspecialchars($asset['brand']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Model:</strong>
            <p><?= htmlspecialchars($asset['model']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Floor:</strong>
            <p><?= htmlspecialchars($asset['floor']) ?: '—'; ?></p>
        </div>

        <div class="col-md-3">
            <strong>Serial No:</strong>
            <p><?= htmlspecialchars($asset['serial_no']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Quantity:</strong>
            <p><?= htmlspecialchars($asset['qty']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Purchase Date:</strong>
            <p><?= ($asset['purchase_date']) ? date("d-M-Y", strtotime($asset['purchase_date'])) : "—"; ?></p>
        </div>

        <div class="col-md-3">
            <strong>Purchase Price:</strong>
            <p><?= ($asset['purchase_price']) ? "₹" . number_format($asset['purchase_price'], 2) : "—"; ?></p>
        </div>

        <div class="col-md-3">
            <strong>Assigned To:</strong>
            <p><?= htmlspecialchars($asset['assigned_to']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Current Location:</strong>
            <p><?= htmlspecialchars($asset['current_location']); ?></p>
        </div>

        <div class="col-md-3">
            <strong>Status:</strong>
            <p>
                <span class="badge 
                    <?= $asset['status'] == 'Available' ? 'bg-success' : ($asset['status'] == 'In Use' ? 'bg-primary' : 'bg-warning'); ?>">
                    <?= htmlspecialchars($asset['status']); ?>
                </span>
            </p>
        </div>

        <div class="col-md-3">
            <strong>Created At:</strong>
            <p><?= date("d-M-Y h:i A", strtotime($asset['created_at'])); ?></p>
        </div>

        <div class="col-12">
            <strong>Note:</strong>
            <p><?= nl2br(htmlspecialchars($asset['note'])); ?></p>
        </div>

    </div>
</div>

<script>
function printBill() {
    var imgSrc = '<?= $billImgPath ?>';
    var w = window.open('', '_blank');

    w.document.write(`
        <html>
        <head>
            <title>Print Bill</title>
            <style>
                body { text-align:center; margin:0; padding:20px; }
                img { max-width:100%; height:auto; }
                @media print { body { margin:0; } }
            </style>
        </head>
        <body>
            <img src="${imgSrc}" id="billImg">
        </body>
        </html>
    `);

    w.document.close();

    w.onload = function() {
        var img = w.document.getElementById('billImg');
        if (img.complete) {
            w.focus();
            w.print();
        } else {
            img.onload = function() {
                w.focus();
                w.print();
            };
        }
    };
}
</script>
