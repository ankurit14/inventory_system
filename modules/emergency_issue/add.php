<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH.'/config/db.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

// Only HR or Admin
if ($_SESSION['role'] != 'hr' && $_SESSION['role'] != 'admin') {
    echo "<script>alert('Access Denied');window.location='" . BASE_URL . "index.php';</script>";
    exit;
}

/* ---------------------------------------------------------------------------
   SAVE EMERGENCY ISSUE
--------------------------------------------------------------------------- */
if (isset($_POST['submit_issue'])) {

    $issued_by      = $_SESSION['user_id'];
    $issued_to_type = $_POST['issued_to_type'];

    if ($issued_to_type == "employee") {
        $issued_to_id   = intval($_POST['employee_id']);
        $issued_to_name = "NULL";
    } else {
        // Other receiver selected
        $issued_to_id   = intval($_POST['other_receiver_id']);
        $issued_to_name = "NULL"; // Name already inside DB
    }
    $request_for = mysqli_real_escape_string($conn, $_POST['request_for']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $valid_items = []; // Only valid stock items

    // Validate items & stock
    foreach ($_POST['product_id'] as $i => $pid) {
        if ($pid == "" || $_POST['qty'][$i] == "") continue;

        $category_id = intval($_POST['category_id'][$i]);
        $subcategory_id = intval($_POST['sub_category_id'][$i]);
        $product_id = intval($pid);
        $qty = floatval($_POST['qty'][$i]);

        // Fetch unit
        $p = mysqli_query($conn, "SELECT unit FROM products WHERE id=$product_id");
        $row = mysqli_fetch_assoc($p);
        $unit = $row['unit'] ?? '';

        // Stock check
        $st = mysqli_query($conn, "
            SELECT SUM(stock_in) AS tin, SUM(stock_out) AS tout
            FROM stock_master
            WHERE product_id = $product_id
        ");
        $s = mysqli_fetch_assoc($st);

        $available_stock = floatval($s['tin']) - floatval($s['tout']);

        if ($qty > $available_stock) {
            echo "<script>alert('Not enough stock for Product ID: $product_id');</script>";
            continue;
        }

        // Valid item
        $valid_items[] = [
            'category_id' => $category_id,
            'sub_category_id' => $subcategory_id,
            'product_id' => $product_id,
            'unit' => $unit,
            'qty' => $qty
        ];
    }

    // Stop if no items can be saved
    if (count($valid_items) == 0) {
        echo "<script>alert('No valid items to save. Issue not created.'); window.history.back();</script>";
        exit;
    }

    // Insert master issue
    $sql = "INSERT INTO emergency_issues 
        (issued_by, issued_to_type, issued_to_id, issued_to_name, request_for, remarks)
        VALUES 
        ($issued_by, '$issued_to_type', $issued_to_id, $issued_to_name, '$request_for', '$remarks')";
        mysqli_query($conn, $sql);
        $issue_id = mysqli_insert_id($conn);

        // Insert items & stock update
        foreach ($valid_items as $item) {
        mysqli_query($conn, "
            INSERT INTO emergency_issue_items 
            (issue_id, category_id, sub_category_id, product_id, unit, qty_issued)
            VALUES 
            ($issue_id, {$item['category_id']}, {$item['sub_category_id']}, {$item['product_id']}, '{$item['unit']}', {$item['qty']})
        ");

        mysqli_query($conn, "
            INSERT INTO stock_master 
            (product_id, stock_in, stock_out, source, ref_id, note, request_for)
            VALUES
            ({$item['product_id']}, 0, {$item['qty']}, 'issue', $issue_id, 'Emergency Issue', '$request_for')
        ");



        // ✅ If this is a distribution request to an employee, insert into employee_stock_master
        if ($request_for === 'Distribution' && $issued_to_type === 'employee') {


            mysqli_query($conn, "
                INSERT INTO employee_stock_master
                (employee_id, product_id, stock_in, stock_out, client_id, ref_id, note, request_for)
                VALUES
                ($issued_to_id, {$item['product_id']}, {$item['qty']}, 0, NULL, $issue_id, 'Distribution Issue', 'distribution')
            ");
        }
    }

    echo "<script>alert('Emergency Issue Saved Successfully');window.location='index.php';</script>";
    exit;
}
?>


<style>
.header-box {
    background: linear-gradient(135deg, #da4453, #f6bb42);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.header-box h2 { color: #fff; text-align: center; }
.table thead th { background: #dc3545; color: #fff; }

/* Hover effect */
.table tbody tr:hover {
    background: #f1f5ff;
}
</style>


<div class="pcoded-content">
    <div class="card">

        <div class="header-box">
            <h2>New Emergency Issue</h2>
        </div>

        <div class="card-body">

            <form method="post">

                <!-- Issue To Type -->
                <div class="mb-3">
                    <label><b>Issue To</b></label>
                    <select name="issued_to_type" id="issued_to_type" class="form-control" required>
                        <option value="">Select</option>
                        <option value="employee">Employee</option>
                        <option value="other">Other Receiver</option>
                    </select>
                </div>

                <!-- EMPLOYEE DROPDOWN -->
                <div class="mb-3" id="employee_box" style="display:none;">
                    <label><b>Select Employee</b></label>
                    <select name="employee_id" class="form-control">
                        <option value="">Select Employee</option>
                        <?php
                        $emp = mysqli_query($conn, "SELECT id, name FROM users WHERE role='employee' AND status='active'");
                        while ($e = mysqli_fetch_assoc($emp)):
                        ?>
                            <option value="<?= $e['id'] ?>"><?= $e['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- OTHER RECEIVER DROPDOWN -->
                <div class="mb-3" id="other_box" style="display:none;">
                    <label><b>Select Receiver (Non-Employee)</b></label>
                    <select name="other_receiver_id" id="other_receiver_id" class="form-control">
                        <option value="">Select Receiver</option>
                        <?php
                        $others = mysqli_query($conn, "SELECT id, name, mobile, address FROM other_receivers WHERE status=1");
                        while ($o = mysqli_fetch_assoc($others)):
                        ?>
                            <option value="<?= $o['id'] ?>"
                                data-mobile="<?= $o['mobile'] ?>"
                                data-address="<?= htmlspecialchars($o['address']) ?>">
                                <?= $o['name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <div class="mt-2">
                        <small><b>Mobile:</b> <span id="receiver_mobile">—</span></small><br>
                        <small><b>Address:</b> <span id="receiver_address">—</span></small>
                    </div>
                </div>



                <div class="mb-3">
    <label><b>Request For</b></label>
    <select name="request_for" class="form-control" required>
    <option value="">Select Purpose</option>
    <option value="Personal Use" selected>Personal Use</option>
    <option value="Distribution">Distribution</option>
    <option value="Project">Project</option>
    <option value="Other">Other</option>
</select>

</div>

                <!-- Remarks -->
                <div class="mb-3">
                    <label><b>Remarks / Reason</b></label>
                    <textarea name="remarks" class="form-control" required></textarea>
                </div>

                <hr>

                <h5>Issue Products</h5>

                <!-- PRODUCT TABLE -->
                <table class="table table-bordered" id="itemTable">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>
                                <select class="form-control category" name="category_id[]" required>
                                    <option value="">Select</option>
                                    <?php
                                    $cat = mysqli_query($conn, "SELECT id,name FROM category WHERE status='active'");
                                    while ($c = mysqli_fetch_assoc($cat)):
                                    ?>
                                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </td>

                            <td>
                                <select class="form-control subcategory" name="sub_category_id[]" required>
                                    <option value="">Select Category First</option>
                                </select>
                            </td>

                            <td>
                                <select class="form-control product" name="product_id[]" required>
                                    <option value="">Select Subcategory First</option>
                                </select>
                            </td>

                            <td>
                                <input type="number" name="qty[]" class="form-control" min="1" required>
                            </td>

                            <td>
                                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                            </td>
                        </tr>

                    </tbody>
                </table>

                <button type="button" class="btn btn-primary" id="addRow">+ Add More</button>
                <br><br>
                <button type="submit" name="submit_issue" class="btn btn-success">Save Issue</button>

            </form>

        </div>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>


<script>
// SHOW / HIDE EMPLOYEE / OTHER RECEIVER
$("#issued_to_type").change(function () {
    let t = $(this).val();

    if (t == "employee") {
        $("#employee_box").show();
        $("#other_box").hide();
    } else if (t == "other") {
        $("#employee_box").hide();
        $("#other_box").show();
    } else {
        $("#employee_box").hide();
        $("#other_box").hide();
    }
});

// AUTO-FILL OTHER RECEIVER DETAILS
$(document).on("change", "#other_receiver_id", function () {
    let mobile = $("option:selected", this).data("mobile") || "—";
    let address = $("option:selected", this).data("address") || "—";

    $("#receiver_mobile").text(mobile);
    $("#receiver_address").text(address);
});

// ADD NEW ROW
$("#addRow").click(function () {
    let row = `
        <tr>
            <td>
                <select class="form-control category" name="category_id[]" required>
                    <option value="">Select</option>
                    <?php
                    $cat = mysqli_query($conn, "SELECT id,name FROM category WHERE status='active'");
                    while ($c = mysqli_fetch_assoc($cat)):
                    ?>
                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </td>

            <td>
                <select class="form-control subcategory" name="sub_category_id[]" required>
                    <option value="">Select Category First</option>
                </select>
            </td>

            <td>
                <select class="form-control product" name="product_id[]" required>
                    <option value="">Select Subcategory First</option>
                </select>
            </td>

            <td>
                <input type="number" name="qty[]" class="form-control" min="1" required>
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>
        </tr>
    `;

    $("#itemTable tbody").append(row);
});

// REMOVE ROW
$(document).on("click", ".removeRow", function () {
    $(this).closest("tr").remove();
});

// CATEGORY → SUBCATEGORY AJAX
$(document).on("change", ".category", function () {
    let category_id = $(this).val();
    let row = $(this).closest("tr");

    $.ajax({
        url: "ajax_get_subcategories.php",
        type: "POST",
        data: { category_id: category_id },
        success: function (data) {
            row.find(".subcategory").html(data);
            row.find(".product").html("<option value=''>Select Subcategory First</option>");
        }
    });
});

// SUBCATEGORY → PRODUCTS AJAX
$(document).on("change", ".subcategory", function () {
    let sub_category_id = $(this).val();
    let row = $(this).closest("tr");

    $.ajax({
        url: "ajax_get_products.php",
        type: "POST",
        data: { sub_category_id: sub_category_id },
        success: function (data) {
            row.find(".product").html(data);
        }
    });
});
</script>
