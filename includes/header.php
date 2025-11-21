<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['locked']) && $_SESSION['locked'] === true) {
    header("Location: " . BASE_URL . "lock-screen.php");
    exit;
}
$role = $_SESSION['role'];
$username = $_SESSION['name'] ?? 'User';
// define('BASE_URL', '/inventory_system/');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Mega Able Bootstrap Admin Template by CodedThemes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Mega Able Bootstrap admin template made using Bootstrap 4..." />
    <meta name="keywords" content="bootstrap, admin template, dashboard, responsive" />
    <meta name="author" content="codedthemes" />

    <!-- Favicon icon -->
    <!-- Favicon: simple box icon for inventory -->
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='48' height='32' x='8' y='16' fill='%23007bff' stroke='%23000000' stroke-width='2'/><line x1='8' y1='16' x2='32' y2='0' stroke='%23000000' stroke-width='2'/><line x1='56' y1='16' x2='32' y2='0' stroke='%23000000' stroke-width='2'/></svg>" type="image/svg+xml">


    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/icon/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/jquery.mCustomScrollbar.css">
    <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css">

</head>


  <body>
  <!-- Pre-loader start -->
  <div class="theme-loader">
      <div class="loader-track">
          <div class="preloader-wrapper">
              <div class="spinner-layer spinner-blue">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
              <div class="spinner-layer spinner-red">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
            
              <div class="spinner-layer spinner-yellow">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
            
              <div class="spinner-layer spinner-green">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
          </div>
      </div>
  </div>
  <!-- Pre-loader end -->
  <div id="pcoded" class="pcoded">
      <div class="pcoded-overlay-box"></div>
      <div class="pcoded-container navbar-wrapper">
          <nav class="navbar header-navbar pcoded-header">
              <div class="navbar-wrapper">
                  <div class="navbar-logo">
                      <a class="mobile-menu waves-effect waves-light" id="mobile-collapse" href="#!">
                          <i class="ti-menu"></i>
                      </a>
                      <div class="mobile-search waves-effect waves-light">
                          <div class="header-search">
                              <div class="main-search morphsearch-search">
                                  <div class="input-group">
                                      <span class="input-group-addon search-close"><i class="ti-close"></i></span>
                                      <input type="text" class="form-control" placeholder="Enter Keyword">
                                      <span class="input-group-addon search-btn"><i class="ti-search"></i></span>
                                  </div>
                              </div>
                          </div>
                      </div>
                    <a href="index.php" class="navbar-brand" style="font-weight: 600; font-size: 1.2rem; color: #ffffff; text-decoration: none;">
    Inventory System
</a>

                      <a class="mobile-options waves-effect waves-light">
                          <i class="ti-more"></i>
                      </a>
                  </div>
                
                  <div class="navbar-container container-fluid">
                      <ul class="nav-left">
                          <li>
                              <div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div>
                          </li>
                          <li class="header-search">
                              <div class="main-search morphsearch-search">
                                  <div class="input-group">
                                      <span class="input-group-addon search-close"><i class="ti-close"></i></span>
                                      <input type="text" class="form-control">
                                      <span class="input-group-addon search-btn"><i class="ti-search"></i></span>
                                  </div>
                              </div>
                          </li>
                          <li>
                              <a href="#!" onclick="javascript:toggleFullScreen()" class="waves-effect waves-light">
                                  <i class="ti-fullscreen"></i>
                              </a>
                          </li>
                      </ul>
                      <ul class="nav-right">
                         
                         <li class="user-profile header-notification">
    <a href="#!" class="waves-effect waves-light">
        <span id="more-details"><?php echo htmlspecialchars($username); ?></span>
        <i class="ti-angle-down"></i>
    </a>

    <ul class="show-notification profile-notification">

        <!-- SETTINGS → Update Password Page -->
        <li class="waves-effect waves-light">
            <a href="<?php echo BASE_URL; ?>/modules/users/user_password_update.php">
                <i class="ti-settings"></i> Change Password
            </a>
        </li>

        <!-- PROFILE → View/Edit Profile -->
        <li class="waves-effect waves-light">
            <a href="<?php echo BASE_URL; ?>/modules/users/user_profile.php">
                <i class="ti-user"></i> My Profile
            </a>
        </li>

        <!-- LOCK SCREEN (Optional) -->
        <li class="waves-effect waves-light">
            <a href="<?php echo BASE_URL; ?>/lock-screen.php">
                <i class="ti-lock"></i> Lock Screen
            </a>
        </li>

        <!-- LOGOUT -->
        <li class="waves-effect waves-light">
            <a href="<?php echo BASE_URL; ?>logout.php">
                <i class="ti-layout-sidebar-left"></i> Logout
            </a>
        </li>
    </ul>
</li>





                      </ul>
                  </div>
              </div>
          </nav>

          <div class="pcoded-main-container">

          
              <div class="pcoded-wrapper">
                  
                
                      
                  
                  
                 
                    
                    
                    
                  