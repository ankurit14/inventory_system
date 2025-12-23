<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

$id = $_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM other_receivers WHERE id='$id'");
$row = mysqli_fetch_assoc($res);
?>

<style>
.form-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    align-items: center;
    margin-bottom: 20px;
    text-align: center;
}

.header-box h2 {
    color: #fff;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}
</style>

<div class="pcoded-content">

    <div class="header-box">
        <h2>Edit Receiver</h2>
    </div>

    <div class="form-container">
        <form action="save.php" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">

            <div class="form-group mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
            </div>

            <div class="form-group mb-3">
                <label>Mobile</label>
                <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($row['mobile']) ?>"  maxlength="10">
            </div>

            <div class="form-group mb-3">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>">
            </div>

            <div class="form-group mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="1" <?= $row['status']==1?'selected':'' ?>>Active</option>
                    <option value="0" <?= $row['status']==0?'selected':'' ?>>Inactive</option>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-success">Update</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
