<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['name'] ?? 'User';
?>

<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">

        <div class="">
            <div class="main-menu-header">
                <!-- <img class="img-80 img-radius" src="<?php echo BASE_URL; ?>assets/images/avatar-4.jpg" alt="User"> -->
                <div class="user-details">
                    <span id="more-details"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>
        </div>

        <!-- <div class="pcoded-navigation-label">Layout</div> -->

        <ul class="pcoded-item pcoded-left-item mt-2">
            <?php
            if (isset($_SESSION['role'])) {
                if ($_SESSION['role'] == 'admin') {
                    $dashboard_url = BASE_URL . 'dashboard/admin.php';
                } elseif ($_SESSION['role'] == 'hr') {
                    $dashboard_url = BASE_URL . 'dashboard/hr.php';
                } elseif ($_SESSION['role'] == 'employee') {
                    $dashboard_url = BASE_URL . 'dashboard/employee.php';
                } else {
                    $dashboard_url = BASE_URL . 'login.php';
                }
            } else {
                $dashboard_url = BASE_URL . 'login.php';
            }
            ?>

            <li class="active">
                <a href="<?= $dashboard_url ?>" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <?php if ($role == 'admin' || $role == 'hr') { ?>

                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-user"></i></span>
                        <span class="pcoded-mtext">Employee</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/users/index.php">
                                <span class="pcoded-mtext">Employee List</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/users/add.php">
                                <span class="pcoded-mtext">Add Employee</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- <li><a href="<?php echo BASE_URL; ?>modules/users/index.php"><span class="pcoded-micon"><i class="ti-user"></i></span>Users</a></li> -->
            <?php } ?>







            <?php if ($role == 'admin' || $role == 'purchase') { ?>

    <li class="pcoded-hasmenu">
        <a href="javascript:void(0)">
            <span class="pcoded-micon"><i class="ti-briefcase"></i></span>
            <span class="pcoded-mtext">Clients</span>
        </a>
        <ul class="pcoded-submenu">
            <li>
                <a href="<?php echo BASE_URL; ?>modules/clients/index.php">
                    <span class="pcoded-mtext">Client List</span>
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>modules/clients/add.php">
                    <span class="pcoded-mtext">Add Client</span>
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>modules/clients/trash.php">
                    <span class="pcoded-mtext">Trash</span>
                </a>
            </li>
        </ul>
    </li>

<?php } ?>






            <?php if ($role == 'admin' || $role == 'hr') { ?>

                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-id-badge"></i></span>
                        <span class="pcoded-mtext">Other Receivers</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/other_receivers/index.php">
                                <span class="pcoded-mtext">All Receivers</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/other_receivers/add.php">
                                <span class="pcoded-mtext">Add Receiver</span>
                            </a>
                        </li>
                    </ul>
                </li>

            <?php } ?>





             <?php if ($role == 'admin' || $role == 'hr') { ?>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-agenda"></i></span>
                        <span class="pcoded-mtext">Supplier</span>

                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/suppliers/index.php">
                                <span class="pcoded-mtext">All Suppliers</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/suppliers/add.php">
                                <span class="pcoded-mtext">Add Supplier</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } ?> 



            <?php if ($role === 'admin' || $role === 'hr') : ?>


                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-layers"></i></span>
                        <span class="pcoded-mtext">Category</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/Category/index.php">
                                <span class="pcoded-mtext">All Category</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/Category/add.php">
                                <span class="pcoded-mtext">Add Category</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>


            <!-- <li>
        <a href="<?php echo BASE_URL; ?>modules/category/index.php">
            <span class="pcoded-micon"><i class="fa fa-tag"></i></span>
            Category
        </a>
    </li> -->
            <?php if ($role === 'admin' || $role === 'hr') : ?>

                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-folder"></i></span>

                        <span class="pcoded-mtext">Sub Category</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/sub_category/index.php">
                                <span class="pcoded-mtext">All Sub Category</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/sub_category/add.php">
                                <span class="pcoded-mtext">Add Sub Category</span>
                            </a>
                        </li>
                    </ul>
                </li>



                <!-- <li>
        <a href="<?php echo BASE_URL; ?>modules/sub_category/index.php">
            <span class="pcoded-micon"><i class="fa fa-tags"></i></span>
            Sub Category
        </a>
    </li> -->
            <?php endif; ?>



             <?php if ($role == 'admin' || $role == 'hr') { ?>
                <!-- <li><a href="<?php echo BASE_URL; ?>modules/product/index.php"><span class="pcoded-micon"><i class="ti-package"></i></span>Product</a></li> -->
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-briefcase"></i></span>
                        <span class="pcoded-mtext">Product</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/product/index.php">
                                <span class="pcoded-mtext">All Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/product/add.php">
                                <span class="pcoded-mtext">Add Product</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } ?>




           

           


            <?php if ($role == 'admin' || $role == 'hr') { ?>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-shopping-cart"></i></span>
                        <span class="pcoded-mtext">Purchase</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/purchase/index.php">
                                <span class="pcoded-mtext">All Purchase</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/purchase/add.php">
                                <span class="pcoded-mtext">Add Purchase</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-stats-up"></i></span>
                        <span class="pcoded-mtext">Stock</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/stock/product_stock.php">
                                <span class="pcoded-mtext">All Product Stock</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr' || $role == 'employee') { ?>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-clipboard"></i></span>
                        <span class="pcoded-mtext">Request Approval</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/requests/request_list.php">
                                <span class="pcoded-micon"><i class="ti-plus"></i></span>
                                Request List
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/requests/request_add.php">
                                <span class="pcoded-micon"><i class="ti-plus"></i></span>
                                Request Add
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-alert"></i></span>
                        <span class="pcoded-mtext">Emergency Issue</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li><a href="<?= BASE_URL ?>modules/emergency_issue/index.php">Issue List</a></li>
                        <li><a href="<?= BASE_URL ?>modules/emergency_issue/add.php">New Issue</a></li>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
                <!-- <li><a href="<?php echo BASE_URL; ?>modules/product/index.php"><span class="pcoded-micon"><i class="ti-package"></i></span>Product</a></li> -->
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-wallet"></i></span>
                        <span class="pcoded-mtext">Expense</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/expense/index.php">
                                <span class="pcoded-mtext">All Expense</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/expense/add.php">
                                <span class="pcoded-mtext">Add Expense</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-package"></i></span>
                        <span class="pcoded-mtext">Assets</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/assets/index.php">
                                <span class="pcoded-micon"><i class="ti-plus"></i></span>
                                Assets List
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/assets/add.php">
                                <span class="pcoded-micon"><i class="ti-plus"></i></span>
                                Assets Add
                            </a>
                        </li>
                    </ul>
                </li>

            <?php } ?>


            <?php if ($role == 'admin' || $role == 'hr' || $role == 'employee') { ?>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="ti-file"></i></span>
                        <span class="pcoded-mtext">New Product Request</span>
                    </a>

                    <ul class="pcoded-submenu">

                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/new_product_request/list.php">
                                <span class="pcoded-micon"><i class="ti-list"></i></span>
                                Request List
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo BASE_URL; ?>modules/new_product_request/add.php">
                                <span class="pcoded-micon"><i class="ti-plus"></i></span>
                                Add New Request
                            </a>
                        </li>

                    </ul>
                </li>
            <?php } ?>







          <?php if (in_array($role, ['admin', 'hr', 'employee'])): ?>
<li class="pcoded-hasmenu">
    <a href="javascript:void(0)">
        <span class="pcoded-micon"><i class="fa fa-user"></i></span>
        <span class="pcoded-mtext">Clients Distribution</span>
    </a>

    <ul class="pcoded-submenu">
        
        <!-- Distribution List (only for admin and hr) -->
        <?php if (in_array($role, ['admin', 'hr', 'employee'])): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>modules/distribution/index.php">
                <span class="pcoded-micon"><i class="ti-list"></i></span>
                Distribution List
            </a>
        </li>
        <?php endif; ?>

        <!-- Single Client Distribution (only for employee) -->
        <?php if ($role === 'employee'): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>modules/distribution/add.php">
                <span class="pcoded-micon"><i class="ti-plus"></i></span>
                Single Client Distribution
            </a>
        </li>
        <?php endif; ?>

        <!-- Bulk Distribution for Multiple Clients (only for employee) -->
        <?php if ($role === 'employee'): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>modules/distribution_multi/add.php">
                <span class="pcoded-micon"><i class="ti-plus"></i></span>
                Bulk Distribution for Multiple Clients
            </a>
        </li>
        <?php endif; ?>

    </ul>
</li>
<?php endif; ?>






        </ul>
    </div>
</nav>