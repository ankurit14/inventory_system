<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/path.php');
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/category_functions.php');
include(BASE_PATH . '/includes/sidebar.php');

$error = "";
$success = "";

/* ------------------------------------
   FORM SUBMISSION + VALIDATION
-------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['name'])) {
        $error = "Category name is required.";

    } else {

        if (add_category($_POST)) {
            $success = "Category added successfully!";
        } else {
            $error = "Database error. Please try again.";
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
        <h2>Add New Category</h2>
        <p class="text-muted">Create a new product category for inventory management.</p>
    </div>

    <div class="form-container">

        <div class="card">
            <div class="card-body p-4">

                <!-- ERROR MESSAGE -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error!</strong> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- SUCCESS MESSAGE -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <strong>Success!</strong> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- FORM START -->
                <form method="POST" id="addCategoryForm">

                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               placeholder="Enter category name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Enter category description"></textarea>
                    </div>

                    <button class="btn btn-primary w-100 py-2">Save Category</button>

                </form>
                <!-- FORM END -->

            </div>
        </div>

    </div>

</div>

<script>
// Input Validation
document.addEventListener("DOMContentLoaded", () => {

    document.getElementById("name").addEventListener("input", function () {
        // Allow letters, numbers & spaces
        this.value = this.value.replace(/[^A-Za-z0-9 ]/g, "");
    });

});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
