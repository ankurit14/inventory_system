<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $_SESSION['locked'] = true;
    header("Location: lock-screen.php");
    exit;
}

header("Location: login.php");
exit;
?>
