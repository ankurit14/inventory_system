<?php function get_all_others()
{
    global $conn;
    $q = "SELECT * FROM other_receivers ORDER BY name ASC";
    $res = mysqli_query($conn, $q);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    return $data;
}
?>
