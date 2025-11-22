<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/path.php');
include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

include(BASE_PATH . '/includes/header.php');
include(BASE_PATH . '/includes/sidebar.php');

// Fetch counts from DB
// Total Suppliers
$supplier_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM suppliers");
$supplier_count = mysqli_fetch_assoc($supplier_res)['total'];

// Total Employees (role = 'employee')
$employee_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$employee_count = mysqli_fetch_assoc($employee_res)['total'];

// Total Products
$product_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$product_count = mysqli_fetch_assoc($product_res)['total'];

// Today's Requests
$today = date('Y-m-d');
$request_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM product_requests WHERE DATE(request_date) = '$today'");
$today_request_count = mysqli_fetch_assoc($request_res)['total'];
?>

<div class="pcoded-content">
    <!-- Page-header start -->
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
                            <a href="index.html"> <i class="fa fa-home"></i> </a>
                        </li>
                        <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">

                        <!-- Total Suppliers -->
                          </a>
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-purple"><?= $supplier_count ?></h4>
                                            <h6 class="text-muted m-b-0"><a href="http://localhost/inventory_system/modules/suppliers/index.php" style="text-decoration:none;">Total Suppliers</a></h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-truck f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-purple">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0"><a href="http://localhost/inventory_system/modules/suppliers/index.php" style="text-decoration:none;">All Suppliers</a></p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Employees -->
                         
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-green"><?= $employee_count ?></h4>
                                            <h6 class="text-muted m-b-0"><a href="http://localhost/inventory_system/modules/users/index.php" style="text-decoration:none;">Total Employees</a></h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-users f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-green">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0"><a href="http://localhost/inventory_system/modules/users/index.php" style="text-decoration:none;">All Employees</a></p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        


                        <!-- Total Products -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-red"><?= $product_count ?></h4>
                                            <h6 class="text-muted m-b-0"><a href="http://localhost/inventory_system/modules/product/index.php" style="text-decoration:none;">Total Products</a></h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-cube f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-red">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0"><a href="http://localhost/inventory_system/modules/product/index.php" style="text-decoration:none;">All Products</a></p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Requests -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h4 class="text-c-blue"><?= $today_request_count ?></h4>
                                            <h6 class="text-muted m-b-0"><a href="http://localhost/inventory_system/modules/requests/request_list.php?filter=today" style="text-decoration:none;">Today's Requests</a></h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <i class="fa fa-list f-28"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-c-blue">
                                    <div class="row align-items-center">
                                        <div class="col-9">
                                            <p class="text-white m-b-0"><a href="http://localhost/inventory_system/modules/requests/request_list.php?filter=today" style="text-decoration:none;">Requests Today</a></a></p>
                                        </div>
                                        <div class="col-3 text-right">
                                            <i class="fa fa-line-chart text-white f-16"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div id="styleSelector"></div>
        </div>
    </div>
</div>

<?php 
include __DIR__ . '/../includes/footer.php';
?>
