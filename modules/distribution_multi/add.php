<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

$employee_id = $_SESSION['user_id'];
$role        = $_SESSION['role'];

if(!in_array($role,['employee','hr','admin'])){
    echo "<script>alert('Access Denied');window.location='".BASE_URL."index.php';</script>";
    exit;
}

/* ================= PRODUCTS WITH AVAILABLE STOCK ================= */
$products_res = mysqli_query($conn, "
    SELECT p.id, p.name, p.unit,
           (SUM(e.stock_in) - SUM(e.stock_out)) AS available
    FROM employee_stock_master e
    JOIN products p ON p.id = e.product_id
    WHERE e.employee_id = $employee_id
    GROUP BY e.product_id
    HAVING available > 0
");

$products = [];
while($p = mysqli_fetch_assoc($products_res)){
    $products[] = $p;
}

/* ================= CLIENTS ================= */
$clients_res = mysqli_query($conn,"SELECT id,name FROM clients ORDER BY name");
?>

<style>
.header-box{
    background:linear-gradient(135deg,#4e73df,#1cc88a);
    padding:15px;
    border-radius:8px;
    text-align:center;
    margin-bottom:20px;
}
.header-box h2{color:#fff;margin:0}

.card-box{
    background:#fff;
    padding:20px;
    border-radius:8px;
    max-width:1100px;
    margin:auto;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}

#totalQtyBar{
    margin-top:15px;
    padding:10px;
    background:#e9f7ef;
    border-left:5px solid #28a745;
    font-weight:600;
}

.remove-btn{padding:6px 10px}
</style>

<div class="pcoded-content">

    <div class="header-box">
        <h2>Distribute Products to Clients</h2>
    </div>

    <div class="card-box">

        <form method="POST" action="save_distribution.php" id="distributionForm">

            <!-- EMPLOYEE -->
            <div class="mb-3">
                <label><strong>Employee</strong></label>
                <input type="text" class="form-control" value="<?= $_SESSION['name']; ?>" readonly>
                <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
            </div>

            <hr>

            <!-- DISTRIBUTION ITEMS -->
            <div id="distributionItems">

                <div class="distribution-item row g-2 mb-3">

                    <!-- PRODUCT -->
                    <div class="col-md-3">
                        <label><strong>Product *</strong></label>
                        <select name="product_id[]" class="form-control product-select" required>
                            <option value="">Select Product</option>
                            <?php foreach($products as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                        data-available="<?= $p['available'] ?>">
                                    <?= $p['name'] ?> (<?= $p['unit'] ?>) - <?= $p['available'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- CLIENT -->
                    <div class="col-md-3">
                        <label><strong>Client *</strong></label>
                        <select name="client_id[]" class="form-control client-select" required>
                            <option value="">Select Client</option>
                            <?php while($c = mysqli_fetch_assoc($clients_res)): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- QTY -->
                    <div class="col-md-2">
                        <label><strong>Qty *</strong></label>
                        <input type="number" name="qty[]" class="form-control qty-input" min="1" required>
                    </div>

                    <!-- NOTE -->
                    <div class="col-md-3">
                        <label><strong>Note</strong></label>
                        <input type="text" name="note[]" class="form-control">
                    </div>

                    <div class="col-md-1 remove-col"></div>
                </div>

            </div>

            <button type="button" id="addMoreBtn" class="btn btn-success w-100">
                + Add More
            </button>

            <!-- TOTAL QTY BAR -->
            <div id="totalQtyBar">
                <div id="totalQtyInfo">Total Quantity: 0</div>
            </div>

            <button type="submit" class="btn btn-primary mt-3 w-100" id="submitBtn">
                Save Distribution
            </button>

        </form>

    </div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<!-- SELECT2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function(){

    function applySelect2(){
        $('.product-select,.client-select').each(function(){
            if($(this).hasClass("select2-hidden-accessible")){
                $(this).select2('destroy');
            }
            $(this).select2({ width:'100%' });
        });
    }
    applySelect2();

    /* ================= ADD ROW ================= */
    $('#addMoreBtn').click(function(){
        let first = $('.distribution-item').first();
        first.find('select').select2('destroy');

        let clone = first.clone();
        clone.find('input').val('');
        clone.find('select').val('');
        clone.find('.remove-col').html(
            '<button type="button" class="btn btn-danger btn-sm remove-btn">-</button>'
        );

        $('#distributionItems').append(clone);
        applySelect2();
        validateStock();
    });

    /* ================= REMOVE ROW ================= */
    $(document).on('click','.remove-btn',function(){
        $(this).closest('.distribution-item').remove();
        validateStock();
    });

    /* ================= FIXED TOTAL QTY LOGIC ================= */
    function validateStock(){

        let productTotals = {};
        let productInfo   = {};
        let valid = true;
        let html = '';

        $('.distribution-item').each(function(){
            let select = $(this).find('.product-select');
            let pid    = select.val();
            let qty    = parseFloat($(this).find('.qty-input').val()) || 0;

            if(pid){
                productTotals[pid] = (productTotals[pid] || 0) + qty;

                if(!productInfo[pid]){
                    productInfo[pid] = {
                        text: select.find('option:selected').text(),
                        available: parseFloat(select.find('option:selected').data('available')) || 0
                    };
                }
            }
        });

        $.each(productTotals, function(pid, used){
            let available = productInfo[pid].available;

            html += `
                <div>
                    ${productInfo[pid].text} :
                    <strong>${used}</strong> / ${available}
                </div>
            `;

            if(used > available){
                valid = false;
            }
        });

        $('#totalQtyInfo').html(html || 'Total Quantity: 0');

        $('#totalQtyBar').css({
            borderLeft: valid ? '5px solid #28a745' : '5px solid red',
            color: valid ? '#000' : 'red'
        });

        $('#submitBtn').prop('disabled', !valid);
    }

    $(document).on('change','.product-select',validateStock);
    $(document).on('input','.qty-input',validateStock);

});
</script>
