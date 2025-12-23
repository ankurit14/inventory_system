<?php
session_start();
$emp_id = $_SESSION['user_id'];

include('distribution_functions.php');
include('../clients/client_functions.php');

$products = get_employee_products($emp_id);
$clients = get_all_clients();
?>

<h3>Distribute Products</h3>

<form method="POST" action="save_distribution.php">
    <input type="hidden" name="emp_id" value="<?= $emp_id ?>">

    <div class="mb-3">
        <label>Product</label>
        <select name="product_id" class="form-control" required>
            <option value="">Select Product</option>
            <?php foreach($products as $p): ?>
                <option value="<?= $p['product_id'] ?>">
                    <?= get_product_name($p['product_id']) ?> 
                    (Remaining: <?= $p['remaining'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Client</label>
        <select name="client_id" class="form-control" required>
            <option value="">Select Client</option>
            <?php foreach($clients as $c): ?>
                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Quantity</label>
        <input type="number" name="qty" min="1" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Note (optional)</label>
        <textarea name="note" class="form-control"></textarea>
    </div>

    <button class="btn btn-primary">Submit</button>
</form>
<?php include(BASE_PATH . '/includes/footer.php'); ?>