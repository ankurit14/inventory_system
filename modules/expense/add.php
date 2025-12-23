<?php
session_start();
include_once __DIR__ . '/../../config/path.php';

include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');
include('others_functions.php');
include(BASE_PATH . '/includes/users_functions.php'); // FIXED: added users function
$users = get_all_users();
$others = get_all_others();


$errors = [];
$success = "";
$old = $_POST ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['received_amt'])) $errors['received_amt'] = "Debitor Amount is required!";
    if (empty($_POST['date'])) $errors['date'] = "Date is required!";
    if (empty($_POST['payment_method'])) $errors['payment_method'] = "Payment Method is required!";
    if (empty($_POST['apv_by'])) $errors['apv_by'] = "Approved By is required!";
    if (empty($_POST['receiver_type'])) $errors['receiver_type'] = "Receiver Type is required!";
    if ($_POST['receiver_type'] === 'other' && empty($_POST['receiver_id'])) {
        $errors['receiver_id'] = "Other Receiver is required!";
    }

    // Handle file upload for voucher image
    // Handle file upload for voucher image
    $voucher_image = '';
    if (isset($_FILES['voucher_image']) && $_FILES['voucher_image']['error'] === UPLOAD_ERR_OK) {

        $upload_dir = BASE_PATH . '/uploads/vouchers/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Use original name from the uploaded file
        $image_name = $_FILES['voucher_image']['name'];
        $file_path = $upload_dir . $image_name;

        // Move the file
        if (move_uploaded_file($_FILES['voucher_image']['tmp_name'], $file_path)) {
            $voucher_image = 'uploads/vouchers/' . $image_name;
        } else {
            $errors['voucher_image'] = "Failed to upload voucher image!";
        }
    } else {
        $errors['voucher_image'] = "Voucher Image is required!";
    }


    if (empty($errors)) {
        // Include the function file that contains add_voucher function
        include('./expense_function.php');

        // Prepare data for insertion
        $voucher_data = [
            'receiver_type' => $_POST['receiver_type'],
            'receiver_id'   => $_POST['receiver_id'],
            'received_amt'  => $_POST['received_amt'],
            'date'          => $_POST['date'],
            'payment_method' => $_POST['payment_method'],
            'cheque_no'     => $_POST['cheque_no'] ?? '',
            'voucher_image' => $image_name,
            'apv_by'        => $_POST['apv_by'],
            'notes'         => $_POST['notes'] ?? ''
        ];

        if (add_voucher($voucher_data)) {
            $success = "Voucher Added Successfully!";
            $old = [];
        } else {
            $errors['form'] = "Something went wrong while adding the voucher!";
        }
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

    .alert-inline {
        padding: 0.75rem;
        margin-bottom: 10px;
        border-radius: 4px;
        font-size: 0.875rem;
    }
</style>

<div class="pcoded-content">
    <div class="header-box">
        <h2 style="color: #ffffff; margin: 0;">Add Voucher</h2>
    </div>

    <div class="card">
        <?php if (isset($errors['form'])): ?>
            <div class="alert alert-danger"><?= $errors['form'] ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" id="voucherForm" enctype="multipart/form-data" novalidate>
            <div class="p-3 mb-3">
                <h5>Voucher Information</h5>
                <div class="row">
                    <input type="hidden" name="receiver_id" id="receiver_id">

                    <div class="col-md-4 mb-3">
                        <label><strong>Receiver Type</strong></label>
                        <select name="receiver_type" id="receiver_type" class="form-control" required onchange="toggleReceiverFields()">
                            <option value="">Select Type</option>
                            <option value="employee" selected>Employee</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Employee List -->
                    <div class="col-md-4 mb-3" id="employee_box" style="display:none;">
                        <label><strong>Select Employee</strong></label>
                        <div class="d-flex">
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">Select User</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= $u['name'] ?></option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                    </div>

                    <!-- Other Receiver -->
                    <div class="col-md-4 mb-3" id="other_box" style="display:none;">
                        <label><strong>Other Receiver Name</strong></label>
                        <div class="d-flex">
                            <select name="other_name" id="other_name" class="form-control">
                                <option value="">Select Other Receiver</option>
                                <?php foreach ($others as $o): ?>
                                    <option value="<?= $o['id'] ?>"><?= $o['name'] ?></option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                    </div>

                    <!-- Receiver Name -->
                    <!-- <div class="col-md-4 mb-3">
                        <label><strong>Receiver Name</strong></label>
                        <input type="text" name="debitor_name" class="form-control"
                            value="<?= htmlspecialchars($old['debitor_name'] ?? '') ?>" required>
                        <?php if (isset($errors['debitor_name'])): ?>
                            <small class="text-danger"><?= $errors['debitor_name'] ?></small>
                        <?php endif; ?>
                    </div> -->

                    <!-- Received Amount -->
                   <div class="col-md-4 mb-3">
    <label><strong>Received Amount</strong></label>
    <input type="number"
           name="received_amt"
           class="form-control"
           step="0.01"
           min="0.01"
           onkeydown="return event.key !== '-'"
           value="<?= htmlspecialchars($old['received_amt'] ?? '') ?>"
           required>

    <?php if (isset($errors['received_amt'])): ?>
        <small class="text-danger"><?= $errors['received_amt'] ?></small>
    <?php endif; ?>
</div>


                    <!-- Date -->
                 <div class="col-md-4 mb-3">
    <label><strong>Date</strong></label>
    <input type="date" name="date" class="form-control"
        value="<?= htmlspecialchars($old['date'] ?? date('Y-m-d')) ?>" required 
        max="<?= date('Y-m-d') ?>">
    <?php if (isset($errors['date'])): ?>
        <small class="text-danger"><?= $errors['date'] ?></small>
    <?php endif; ?>
</div>





                    <!-- <div class="col-md-4 mb-3">
    <label><strong>Date</strong></label>
    <input type="date"
           name="date"
           class="form-control"
           value="<?= htmlspecialchars($old['date'] ?? date('Y-m-d')) ?>"
           readonly
           required>

    <?php if (isset($errors['date'])): ?>
        <small class="text-danger"><?= $errors['date'] ?></small>
    <?php endif; ?>
</div> -->


                    <!-- Payment Method -->
                    <div class="col-md-4 mb-3">
                        <label><strong>Payment Method</strong></label>
                        <select name="payment_method" id="payment_method" class="form-control" required onchange="toggleChequeNo()">
                            <option value="">Select Payment Method</option>

                            <option value="cash"
                                <?= (isset($old['payment_method']) && $old['payment_method'] === 'cash') ? 'selected' : '' ?>>
                                Cash
                            </option>

                            <option value="bank_transfer"
                                <?= (isset($old['payment_method']) && $old['payment_method'] === 'bank_transfer') ? 'selected' : '' ?>>
                                Bank Transfer
                            </option>

                            <option value="cheque"
                                <?= (isset($old['payment_method']) && $old['payment_method'] === 'cheque') ? 'selected' : '' ?>>
                                Cheque
                            </option>
                        </select>
                        <?php if (isset($errors['payment_method'])): ?>
                            <small class="text-danger"><?= $errors['payment_method'] ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Cheque No / Reference No -->
                    <div class="col-md-4 mb-3">
                        <label><strong>Cheque No. / Reference No.</strong></label>
                        <input type="text" name="cheque_no" id="cheque_no" class="form-control"
                            value="<?= htmlspecialchars($old['cheque_no'] ?? '') ?>" disabled>
                    </div>

                    <!-- Voucher Image -->
                    <div class="col-md-4 mb-3">
                        <label><strong>Voucher Image</strong></label>
                        <input type="file" name="voucher_image" class="form-control" accept="image/*" required>
                        <?php if (isset($errors['voucher_image'])): ?>
                            <small class="text-danger"><?= $errors['voucher_image'] ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Approved By -->
                    <div class="col-md-4 mb-3">
                        <label><strong>Approved By</strong></label>
                        <select name="apv_by" class="form-control" required>
                            <option value="">Select Approver</option>
                            <option value="Admin" <?= (isset($old['apv_by']) && $old['apv_by'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="Manager" <?= (isset($old['apv_by']) && $old['apv_by'] === 'Manager') ? 'selected' : '' ?>>Manager</option>
                            <option value="Supervisor" <?= (isset($old['apv_by']) && $old['apv_by'] === 'Supervisor') ? 'selected' : '' ?>>Supervisor</option>
                        </select>
                        <?php if (isset($errors['apv_by'])): ?>
                            <small class="text-danger"><?= $errors['apv_by'] ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Notes -->
                    <div class="col-md-12 mb-3">
                        <label><strong>Notes</strong></label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="text-right mx-2">
                        <button type="submit" class="btn btn-primary w-100">Add Voucher</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>

<script>
    function toggleChequeNo() {
        let method = document.getElementById("payment_method").value;
        let chequeInput = document.getElementById("cheque_no");

        if (method === "cash" || method === "") {
            chequeInput.value = "";
            chequeInput.disabled = true;
        } else {
            chequeInput.disabled = false;
        }
    }

    function toggleReceiverFields() {
        let type = document.getElementById("receiver_type").value;

        document.getElementById("employee_box").style.display = (type === "employee") ? "block" : "none";
        document.getElementById("other_box").style.display = (type === "other") ? "block" : "none";
    }


    window.onload = function() {
        toggleChequeNo();
        toggleReceiverFields();
    };

    // -------------------------------
    // SELECT2 – OTHERS
    // -------------------------------

    // $("#other_name").on("select2:select", function(e) {
    //     let d = e.params.data;

    //     if (d.element) return; // already exists

    //     let name = d.text;

    //     $.post("ajax_add_other.php", { name }, function(res) {
    //         let x = JSON.parse(res);

    //         if (x.status === "success") {
    //             let newOption = new Option(x.name, x.name, true, true);
    //             $("#other_name").append(newOption).trigger("change");
    //         }
    //     });
    // });


    $(document).ready(function() {

        // Select2 for Other Receiver
        $('#other_name').select2({
            tags: true,
            placeholder: "Select or Add Receiver",
            width: "100%",
            createTag: function(params) {
                let term = $.trim(params.term);
                if (term === '') return null;

                return {
                    id: "new:" + term,
                    text: term,
                    newOption: true
                };
            },
            templateResult: function(data) {
                if (data.newOption) {
                    return $('<span style="color:#28a745;font-weight:bold;">' + data.text + '</span>');
                }
                return data.text;
            }
        });

        // When new Other Receiver is selected
        $('#other_name').on('select2:select', function(e) {
            let data = e.params.data;

            if (data.id.startsWith("new:")) {
                let newName = data.id.replace("new:", "");

                $.post("ajax_add_other.php", {
                    name: newName
                }, function(res) {

                    if (res.success) {

                        // Remove temp option
                        $("#other_name option[value='" + data.id + "']").remove();

                        // Add DB saved ID
                        let newOption = new Option(res.data.name, res.data.id, true, true);

                        $('#other_name').append(newOption).trigger("change");

                        // Set hidden receiver_id
                        $('#receiver_id').val(res.data.id);

                    } else {
                        alert(res.message);
                    }

                }, "json");
            } 
            else {
                // Existing receiver
                $('#receiver_id').val(data.id);
                // alert('Existing receiver selected: ' + data.id);
            }
        });

        $('#user_id').select2({
            tags: true,
            placeholder: "Select or Add User",
            width: "100%",
            createTag: function(params) {
                const term = $.trim(params.term);
                if (term === '') return null;
                return {
                    id: "new:" + term,
                    text: term,
                    newOption: true
                };
            },
            templateResult: function(data) {
                if (data.newOption) {
                    return $('<span style="color:#28a745;font-weight:bold;">' + data.text + '</span>');
                }
                return data.text;
            }
        });

        $('#user_id').on('select2:select', function(e) {
            const data = e.params.data;

            if (!data.id.startsWith("new:")) {
                $('#receiver_id').val(data.id);
                // alert('Existing user selected: ' + data.id);
                return;
            }

            const name = data.id.replace("new:", "");

            $.post("ajax_add_user.php", {
                name: name
            }, function(res) {

                if (res.status === "success") {

                    $("#user_id option[value='" + data.id + "']").remove();

                    let option = new Option(res.name, res.id, true, true);
                    $('#user_id').append(option).trigger("change");

                    $('#receiver_id').val(res.id);

                    // alert(
                    //     "User Created!\n" +
                    //     "Username: " + res.username + "\n" +
                    //     "Password: " + res.password
                    // );

                } else {
                    alert(res.msg);
                    console.log(res.msg);
                }

            }, "json");
        });

    });
</script>