<?php
include(__DIR__ . '/../user/includes/dbc.php');

if (isset($_POST['approve'])) {
    $division = $_POST['division'];
    $item_name = $_POST['item_name'];
    $year = $_POST['year'];
    $unit_price = $_POST['unit_price'];
    $quantity = $_POST['quantity'];

    $query = "UPDATE item_requests 
              SET unit_price = '$unit_price', quantity = '$quantity', status = 'Approved'
              WHERE division = '$division' AND year = '$year'
              AND item_code = (SELECT item_code FROM items WHERE name = '$item_name' LIMIT 1)";

    $run = mysqli_query($connect, $query);

    if ($run) {
        header("Location: dashbord_user.php?success=approved");
        exit();
    } else {
        echo "Failed to approve.";
    }
}

if (isset($_POST['delete'])) {
    $division = $_POST['division'];
    $item_name = $_POST['item_name'];
    $year = $_POST['year'];

    $query = "DELETE FROM item_requests 
              WHERE division = '$division' AND year = '$year'
              AND item_code = (SELECT item_code FROM items WHERE name = '$item_name' LIMIT 1)";

    $run = mysqli_query($connect, $query);

    if ($run) {
        header("Location: dashbord_user.php?success=deleted");
        exit();
    } else {
        echo "Failed to delete.";
    }
}
?>
