<?php
include('E:\xamp\htdocs\SLPA-Update\user\includes\dbc.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];

    // Update status in the database
    $sql = "UPDATE item_requests SET status='$status' WHERE id=$request_id";
    if ($connect->query($sql) === TRUE) {
        echo "<div class='alert alert-success text-center'>Status updated successfully!</div>";
        header("Location: subadmin_view_requests.php"); // Redirect back to the sub-admin view
    } else {
        echo "<div class='alert alert-danger text-center'>Error: " . $sql . "<br>" . $connect->error . "</div>";
    }
}
?>
