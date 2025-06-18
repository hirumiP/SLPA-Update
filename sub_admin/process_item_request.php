<?php
include(__DIR__ . '/../user/includes/dbc.php');

// Approve logic using unique ID
if (isset($_POST['approve'])) {
    $request_id = $_POST['request_id']; // Unique ID from hidden input
    $unit_price = $_POST['unit_price'];
    $quantity = $_POST['quantity'];

    // Use correct column name: request_id
    $query = "UPDATE item_requests 
              SET unit_price = '$unit_price', 
                  quantity = '$quantity', 
                  status = 'Approved' 
              WHERE request_id = '$request_id'";

    $run = mysqli_query($connect, $query);  // You forgot this line earlier

    if ($run) {
        header("Location: dashbord_user.php?success=approved");
        exit();
    } else {
        echo "Failed to approve.";
    }
}

// Delete logic using unique ID
if (isset($_POST['delete'])) {
    $request_id = $_POST['request_id']; // Unique ID from hidden input

    $query = "DELETE FROM item_requests 
              WHERE request_id = '$request_id'";  // Again, corrected column name

    $run = mysqli_query($connect, $query);

    if ($run) {
        header("Location: dashbord_user.php?success=deleted");
        exit();
    } else {
        echo "Failed to delete.";
    }
}
?>
