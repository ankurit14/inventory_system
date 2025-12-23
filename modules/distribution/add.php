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

// Fetch products for employee
$products_res = mysqli_query($conn, "
    SELECT p.id, p.name, p.unit,
    (SUM(e.stock_in)-SUM(e.stock_out)) AS available
    FROM employee_stock_master e
    JOIN products p ON p.id = e.product_id
    WHERE e.employee_id = $employee_id
    GROUP BY e.product_id
    HAVING available > 0
");

// Fetch clients
$clients_res = mysqli_query($conn,"SELECT id,name FROM clients ORDER BY name");
?>

<style>
.header-box {
    background: linear-gradient(135deg,#4e73df,#1cc88a);
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
}
.header-box h2 { color:#fff; margin:0; }

.card-box {
    background:#fff;
    padding:20px;
    border-radius:8px;
    max-width:800px;
    margin:auto;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.remove-btn { padding:6px 12px; }
#totalQtyBar {
    margin-top:15px;
    padding:10px;
    background:#e9f7ef;
    border-left:5px solid #28a745;
    font-weight:600;
}

#addMoreBtn {
    margin-top:15px;
}
</style>

<div class="pcoded-content">
    <div class="header-box">
        <h2>Distribute Products to Clients</h2>
    </div>

    <div class="card-box">

        <form id="distributionForm" method="POST" action="save_distribution.php">

            <!-- Employee -->
            <div class="mb-3">
                <label><strong>Employee</strong></label>
                <input type="text" class="form-control" value="<?= $_SESSION['name']; ?>" readonly>
                <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
            </div>

            <!-- Product -->
            <div class="mb-3">
                <label><strong>Product *</strong></label>
                <select name="product_id" class="form-control" id="productSelect" required>
                    <option value="">Select Product</option>
                    <?php while($p = mysqli_fetch_assoc($products_res)): ?>
                        <option value="<?= $p['id'] ?>" data-available="<?= $p['available'] ?>">
                            <?= $p['name'] ?> (<?= $p['unit'] ?>) - Available: <?= $p['available'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <hr>

            <!-- Client distribution repeatable rows -->
            <div id="distributionItems">

                <div class="distribution-item row g-2 mb-2">
                    <div class="col-md-4">
                        <label><strong>Client *</strong></label>
                        <select name="client_id[]" class="form-control client-select" required>
                            <option value="">Select Client</option>
                            <?php mysqli_data_seek($clients_res,0); ?>
                            <?php while($c = mysqli_fetch_assoc($clients_res)): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label><strong>Quantity *</strong></label>
                        <input type="number" name="qty[]" class="form-control qty-input" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label><strong>Note</strong></label>
                        <input type="text" name="note[]" class="form-control">
                    </div>

                    <div class="col-md-1 remove-col"></div>
                </div>

            </div>

            <!-- Add More Button -->
            <button type="button" class="btn btn-success w-100" id="addMoreBtn">+ Add More Clients</button>

            <!-- Total Qty bar -->
            <div id="totalQtyBar">
                Total Quantity: <span id="totalQty">0</span> / <span id="availableQty">0</span>
            </div>

            <button class="btn btn-primary mt-3 w-100" id="submitBtn">Save Distribution</button>

        </form>

    </div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>

<!-- Select2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    let availableStock = 0;

    // APPLY SELECT2
    function applySelect2() {
        $('.client-select').each(function(){
            if ($(this).hasClass("select2-hidden-accessible")) {
                $(this).select2('destroy');
            }
            $(this).select2({
                width: "100%",
                placeholder: "Select Client"
            });
        });
    }

    applySelect2();

    // Update available stock
    function updateAvailableStock() {
        let selectedOption = $('#productSelect').find('option:selected');
        availableStock = parseFloat(selectedOption.attr('data-available') || 0);
        $('#availableQty').text(availableStock);
        updateTotalQty();
    }

    // Initialize on page load
    updateAvailableStock();

    // On product change
    $('#productSelect').on('change', function() {
        updateAvailableStock();
    });

    // ADD MORE ROW
    $('#addMoreBtn').on('click', function() {
        let container = $('#distributionItems');
        let firstItem = container.find('.distribution-item').first();

        // Destroy select2 before cloning
        firstItem.find('.client-select').select2('destroy');

        let clone = firstItem.clone(true);

        // Reset cloned row values
        clone.find('select').val('');
        clone.find('input').val('');

        // Add remove button
        clone.find('.remove-col').html('<button type="button" class="btn btn-danger btn-sm btn-remove">-</button>');

        container.append(clone);
        applySelect2();
        updateTotalQty();
    });

    // REMOVE ROW BUTTON
    $(document).on('click', '.btn-remove', function() {
        $(this).closest('.distribution-item').remove();
        applySelect2();
        updateTotalQty();
    });

    // PREVENT DUPLICATE CLIENTS
    function preventDuplicateClients(){
        let selectedClients = [];
        $('.client-select').each(function(){
            let val = $(this).val();
            if(val) selectedClients.push(val);
        });

        $('.client-select option').prop('disabled', false);

        $('.client-select').each(function(){
            let current = $(this).val();
            selectedClients.forEach(function(val){
                if(val && val !== current){
                    $(this).find('option[value="'+val+'"]').prop('disabled', true);
                }
            }.bind(this));
        });
    }

    // UPDATE TOTAL QTY
    function updateTotalQty(){
    let total = 0;
    $('.qty-input').each(function(){
        let val = parseFloat($(this).val()) || 0;
        total += val;
    });
    $('#totalQty').text(total);
    preventDuplicateClients();

    // Disable submit if total exceeds available stock
    if(total > availableStock){
        $('#totalQtyBar').css('color','red');
        $('#submitBtn').prop('disabled',true);
    } else {
        $('#totalQtyBar').css('color','black');
        $('#submitBtn').prop('disabled',false);
    }

    // Disable "Add More Clients" if only one row exists or total >= available stock
    let rowCount = $('.distribution-item').length;
    // if(rowCount === 1 || total == availableStock){
    if(total == availableStock){
        $('#addMoreBtn').prop('disabled', true);
    } else {
        $('#addMoreBtn').prop('disabled', false);
    }
}


    // Update total on qty change or client change
    $(document).on('input','.qty-input', updateTotalQty);
    $(document).on('change','.client-select', updateTotalQty);

});
</script>
