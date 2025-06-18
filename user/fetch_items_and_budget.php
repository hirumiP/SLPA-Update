<?php
include('C:\xampp\htdocs\SLPA-Update\user\includes\dbc.php');

// Check if the division is sent via AJAX
if (isset($_GET['division'])) {
    $division = $_GET['division']; // Get the division from the request

    // Fetch items for the division
    $item_sql = "SELECT item_code, name FROM equipment_plan WHERE division = '$division'";
    $item_result = $connect->query($item_sql);
    $items = [];
    while ($row = $item_result->fetch_assoc()) {
        $items[] = $row; // Add each item to the items array
    }

    // Fetch the budget for the division (replace with your logic if necessary)
    $budget_sql = "SELECT budget FROM budget WHERE id = (SELECT budget_id FROM division_budget WHERE division = '$division')";
    $budget_result = $connect->query($budget_sql);
    $budget = $budget_result->fetch_assoc()['budget'];

    // Return the data as JSON
    echo json_encode([
        'items' => $items,         // List of items
        'year' => date('Y'),       // Auto-filled current year
        'budget' => $budget        // Budget for the division
    ]);
}
?>
