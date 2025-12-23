<?php
include(BASE_PATH.'/config/db.php');

/* FETCH ALL SUPPLIERS */
function get_all_suppliers() {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM suppliers ORDER BY id DESC");
}

/* GET SINGLE SUPPLIER */
function get_supplier($id) {
    global $conn;
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT * FROM suppliers WHERE id=$id");
    if(!$res) die("DB Error: ".mysqli_error($conn));
    return $res;
}

/* INSERT SUPPLIER */
function add_supplier($data) {
    global $conn;

    $name    = trim($data['name'] ?? '');
    $phone   = trim($data['phone'] ?? '');
    $email   = trim($data['email'] ?? '');
    $address = trim($data['address'] ?? '');
    $gst_no  = trim($data['gst_no'] ?? '');

    /* ---------------- BASIC VALIDATION ---------------- */
    if ($name === '') {
        return "Supplier name is required.";
    }

    if ($phone !== '' && !preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        return "Invalid phone number.";
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }

    /* ---------------- DUPLICATE RULES ---------------- */

    // 1️⃣ SAME NAME ONLY (when user submits only name)
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM suppliers WHERE LOWER(name)=LOWER(?) LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0 && $phone==='' && $email==='' && $address==='' && $gst_no==='') {
        return "Supplier with same name already exists.";
    }
    mysqli_stmt_close($stmt);

    // 2️⃣ NAME + PHONE
    if ($phone !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND phone=? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $name, $phone);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and phone already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // 3️⃣ NAME + EMAIL
    if ($email !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND LOWER(email)=LOWER(?) LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $name, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and email already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // 4️⃣ NAME + ADDRESS
    if ($address !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND LOWER(address)=LOWER(?) LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $name, $address);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and address already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // 5️⃣ NAME + GST
    if ($gst_no !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND UPPER(gst_no)=UPPER(?) LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $name, $gst_no);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and GST already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    /* ---------------- INSERT ---------------- */
    $stmt = mysqli_prepare($conn,
        "INSERT INTO suppliers (name, phone, email, address, gst_no)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssss", $name, $phone, $email, $address, $gst_no);

    if (mysqli_stmt_execute($stmt)) {
        return true;
    }

    return "Database error. Please try again.";
}



/* UPDATE SUPPLIER */
function update_supplier($id, $data) {
    global $conn;

    $id = intval($id);
    $name = mysqli_real_escape_string($conn, $data['name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $data['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $data['email'] ?? '');
    $address = mysqli_real_escape_string($conn, $data['address'] ?? '');
    $gst_no = mysqli_real_escape_string($conn, $data['gst_no'] ?? '');

    $sql = "UPDATE suppliers SET 
            name='$name',
            phone='$phone',
            email='$email',
            address='$address',
            gst_no='$gst_no'
            WHERE id=$id";

    return mysqli_query($conn, $sql);
}

/* DELETE SUPPLIER */
function delete_supplier($id) {
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "DELETE FROM suppliers WHERE id=$id");
}


function update_supplier_safe($id, $data) {
    global $conn;

    $id      = intval($id);
    $name    = trim($data['name'] ?? '');
    $phone   = trim($data['phone'] ?? '');
    $email   = trim($data['email'] ?? '');
    $address = trim($data['address'] ?? '');
    $gst_no  = trim($data['gst_no'] ?? '');

    /* ---------------- BASIC VALIDATION ---------------- */
    if ($name === '') {
        return "Supplier name is required.";
    }

    if ($phone !== '' && !preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        return "Invalid phone number.";
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }

    /* ---------------- DUPLICATE CHECKS (IGNORE OWN ID) ---------------- */

    // 1️⃣ NAME ONLY
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM suppliers 
         WHERE LOWER(name)=LOWER(?) AND id!=? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "si", $name, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0 &&
        $phone==='' && $email==='' && $address==='' && $gst_no==='') {
        return "Supplier with same name already exists.";
    }
    mysqli_stmt_close($stmt);

    // 2️⃣ NAME + PHONE
    if ($phone !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND phone=? AND id!=? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $name, $phone, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and phone already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // 3️⃣ NAME + EMAIL
    if ($email !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND LOWER(email)=LOWER(?) AND id!=? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and email already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // 4️⃣ NAME + ADDRESS
    if ($address !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND LOWER(address)=LOWER(?) AND id!=? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $name, $address, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and address already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // 5️⃣ NAME + GST
    if ($gst_no !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM suppliers 
             WHERE LOWER(name)=LOWER(?) AND UPPER(gst_no)=UPPER(?) AND id!=? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $name, $gst_no, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            return "Supplier with same name and GST already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    /* ---------------- UPDATE ---------------- */
    $stmt = mysqli_prepare($conn,
        "UPDATE suppliers 
         SET name=?, phone=?, email=?, address=?, gst_no=? 
         WHERE id=?"
    );
    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $name, $phone, $email, $address, $gst_no, $id
    );

    if (mysqli_stmt_execute($stmt)) {
        return true;
    }

    return "Database error. Please try again.";
}
