<?php
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

// DB connection assumed via $pdo or your framework
?>

<div class="pcoded-content">

    <!-- HEADER -->
    <div class="header-box d-flex justify-content-between align-items-center">
        <h4>Expense Management</h4>
        <a href="add.php" class="btn btn-light shadow-sm">+ Add Expense</a>
    </div>

    <div class="container mt-4">

        <!-- FILTER FORM -->
        <form class="row g-3 align-items-end" id="filterForm">

            <div class="col-md-2">
                <label>Receiver Type</label>
                <select name="rtype" class="form-control">
                    <option value="">All</option>
                    <option value="employee">Employee</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>Receiver Name</label>
                <input type="text" name="name" class="form-control" placeholder="Search by name">
            </div>

            <div class="col-md-2">
                <label>From Date</label>
                <input type="date" name="from" class="form-control">
            </div>

            <div class="col-md-2">
                <label>To Date</label>
                <input type="date" name="to" class="form-control">
            </div>

            <div class="col-md-2">
                <div id="totalExpenseCard" class="total-card">
                    <h6>Total Expense</h6>
                    <h4>₹ 0.00</h4>
                    <small>(No data)</small>
                </div>
            </div>

        </form>

        <!-- RESULTS -->
        <div id="results" class="mt-3"></div>

    </div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>

<!-- BOOTSTRAP CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ================= LOAD EXPENSES ================= */
function loadExpenses(page = 1) {
    const formData = new FormData(document.getElementById('filterForm'));
    formData.append('page', page);

    const params = new URLSearchParams(formData);

    fetch("expense_ajax_data.php?" + params.toString())
        .then(res => res.text())
        .then(html => {
            document.getElementById("results").innerHTML = html;
            updateTotal();
        });
}

/* ================= TOTAL ================= */
function updateTotal() {
    const formData = new FormData(document.getElementById('filterForm'));
    formData.append('total_only', 'yes');

    const params = new URLSearchParams(formData);

    fetch("expense_ajax_data.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            document.querySelector("#totalExpenseCard h4").innerText =
                "₹ " + parseFloat(data.total_expense || 0).toFixed(2);
            document.querySelector("#totalExpenseCard small").innerText =
                `(${data.record_count || 0} record(s))`;
        });
}

/* ================= FILTER EVENTS ================= */
let timer;
document.querySelectorAll("#filterForm select, #filterForm input").forEach(el => {
    el.addEventListener("input", () => {
        clearTimeout(timer);
        timer = setTimeout(() => loadExpenses(), 300);
    });
});

/* ================= VIEW MODAL ================= */
function openViewModal(type, name, amount, method, apv, notes, date, cheque, image) {
    document.getElementById("viewTitle").innerText = name + " - " + date;
    document.getElementById("p_type").innerText = type;
    document.getElementById("p_name").innerText = name;
    document.getElementById("p_amount").innerText = amount;
    document.getElementById("p_method").innerText = method;
    document.getElementById("p_apv").innerText = apv || "-";
    document.getElementById("p_notes").innerText = notes;
    document.getElementById("p_date").innerText = date;
    document.getElementById("cheque_no").innerText = cheque || "";

    document.getElementById("chequeBlock").style.display =
        method.toLowerCase() === "cash" ? "none" : "";

    document.getElementById("voucherImage").src = image
        ? "<?= BASE_URL ?>uploads/vouchers/" + image
        : "https://via.placeholder.com/200";

    new bootstrap.Modal(document.getElementById("viewModal")).show();
}

/* ================= PRINT MODAL ================= */
function openVoucherPrintModal(type, name, amount, method, apv, notes, date, cheque, image) {
    document.getElementById("vp_type").innerText = type;
    document.getElementById("vp_name").innerText = name;
    document.getElementById("vp_amount").innerText = amount;
    document.getElementById("vp_method").innerText = method;
    document.getElementById("vp_apv").innerText = apv || "-";
    document.getElementById("vp_notes").innerText = notes;
    document.getElementById("vp_date").innerText = date;
    document.getElementById("vp_cheque_no").innerText = cheque || "";

    document.getElementById("vpChequeBlock").style.display =
        method.toLowerCase() === "cash" ? "none" : "table-row";

    // If you have an image element with id="vpImage", set it here:
    /*
    document.getElementById("vpImage").src = image
        ? "<?= BASE_URL ?>uploads/vouchers/" + image
        : "https://via.placeholder.com/200";
    */

    new bootstrap.Modal(document.getElementById("voucherPrintModal")).show();
}

/* ================= PRINT ================= */
function printVoucher() {
    let html = document.getElementById("voucherPrintArea").innerHTML;
    let originalBody = document.body.innerHTML;

    document.body.innerHTML = `
        <style>
            table { width: 100%; border-collapse: collapse; }
            table, th, td { border: 1px solid #000; padding: 8px; }
            .text-center { text-align: center; }
            .fw-bold { font-weight: bold; }
        </style>
    ` + html;

    window.print();

    document.body.innerHTML = originalBody;
    location.reload();
}

/* ================= INITIAL LOAD ================= */
loadExpenses();
</script>

<?php include "view_modal.php"; ?>
<?php include "voucher_print_modal.php"; ?>

<style>
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px;
    border-radius: 8px;
    color: #fff;
}
.total-card {
    background: #1cc88a;
    color: #fff;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
}
</style>
