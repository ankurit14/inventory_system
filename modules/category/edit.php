<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/path.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/category_functions.php');
include(BASE_PATH . '/includes/sidebar.php');

$error = "";
$success = "";

// Check ID
if (!isset($_GET['id'])) {
    die("Category ID missing.");
}

$id = intval($_GET['id']);
$res = get_category($id);
$cat = mysqli_fetch_assoc($res);

if (!$cat) die("Category not found.");

// FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['name'])) {
        $error = "Please enter category name.";

    } else {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description']
        ];

        $result = update_category($id, $data);

        if ($result === true) {
            $success = "Category updated successfully!";

            // refresh
            $cat = mysqli_fetch_assoc(get_category($id));

        } else {
            $error = $result;
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
    max-width: 700px;
    margin: 0 auto;
    padding: 30px 15px;
}
</style>

<div class="pcoded-content">

    <div class="page-header text-center mt-4 mb-4">
        <h2>Edit Category</h2>
        <p class="text-muted">Update the category name and description.</p>
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
                <form method="POST" id="editCategory">

                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?= htmlspecialchars($cat['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($cat['description']) ?></textarea>
                    </div>

                    <button class="btn btn-primary w-100 py-2">Update Category</button>

                </form>
                <!-- FORM END -->

            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // Allow only letter, number, space
    document.getElementById("name").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9 ]/g, "");
    });

});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
