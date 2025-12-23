<?php
// expense_functions.php
include_once __DIR__ . '/../../config/db.php';

function add_voucher($data) {
    global $conn;
    
    $query = "INSERT INTO expenses (receiver_type, receiver_id, received_amt, date, payment_method, cheque_no, apv_by, voucher_image, notes) 
              VALUES (?, ?,?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sddssssss", 
        $data['receiver_type'],
        $data['receiver_id'],
        $data['received_amt'],
        $data['date'],
        $data['payment_method'],
        $data['cheque_no'],
        $data['apv_by'],
        $data['voucher_image'],
        $data['notes']
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}
?>