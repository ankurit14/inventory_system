<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
include('sub_category_functions.php');

$error = "";
$success = "";

// Active categories laa rahe hain
$categories = mysqli_query($conn, "SELECT id, name FROM category WHERE status='active'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['name']) || empty($_POST['category_id'])) {
        $error = "Please fill all required fields.";
    } else {
        $data = [
            'category_id' => $_POST['category_id'],
            'name'        => $_POST['name'],
            'description' => $_POST['description'],
            'status'      => 'active'   // default
        ];

        if (add_subcategory($data)) {
            $success = "Sub Category added successfully!";
        } else {
            $error = "Error inserting data.";
        }
    }
}
?>

<div class="pcoded-content">

    <!-- Page Header -->
    <div class="page-header mt-4 text-center">
        <h2>Add Sub Category</h2>
        <p class="text-muted">Create a new sub category under an existing category</p>
    </div>

    <!-- Center Form -->
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body">

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST">

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Sub Category Name -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sub Category Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Sub Category" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" placeholder="Write something..."></textarea>
                        </div>

                        <!-- Submit -->
                        <button class="btn btn-primary w-100">Add Sub Category</button>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
