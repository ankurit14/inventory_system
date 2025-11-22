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
<style>
.header-box {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    padding: 15px 20px;
    border-radius: 8px;
    align-items: center;
    margin-bottom: 20px;
}
.header-box h2 {
    color: #fff;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    text-align: center;
}

.header-box h5 {
    color: #fff;
    margin: 0;
    font-size: 20px;
    font-weight: 200;
}
.header-box a.btn {
    color: #1f2937;
    background-color: #fff;
    padding: 6px 15px;
    border-radius: 6px;
    text-decoration: none;
}

.filter-container input, .filter-container select {
    padding: 6px 10px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.table thead th {
    background: #2d6cdf;
    color: #fff;
    font-size: 14px;
}
.table tbody td {
    font-size: 14px;
    padding: 6px 10px;
}
.table tbody tr:hover {
    background: #f1f5ff;
}

.btn-sm {
    padding: 3px 7px;
    font-size: 13px;
}
.status-btn {
    min-width: 80px;
}
.table thead th {
    background: #2d6cdf;
    color: white;
    font-size: 14px;
    padding: 4px 6px !important;
    height: 30px !important;
    line-height: 14px;
}
.page-header-bg {
    position: absolute;
    top: 5px;
    left: 0;
    width: 100%;
    height: 50%;
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    z-index: 1;
    border-radius: 8px;
}

</style>
<div class="pcoded-content">

    <!-- Page Header -->
    <div class="header-box mt-4 text-center">
        <h2>Add Sub Category</h2>
        <!-- <p class="text-muted">Create a new sub category under an existing category</p> -->
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
