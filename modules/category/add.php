<?php
session_start();

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/category_functions.php');
include(BASE_PATH . '/includes/sidebar.php');

$error = "";
$success = "";

/* ------------------------------------
   FORM SUBMISSION + VALIDATION
-------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');

    if ($name === "") {
        $error = "Category name is required.";

    } else {

        // SERVER-SIDE DUPLICATE CHECK
        $safeName = mysqli_real_escape_string($conn, $name);
        $check = mysqli_query(
            $conn,
            "SELECT id FROM category WHERE LOWER(name)=LOWER('$safeName') LIMIT 1"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = "Category already exists.";

        } else {

            if (add_category($_POST)) {
                $success = "Category added successfully!";
            } else {
                $error = "Database error. Please try again.";
            }
        }
    }
}
?>

<style>
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
        <p class="text-muted">Create a new product category</p>
    </div>

    <div class="form-container">

        <div class="card">
            <div class="card-body p-4">

                <!-- ALERTS -->
                <?php if ($error): ?>
                    <div class="alert alert-danger auto-hide-alert">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success auto-hide-alert">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <!-- FORM -->
                <form method="POST" id="addCategoryForm" autocomplete="off">

                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control"
                               placeholder="Enter category name"
                               required>

                        <small id="category-msg"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Enter category description"></textarea>
                    </div>

                    <button type="submit"
                            class="btn btn-primary w-100 py-2"
                            id="saveBtn"
                            disabled>
                        Save Category
                    </button>

                </form>

            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const nameInput = document.getElementById("name");
    const msg = document.getElementById("category-msg");
    const saveBtn = document.getElementById("saveBtn");

    /* AUTO HIDE ALERT AFTER 2 SEC */
    setTimeout(() => {
        document.querySelectorAll(".auto-hide-alert").forEach(alert => {
            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        });
    }, 2000);

    /* REAL-TIME DUPLICATE CHECK */
    nameInput.addEventListener("keyup", function () {

        let name = this.value.trim();

        // Allow letters numbers & space only
        this.value = this.value.replace(/[^A-Za-z0-9 ]/g, "");

        if (name === "") {
            msg.innerHTML = "";
            saveBtn.disabled = true;
            return;
        }

        fetch("check_category.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "name=" + encodeURIComponent(name)
        })
        .then(res => res.text())
        .then(data => {

            if (data === "exists") {
                msg.innerHTML = "❌ Category already exists";
                msg.style.color = "red";
                saveBtn.disabled = true;
            } else {
                msg.innerHTML = "✅ Category available";
                msg.style.color = "green";
                saveBtn.disabled = false;
            }
        });
    });
});
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
