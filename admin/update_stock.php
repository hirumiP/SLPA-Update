<?php
include('includes/dbc.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id']) && isset($_POST['new_stock'])) {
    $item_id = $_POST['item_id'];  // Item code from hidden input
    $new_stock = $_POST['new_stock'];  // New stock quantity from the form

    // Update the stock quantity in the database
    $query = "UPDATE items SET qty_in_hand = ? WHERE item_code = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "is", $new_stock, $item_id);
    $result = mysqli_stmt_execute($stmt);
    
    // Close statement
    mysqli_stmt_close($stmt);
    
    if ($result) {
        // Redirect to item view page with updated stock
        header("Location: item_view.php?id=" . urlencode($item_id));
        exit();
    } else {
        die("Error: Unable to update stock.");
    }
} else {
    die("Invalid request.");
}
?>
