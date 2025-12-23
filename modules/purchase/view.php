<?php
session_start();
include_once __DIR__ . '/../../config/path.php';

include('purchase_functions.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>Purchase ID is missing!</div>";
    exit;
}

$purchase_id = intval($_GET['id']);

// Get purchase info
$purchase = get_purchase_by_id($purchase_id);
if (!$purchase) {
    echo "<div class='alert alert-danger'>Purchase not found!</div>";
    exit;
}

// Get supplier info
$supplier = get_supplier_by_id($purchase['supplier_id']);

// Get purchase items
$items = get_purchase_items($purchase_id);
?>

<style>
.invoice-box {
    max-width: 900px;
    margin: auto;
    padding: 30px;
    border: 1px solid #eee;
    font-size: 14px;
    line-height: 20px;
    font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    color: #555;
    background: #fff;
}
.invoice-box table {
    width: 100%;
    line-height: inherit;
    text-align: left;
    border-collapse: collapse;
}
.invoice-box table td {
    padding: 5px;
    vertical-align: top;
}
.invoice-box table tr.heading th {
    background: #2d6cdf;
    color: white;
    border-bottom: 1px solid #ddd;
    font-weight: bold;
}
.invoice-box table tr.item td {
    border-bottom: 1px solid #eee;
}
.invoice-box table tr.total td:nth-child(5) {
    border-top: 2px solid #2d6cdf;
    font-weight: bold;
}
.no-print {
    margin-top: 20px;
}

/* PRINT ONLY INVOICE */
@media print {
    body * {
        visibility: hidden; /* hide everything */
    }

    .invoice-box, .invoice-box * {
        visibility: visible; /* show only invoice */
    }

    .invoice-box {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    /* Hide fixed sidebar/header/footer */
    #sidebar, .pcoded-header, .pcoded-footer, .no-print {
        display: none !important;
    }
}

</style>

<div class="pcoded-content">
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="5">
                    <h2>Purchase Invoice</h2>
                    <p>
                        <strong>Purchase ID:</strong> <?= $purchase['id'] ?><br>
                        <strong>Date:</strong> <?= htmlspecialchars($purchase['purchase_date']) ?><br>
                        <strong>Status:</strong> <?= ucfirst($purchase['status']) ?><br>
                        <strong>Supplier:</strong> <?= htmlspecialchars($supplier['name']) ?><br>
                        <?php if(!empty($supplier['phone'])): ?>
                        <strong>Phone:</strong> <?= htmlspecialchars($supplier['phone']) ?><br>
                        <?php endif; ?>
                        <?php if(!empty($supplier['email'])): ?>
                        <strong>Email:</strong> <?= htmlspecialchars($supplier['email']) ?><br>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <th>Product</th>
                <th>Unit</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>

            <?php $grand_total = 0; ?>
            <?php foreach ($items as $it): ?>
                <tr class="item">
                    <td><?= htmlspecialchars($it['product_name']) ?></td>
                    <td><?= htmlspecialchars($it['unit']) ?></td>
                    <td><?= $it['qty'] ?></td>
                    <td><?= number_format($it['unit_price'], 2) ?></td>
                    <td><?= number_format($it['total'], 2) ?></td>
                </tr>
                <?php $grand_total += floatval($it['total']); ?>
            <?php endforeach; ?>

            <tr class="total">
                <td colspan="4" style="text-align:right;">Grand Total:</td>
                <td><?= number_format($grand_total, 2) ?></td>
            </tr>
        </table>

        <div class="no-print">
            <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
           <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Back</button>

        </div>
    </div>
</div>

<!--  -->
