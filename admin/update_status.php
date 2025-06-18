<?php
include('includes/dbc.php');

// Check if `employee_ID` and `status` are provided
if (isset($_GET['employee_ID']) && isset($_GET['status'])) {
    $employee_ID = mysqli_real_escape_string($connect, $_GET['employee_ID']);
    $status = intval($_GET['status']); // 1 for activate, 0 for deactivate

    // Update the user's status
    $sql = "UPDATE users SET status = $status WHERE employee_ID = '$employee_ID'";
    if (mysqli_query($connect, $sql)) {
        header("Location: view_user.php?success=Status updated successfully");
        exit();
    } else {
        die("Error updating status: " . mysqli_error($connect));
    }
} else {
    header("Location: view_user.php?error=Invalid parameters");
    exit();
}
