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
                <img class="img-80 img-radius" src="<?php echo BASE_URL; ?>assets/images/avatar-4.jpg" alt="User">
                <div class="user-details">
                    <span id="more-details"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>
        </div>

        <div class="pcoded-navigation-label">Layout</div>

        <ul class="pcoded-item pcoded-left-item">

            <li class="active">
                <a href="<?php echo BASE_URL; ?>index.php" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
            <li><a href="<?php echo BASE_URL; ?>modules/users/index.php"><span class="pcoded-micon"><i class="ti-user"></i></span>Users</a></li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
            <li><a href="<?php echo BASE_URL; ?>modules/category/index.php"><span class="pcoded-micon"><i class="ti-tag"></i></span>Category</a></li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
            <li><a href="<?php echo BASE_URL; ?>modules/product/index.php"><span class="pcoded-micon"><i class="ti-package"></i></span>Product</a></li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
            <li><a href="<?php echo BASE_URL; ?>modules/supplier/index.php"><span class="pcoded-micon"><i class="ti-briefcase"></i></span>Supplier</a></li>
            <?php } ?>

            <?php if ($role == 'admin' || $role == 'hr') { ?>
            <li><a href="<?php echo BASE_URL; ?>modules/stock_in/index.php"><span class="pcoded-micon"><i class="ti-import"></i></span>Stock In</a></li>
            <?php } ?>

            <li><a href="<?php echo BASE_URL; ?>modules/stock_out/index.php"><span class="pcoded-micon"><i class="ti-export"></i></span>Stock Out</a></li>

            <?php if ($role == 'hr') { ?>
            <li><a href="<?php echo BASE_URL; ?>modules/approvals/hr_approval.php"><span class="pcoded-micon"><i class="ti-check-box"></i></span>HR Approval</a></li>
            <?php } ?>

            <?php if ($role == 'admin') { ?>
            <li><a href="<?php echo BASE_URL; ?>modules/approvals/final_approval.php"><span class="pcoded-micon"><i class="ti-check"></i></span>Final Approval</a></li>
            <?php } ?>

        </ul>
    </div>
</nav>
