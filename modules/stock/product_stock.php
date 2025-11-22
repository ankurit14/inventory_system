<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/config/db.php');

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

include(BASE_PATH.'/modules/stock/get_stock.php');

// EXPORT LOW STOCK
if (isset($_POST['export_low_stock'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=low_stock_report.xls");

    echo "Product\tStock\tPrice\n";
    $result = mysqli_query($conn, "SELECT * FROM products ORDER BY name ASC");

    while ($row = mysqli_fetch_assoc($result)) {
        $data = get_current_stock($row['id']);
        if ($data['stock'] <= 10) {
            echo $row['name']."\t".$data['stock']."\t".$data['price']."\n";
        }
    }
    exit;
}
?>
<style>
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.header-box h5 {
    color: #fff;
    margin: 0;
    font-size: 20px;
    font-weight: 200;
}
.header-box a.btn {
    color: #1f2937;
    background-color: #fff;
    padding: 6px 15px;
    border-radius: 6px;
    text-decoration: none;
}

.filter-container input, .filter-container select {
    padding: 6px 10px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.table thead th {
    background: #2d6cdf;
    color: #fff;
    font-size: 14px;
}
.table tbody td {
    font-size: 14px;
    padding: 6px 10px;
}
.table tbody tr:hover {
    background: #f1f5ff;
}

.btn-sm {
    padding: 3px 7px;
    font-size: 13px;
}
.status-btn {
    min-width: 80px;
}
.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    padding: 4px 6px !important;
    height: 30px !important;
    line-height: 14px;
}

</style>
<div class="pcoded-content">

<div class="header-box">
        <h2>Product List with Stock</h2>
        <label>
        <h5><input type="checkbox" id="lowStockOnly"> Show only low stock (≤10)</h5>
    </label>
    </div>




<style>
.stock-low { background: #ffe5e5; color:#b30000; font-weight:bold; }
.stock-medium { background: #fff3e0; color:#ff6f00; font-weight:bold; }
.stock-high { background: #e8f5e9; color:#2e7d32; font-weight:bold; }

table { border-collapse: collapse; }
table th, table td { padding: 8px; border: 1px solid #ccc; text-align: center; }
</style>

<table border="1" width="100%" cellpadding="8" id="productTable">
    <tr style="background:#eee;">
        <th>#</th>
        <th>Product</th>
        <th>Price</th>
        <th>Current Stock</th>
    </tr>

<?php
$res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$i = 1;

while ($row = mysqli_fetch_assoc($res)) {

    $data = get_current_stock($row['id']);
    $stock = $data['stock'];
    $price = number_format($data['price'], 2);

    // COLOR CLASS
    if ($stock <= 0)
        $stock_class = "stock-low";
    elseif ($stock <= 10)
        $stock_class = "stock-medium";
    else
        $stock_class = "stock-high";

    // ALERT ICON
    $alert_icon = ($stock <= 10) ? " 🔔" : "";
?>
<tr>
    <td><?= $i++ ?></td>
    <td>
        <a href="stock_ledger.php?product_id=<?= $row['id'] ?>" style="text-decoration:none;">
            <?= htmlspecialchars($row['name']) ?>
        </a>
    </td>
    <td><?= $price ?></td>
    <td class="<?= $stock_class ?>"><?= $stock . $alert_icon ?></td>
</tr>
<?php } ?>
</table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- FILTER SCRIPT -->
<script>
$("#lowStockOnly").change(function () {
    if (this.checked) {
        $("#productTable tr").each(function (index) {
            if (index === 0) return; // skip header
            let stock = parseFloat($(this).find("td").eq(3).text());
            if (stock > 10) $(this).hide();
        });
    } else {
        $("#productTable tr").show();
    }
});
</script>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
