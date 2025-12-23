<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

$action  = $_POST['action'] ?? '';
$id      = $_POST['id'] ?? null;
$name    = trim($_POST['name'] ?? '');
$mobile  = trim($_POST['mobile'] ?? '');
$address = trim($_POST['address'] ?? '');
$status  = $_POST['status'] ?? '';

$error = '';
$success = '';

// -----------------------
// Basic Validation
// -----------------------
if ($name === '') {
    $error = "Name is required.";
} elseif (!preg_match('/^[A-Za-z ]+$/', $name)) {
    $error = "Name must contain only alphabets.";
}

if ($mobile !== '' && !preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
    $error = "Mobile must be 10 digits and start with 6,7,8,9.";
}

if ($status === '') {
    $error = "Please select status.";
}

// -----------------------
// Duplicate Check
// -----------------------
if ($error === '') {
    $duplicateFound = false;

    if ($action === 'add') {
        $sql = "SELECT * FROM other_receivers";
        $result = mysqli_query($conn, $sql);
    } elseif ($action === 'edit' && $id) {
        $sql = "SELECT * FROM other_receivers WHERE id != ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $existingName    = strtolower(trim($row['name']));
        $existingMobile  = trim($row['mobile']);
        $existingAddress = strtolower(trim($row['address']));

        $currentName    = strtolower($name);
        $currentMobile  = $mobile;
        $currentAddress = strtolower($address);

        // -----------------------
        // Exact Match Conditions Only
        // -----------------------
        if (
            ($currentName === $existingName && $currentMobile === $existingMobile && $currentAddress === $existingAddress) || // All same
            ($currentName === $existingName && $currentMobile === $existingMobile && $currentAddress === '') || // Name+Mobile exact
            ($currentName === $existingName && $currentAddress === $existingAddress && $currentMobile === '') || // Name+Address exact
            ($currentMobile !== '' && $currentMobile === $existingMobile && $currentAddress !== '' && $currentAddress === $existingAddress) // Mobile+Address exact
        ) {
            $duplicateFound = true;
            break;
        }
    }

    if ($action === 'edit' && isset($stmt)) {
        mysqli_stmt_close($stmt);
    }

    if ($duplicateFound) {
        $error = "Receiver with same combination already exists!";
    } else {
        if ($action === 'add') {
            $stmt = mysqli_prepare($conn, "INSERT INTO other_receivers (name, mobile, address, status) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $mobile, $address, $status);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Receiver added successfully!";
            } else {
                $error = "Database error. Please try again.";
            }
            mysqli_stmt_close($stmt);
        } elseif ($action === 'edit' && $id) {
            $stmt = mysqli_prepare($conn, "UPDATE other_receivers SET name=?, mobile=?, address=?, status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $mobile, $address, $status, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Receiver updated successfully!";
            } else {
                $error = "Database error. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// -----------------------
// Redirect with messages
// -----------------------
$_SESSION['error'] = $error;
$_SESSION['success'] = $success;
header('Location: add.php');
exit;
