<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/path.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');
include('sub_category_functions.php');

$error = "";
$success = "";

// ID CHECK
if (!isset($_GET['id'])) {
    die("Sub Category ID not specified.");
}

$id = intval($_GET['id']);
$res = get_subcategory($id);
$subcat = mysqli_fetch_assoc($res);

if (!$subcat) die("Sub Category not found.");

// GET Active Categories
$categories = mysqli_query($conn, "SELECT id, name FROM category WHERE status='active'");

// FORM SUBMIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['name']) || empty($_POST['category_id'])) {
        $error = "Please fill all required fields.";

    } else {

        $data = [
            'category_id' => $_POST['category_id'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'status' => $_POST['status']
        ];

        if (update_subcategory($id, $data)) {
            $success = "Sub Category updated successfully!";

            // Refresh data
            $res = get_subcategory($id);
            $subcat = mysqli_fetch_assoc($res);
        } else {
            $error = "Error updating sub category.";
        }
    }
}
?>

<style>
.page-header h2 {
    font-weight: 600;
}
.card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 30px 15px;
}
</style>

<div class="pcoded-content">

    <div class="page-header text-center mt-4 mb-4">
        <h2>Edit Sub Category</h2>
        <p class="text-muted">Update sub category name, category, description & status.</p>
    </div>

    <div class="form-container">

        <div class="card">
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error!</strong> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <strong>Success!</strong> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- FORM START -->
                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Select Category *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>

                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?= $cat['id'] ?>" 
                                    <?= $cat['id'] == $subcat['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sub Category Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?= htmlspecialchars($subcat['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($subcat['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?= $subcat['status']=='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $subcat['status']=='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>

                    <button class="btn btn-primary w-100 py-2">Update Sub Category</button>

                </form>
                <!-- FORM END -->

            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    document.getElementById("name").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9 ]/g, "");
    });

});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
