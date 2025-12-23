<?php
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');
include('sub_category_functions.php');

$error = "";
$success = "";

// Active categories laa rahe hain
$categories = mysqli_query($conn, "SELECT id, name FROM category WHERE status='active'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description'] ?? '');

    if ($name === "" || $category_id === "") {
        $error = "Please fill all required fields.";
    } else {

        // SERVER SIDE DUPLICATE CHECK
        $safeName = mysqli_real_escape_string($conn, $name);
        $safeCatId = intval($category_id);
        $check = mysqli_query($conn,
            "SELECT id FROM sub_category WHERE category_id=$safeCatId AND LOWER(name)=LOWER('$safeName') LIMIT 1"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = "Sub Category already exists in this category.";
        } else {

            $data = [
                'category_id' => $safeCatId,
                'name'        => $name,
                'description' => $description,
                'status'      => 'active'
            ];

            if (add_subcategory($data)) {
                $success = "Sub Category added successfully!";
            } else {
                $error = "Error inserting data.";
            }
        }
    }
}
?>

<style>
.header-box {background: linear-gradient(135deg, #4e73df, #1cc88a); padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center;}
.header-box h2 {color: #fff; margin: 0; font-size: 24px; font-weight: 600;}
.card {border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);}
.form-container {max-width: 600px; margin: 0 auto; padding: 30px 15px;}
</style>

<div class="pcoded-content">

    <div class="header-box mt-4 mb-4">
        <h2>Add Sub Category</h2>
    </div>

    <div class="form-container">

        <div class="card">
            <div class="card-body">

                <?php if ($error): ?>
                    <div class="alert alert-danger auto-hide-alert"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success auto-hide-alert"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" id="addSubCategoryForm">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Category *</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Sub Category Name *</label>
                        <input type="text" name="name" id="sub_name" class="form-control" placeholder="Enter Sub Category" required>
                        <small id="subcategory-msg"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" placeholder="Write something..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="saveBtn" disabled>Add Sub Category</button>

                </form>

            </div>
        </div>

    </div>

</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const categorySelect = document.getElementById("category_id");
    const subNameInput = document.getElementById("sub_name");
    const msg = document.getElementById("subcategory-msg");
    const saveBtn = document.getElementById("saveBtn");

    // AUTO HIDE ALERT
    setTimeout(() => {
        document.querySelectorAll(".auto-hide-alert").forEach(alert => {
            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        });
    }, 2000);

    // REAL-TIME DUPLICATE CHECK
    function checkSubCategory() {
        const name = subNameInput.value.trim();
        const catId = categorySelect.value;

        if (name === "" || catId === "") {
            msg.innerHTML = "";
            saveBtn.disabled = true;
            return;
        }

        fetch("check_subcategory.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "name=" + encodeURIComponent(name) + "&category_id=" + encodeURIComponent(catId)
        })
        .then(res => res.text())
        .then(data => {
            if (data === "exists") {
                msg.innerHTML = "❌ Sub Category already exists in this category";
                msg.style.color = "red";
                saveBtn.disabled = true;
            } else {
                msg.innerHTML = "✅ Sub Category available";
                msg.style.color = "green";
                saveBtn.disabled = false;
            }
        });
    }

    subNameInput.addEventListener("keyup", checkSubCategory);
    categorySelect.addEventListener("change", checkSubCategory);

});
</script>
