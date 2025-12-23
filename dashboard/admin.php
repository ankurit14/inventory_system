<?php
session_start();

include_once __DIR__ . '/../config/path.php';
include(BASE_PATH.'/config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

/* ================= FETCH COUNTS ================= */

$supplier_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM suppliers");
$supplier_count = mysqli_fetch_assoc($supplier_res)['total'];

$employee_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$employee_count = mysqli_fetch_assoc($employee_res)['total'];

$product_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$product_count = mysqli_fetch_assoc($product_res)['total'];

$assets_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM company_assets");
$assets_count = mysqli_fetch_assoc($assets_res)['total'];

$today = date('Y-m-d');
$request_res = mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM product_requests WHERE DATE(request_date) = '$today'"
);
$today_request_count = mysqli_fetch_assoc($request_res)['total'];
?>

<style>
/* Footer links */
.card-footer a {
    color: #ffffff;
    text-decoration: none;
    transition: color 0.3s;
}
.card-footer a:hover {
    color: #ffd700;
    text-decoration: underline;
}

/* ORANGE COLOR FOR ASSETS CARD (FIX) */
.bg-c-orange {
    background: linear-gradient(135deg, #ff9800, #ff5722);
}
.text-c-orange {
    color: #ff9800;
}
</style>

<div class="pcoded-content">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Admin Dashboard</h5>
                        <p class="m-b-0">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item">
                            <a href="admin.php"><i class="fa fa-home"></i></a>
                        </li>
                        <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- PAGE HEADER END -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">

                        <!-- COMPANY ASSETS -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-orange"><?= $assets_count ?></h4>
                                            <h6 class="text-muted m-b-0">
                                                <a href="<?= BASE_URL ?>modules/company_assets/index.php">
                                                    Company Assets
                                                </a>
                                            </h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-archive f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-orange">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0">
                                                <a href="<?= BASE_URL ?>modules/assets/index.php">
                                                    All Assets
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TOTAL SUPPLIERS -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-purple"><?= $supplier_count ?></h4>
                                            <h6 class="text-muted m-b-0">
                                                <a href="<?= BASE_URL ?>modules/suppliers/index.php">
                                                    Total Suppliers
                                                </a>
                                            </h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-truck f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-purple">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0">
                                                <a href="<?= BASE_URL ?>modules/suppliers/index.php">
                                                    All Suppliers
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TOTAL EMPLOYEES -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-green"><?= $employee_count ?></h4>
                                            <h6 class="text-muted m-b-0">
                                                <a href="<?= BASE_URL ?>modules/users/index.php">
                                                    Total Employees
                                                </a>
                                            </h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-users f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-green">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0">
                                                <a href="<?= BASE_URL ?>modules/users/index.php">
                                                    All Employees
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TOTAL PRODUCTS -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-red"><?= $product_count ?></h4>
                                            <h6 class="text-muted m-b-0">
                                                <a href="<?= BASE_URL ?>modules/product/index.php">
                                                    Total Products
                                                </a>
                                            </h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-cube f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-red">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0">
                                                <a href="<?= BASE_URL ?>modules/product/index.php">
                                                    All Products
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TODAY REQUESTS -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-blue"><?= $today_request_count ?></h4>
                                            <h6 class="text-muted m-b-0">
                                                <a href="<?= BASE_URL ?>modules/requests/request_list.php?filter=today">
                                                    Today's Requests
                                                </a>
                                            </h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-list f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-blue">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0">
                                                <a href="<?= BASE_URL ?>modules/requests/request_list.php?filter=today">
                                                    Requests Today
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- row end -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
