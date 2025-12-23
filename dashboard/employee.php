<?php
session_start();
include_once __DIR__ . '/../config/path.php';
include(BASE_PATH.'/config/db.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit;
}
include(BASE_PATH . '/includes/header.php');
// include(BASE_PATH . '/includes/users_functions.php');
include(BASE_PATH . '/includes/sidebar.php');
?>
  <div class="pcoded-content">
 <!-- Page-header start -->
                      <div class="page-header">
                          <div class="page-block">
                              <div class="row align-items-center">
                                  <div class="col-md-8">
                                      <div class="page-header-title">
                                          <h5 class="m-b-10">Employee Dashboard</h5>
                                          <p class="m-b-0">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></p>
                                      </div>
                                  </div>
                                  <div class="col-md-4">
                                      <ul class="breadcrumb-title">
                                          <li class="breadcrumb-item">
                                              <a href="index.html"> <i class="fa fa-home"></i> </a>
                                          </li>
                                          <li class="breadcrumb-item"><a href="#!">Dashboard</a>
                                          </li>
                                      </ul>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!-- Page-header end -->




                     
                        </div>



                        <?php 
include(BASE_PATH . '/includes/footer.php');
?>