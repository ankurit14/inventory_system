<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

$issue_id = intval($_GET['id']);

// Fetch issue master
$sql = "
    SELECT e.*, 
           u.name AS issued_by_name,
           emp.name AS employee_name,
           o.name AS other_receiver_name
    FROM emergency_issues e
    LEFT JOIN users u ON e.issued_by = u.id
    LEFT JOIN users emp ON e.issued_to_id = emp.id AND e.issued_to_type = 'employee'
    LEFT JOIN other_receivers o ON e.issued_to_id = o.id AND e.issued_to_type = 'other'
    WHERE e.id = $issue_id
";
$data = mysqli_fetch_assoc(mysqli_query($conn, $sql));

if (!$data) die("Invalid ID");

// Issued to + Type
if ($data['issued_to_type'] == "employee") {
    $issued_type = "Employee";
    $issued_to = $data['employee_name'];
} else {
    $issued_type = "Other";
    $issued_to = $data['other_receiver_name'];
}

// Items
$item_query = "
    SELECT i.*, 
           c.name AS category_name,
           sc.name AS subcategory_name,
           p.name AS product_name
    FROM emergency_issue_items i
    LEFT JOIN category c ON i.category_id = c.id
    LEFT JOIN sub_category sc ON i.sub_category_id = sc.id
    LEFT JOIN products p ON i.product_id = p.id
    WHERE i.issue_id = $issue_id
";
$items = mysqli_query($conn, $item_query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Print Issue</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; }

.title { text-align: center; font-size: 26px; font-weight: bold; }
.subtitle { text-align: center; font-size: 16px; margin-bottom: 20px; }

/* Details Box */
.details-box {
    border: 2px solid #000;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.details-box table {
    width: 100%;
    border-collapse: collapse;
}

.details-box td {
    padding: 4px 6px;
    vertical-align: top;
    font-size: 14px;
}

.details-box td.label {
    font-weight: bold;
    width: 100px; /* fixed width for labels */
    white-space: nowrap;
}

.details-box td.value {
    width: 40%; /* adjusts value width for alignment */
    padding-left: 6px;
}

/* Items Table */
table.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table.items-table, table.items-table th, table.items-table td {
    border: 1px solid #333;
}

table.items-table th {
    background: #f2f2f2;
    padding: 8px;
    text-align: left;
}

table.items-table td {
    padding: 6px;
    font-size: 14px;
}

/* Footer & Sign */
.footer { margin-top: 40px; }
.sign { margin-top: 60px; text-align: left; font-weight: bold; }
</style>
</head>
<body>

<div class="title">Emergency Product Issue Slip</div>
<div class="subtitle">Inventory Management System</div>

<div class="details-box">
    <table>
        <tr>
            <td class="label">Issue ID:</td>
            <td class="value"><?= $issue_id ?></td>
            <td class="label">Type:</td>
            <td class="value"><?= $issued_type ?></td>
        </tr>
        <tr>
            <td class="label">Issued To:</td>
            <td class="value"><?= $issued_to ?></td>
            <td class="label">Issued By:</td>
            <td class="value"><?= $data['issued_by_name'] ?></td>
        </tr>
        <!-- New Request For / Purpose -->
        <tr>
            <td class="label">Request For:</td>
            <td class="value" colspan="3"><?= htmlspecialchars($data['request_for']) ?></td>
        </tr>
        <tr>
            <td class="label">Date:</td>
            <td class="value" colspan="3"><?= date("d-M-Y h:i A", strtotime($data['issue_date'])) ?></td>
        </tr>
        <tr>
            <td class="label" style="vertical-align: top;">Remarks:</td>
            <td class="value" colspan="3"><?= nl2br($data['remarks']) ?></td>
        </tr>
    </table>
</div>

<table class="items-table">
<tr>
    <th>#</th>
    <th>Category</th>
    <th>Sub Category</th>
    <th>Product</th>
    <th>Unit</th>
    <th>Qty</th>
</tr>

<?php 
$i = 1;
while ($row = mysqli_fetch_assoc($items)) : 
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= $row['category_name'] ?></td>
    <td><?= $row['subcategory_name'] ?></td>
    <td><?= $row['product_name'] ?></td>
    <td><?= $row['unit'] ?></td>
    <td><?= $row['qty_issued'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<div class="footer">
    <div class="sign">_________________________<br> Issued By</div>
</div>

<script>window.print();</script>

</body>
</html>
