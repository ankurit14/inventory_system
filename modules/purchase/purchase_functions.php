<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// purchase_functions.php
include_once __DIR__ . '/../../config/db.php';

/* -------------------------
   PURCHASE MASTER / ITEMS
---------------------------*/
function get_purchase_by_id($id) {
    global $conn;
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT * FROM purchases WHERE id=$id");
    return $res ? mysqli_fetch_assoc($res) : null;
}

function get_all_purchases() {
    global $conn;
    $sql = "SELECT p.*, s.name AS supplier_name  
            FROM purchases p 
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            ORDER BY p.id DESC";
    return mysqli_query($conn, $sql);
}

function get_purchase_items($purchase_id) {
    global $conn;
    $purchase_id = intval($purchase_id);
    $sql = "SELECT pi.*, pr.name AS product_name, pr.unit 
            FROM purchase_items pi
            LEFT JOIN products pr ON pi.product_id = pr.id
            WHERE pi.purchase_id=$purchase_id";
    return mysqli_query($conn, $sql);
}

function insert_purchase($supplier_id, $date, $total, $status) {
    global $conn;
    $supplier_id = intval($supplier_id);
    $total = floatval($total);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO purchases (supplier_id, purchase_date, total_amount, status, created_at)
         VALUES (?, ?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt, "isds", $supplier_id, $date, $total, $status);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function insert_purchase_item($purchase_id, $product_id, $qty, $unit_price, $total) {
    global $conn;
    $purchase_id = intval($purchase_id);
    $product_id = intval($product_id);
    $qty = floatval($qty);
    $unit_price = floatval($unit_price);
    $total = floatval($total);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO purchase_items (purchase_id, product_id, qty, unit_price, total)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiidd", $purchase_id, $product_id, $qty, $unit_price, $total);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

function update_purchase($id, $supplier_id, $date, $total_amount, $status) {
    
//     echo "ID: " . $id . "<br>";
// echo "Supplier ID: " . $supplier_id . "<br>";
// echo "Date: " . $date . "<br>";
// echo "Total Amount: " . $total_amount . "<br>";
// echo "Status: " . $status . "<br>";


    global $conn;
    $id = intval($id);
    $supplier_id = intval($supplier_id);
    $total_amount = floatval($total_amount);

    $stmt = mysqli_prepare($conn,
        "UPDATE purchases SET supplier_id=?, purchase_date=?, total_amount=?, status=?, updated_at=NOW()
         WHERE id=?"
    );
    mysqli_stmt_bind_param($stmt, "isdsi", $supplier_id, $date, $total_amount, $status, $id);
    $res = mysqli_stmt_execute($stmt);
   
    mysqli_stmt_close($stmt);
     if(!$res){
    die("MySQL error: " . mysqli_stmt_error($stmt));
}
    return $res;
}

function delete_purchase_items($purchase_id) {
    global $conn;
    $purchase_id = intval($purchase_id);
    return mysqli_query($conn, "DELETE FROM purchase_items WHERE purchase_id=$purchase_id");
}

function delete_purchase_item($item_id) {
    global $conn;
    $item_id = intval($item_id);
    return mysqli_query($conn, "DELETE FROM purchase_items WHERE id=$item_id");
}

/* -------------------------
   PRODUCT HELPERS
---------------------------*/
function get_product_by_id($id) {
    global $conn;
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
    return $res ? mysqli_fetch_assoc($res) : null;
}

function get_product_name($id) {
    $p = get_product_by_id($id);
    return $p ? $p['name'] : '';
}

/* -------------------------
   STOCK HELPERS (stock_master)
   stock_master: product_id, stock_in, stock_out, source, ref_id, note, created_at
---------------------------*/
function get_stock_summary($product_id) {
    global $conn;
    $product_id = intval($product_id);
    $sql = "SELECT 
                IFNULL(SUM(stock_in),0) AS total_in,
                IFNULL(SUM(stock_out),0) AS total_out
            FROM stock_master
            WHERE product_id = $product_id";
    $res = mysqli_query($conn, $sql);
    return $res ? mysqli_fetch_assoc($res) : ['total_in'=>0,'total_out'=>0];
}

function get_stock_entry_for_purchase($product_id, $purchase_id) {
    global $conn;
    $product_id = intval($product_id);
    $purchase_id = intval($purchase_id);
    $sql = "SELECT * FROM stock_master 
            WHERE product_id=$product_id AND source='purchase' AND ref_id=$purchase_id
            LIMIT 1";
    $res = mysqli_query($conn, $sql);
    return $res ? mysqli_fetch_assoc($res) : null;
}

/**
 * Update or Insert stock_master entry for purchase.
 * Ensures we do not reduce stock below already issued (prevents negative).
 * Returns array: ['success'=>bool, 'message'=>'...']
 */
function update_stock_purchase_entry($product_id, $purchase_id, $new_qty) {
    global $conn;
    $product_id = intval($product_id);
    $purchase_id = intval($purchase_id);
    $new_qty = floatval($new_qty);

    // existing entry (for this purchase)
    $old_entry = get_stock_entry_for_purchase($product_id, $purchase_id);
    $old_qty = $old_entry ? floatval($old_entry['stock_in']) : 0.0;

    // overall stock summary (includes old entry)
    $sum = get_stock_summary($product_id);
    $total_in = floatval($sum['total_in']);
    $total_out = floatval($sum['total_out']);
    $current_stock = $total_in - $total_out; // includes old entry

    $diff = $new_qty - $old_qty; // positive -> increase stock, negative -> decrease

    // if reducing stock, ensure that after reduction stock >= 0
    if ($diff < 0) {
        if (($current_stock + $diff) < 0) {
            return ['success' => false, 'message' => 'Cannot reduce qty: stock already issued for this product.'];
        }
    }

    if ($old_entry) {
        // update existing stock_master record's stock_in
        $stmt = mysqli_prepare($conn, "UPDATE stock_master SET stock_in=? , note=? WHERE id=?");
        $note = "Updated by purchase edit (purchase_id:$purchase_id)";
        mysqli_stmt_bind_param($stmt, "dsi", $new_qty, $note, $old_entry['id']);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return ['success'=>$ok];
    } else {
        // insert new stock_master record for purchase
        $stmt = mysqli_prepare($conn,
            "INSERT INTO stock_master (product_id, stock_in, stock_out, source, ref_id, note, created_at)
             VALUES (?, ?, 0, 'purchase', ?, ? , NOW())"
        );
        $note = "Purchase entry (purchase_id:$purchase_id)";
        mysqli_stmt_bind_param($stmt, "idiss", $product_id, $new_qty, $purchase_id, $note);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return ['success'=>$ok];
    }
}

/**
 * Insert a stock_master record for new purchase item (shortcut)
 */
function insert_stock_for_purchase_item($product_id, $purchase_id, $qty) {
    // uses update_stock_purchase_entry which inserts if missing
    return update_stock_purchase_entry($product_id, $purchase_id, $qty);
}


/* -------------------------
   SUPPLIER HELPER
---------------------------*/
function get_supplier_by_id($id) {
    global $conn;
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT * FROM suppliers WHERE id=$id");
    return $res ? mysqli_fetch_assoc($res) : null;
}

/* -------------------------
   PURCHASE ITEM WITH PRODUCT DETAILS
---------------------------*/
function get_purchase_items_with_products($purchase_id) {
    global $conn;
    $purchase_id = intval($purchase_id);
    $sql = "SELECT pi.*, pr.name AS product_name, pr.unit
            FROM purchase_items pi
            LEFT JOIN products pr ON pi.product_id = pr.id
            WHERE pi.purchase_id=$purchase_id";
    $res = mysqli_query($conn, $sql);
    $items = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $items[] = $row;
        }
    }
    return $items;
}


// function get_purchase_by_id($id) {
//     global $conn;
//     $id = intval($id);
//     $q = "SELECT * FROM purchase WHERE id = $id";
//     $res = mysqli_query($conn, $q);
//     return mysqli_fetch_assoc($res);
// }

// function get_purchase_items($purchase_id) {
//     global $conn;
//     $purchase_id = intval($purchase_id);
//     $q = "SELECT * FROM purchase_items WHERE purchase_id = $purchase_id";
//     $res = mysqli_query($conn, $q);
//     $items = [];
//     while ($row = mysqli_fetch_assoc($res)) {
//         $items[] = $row;
//     }
//     return $items;
// }
