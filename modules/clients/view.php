<?php
include_once __DIR__ . '/../../config/db.php';

$id = intval($_GET['id']);

// Check valid ID
if($id <= 0) {
    echo "<div class='alert alert-danger'>Invalid client ID.</div>";
    exit;
}

$res = mysqli_query($conn, "SELECT * FROM clients WHERE id=$id AND is_deleted=0");

if(mysqli_num_rows($res) == 0){
    echo "<div class='alert alert-warning'>Client not found.</div>";
    exit;
}

$client = mysqli_fetch_assoc($res);
?>

<table class="table table-bordered">
    <tr><th>Name</th><td><?= htmlspecialchars($client['name']) ?></td></tr>
    <tr><th>Business</th><td><?= htmlspecialchars($client['company']) ?></td></tr>
    <tr><th>Email</th><td><?= htmlspecialchars($client['email']) ?></td></tr>
    <tr><th>Phone</th><td><?= htmlspecialchars($client['phone']) ?></td></tr>
    <tr><th>Status</th><td><?= htmlspecialchars($client['status']) ?></td></tr>
    <tr><th>Address</th><td><?= nl2br(htmlspecialchars($client['address'])) ?></td></tr>
    <tr><th>Created</th><td><?= htmlspecialchars($client['created_at']) ?></td></tr>
</table>
