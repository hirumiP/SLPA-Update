<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include(__DIR__ . '/../user/includes/dbc.php');

// Check if 'id' is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input to avoid SQL injection

    // Prepare the SQL statement to delete the equipment request
    if ($stmt = $connect->prepare("DELETE FROM equipment_plan WHERE id = ?")) {
        $stmt->bind_param("i", $id); // Bind the id parameter as an integer

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to the equipment plan page with a success message
            header("Location: eq_plan.php?status=deleted");
        } else {
            // Redirect to the equipment plan page with an error message
            header("Location: eq_plan.php?status=error");
        }
        $stmt->close(); // Close the prepared statement
    } else {
        header("Location: eq_plan.php?status=error");
    }
} else {
    // If no ID is provided, redirect to the equipment plan page
    header("Location: eq_plan.php");
    exit();
}
?>
