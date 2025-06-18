<?php
include('includes/dbc.php');  // Include your database connection

// Get the division passed from the AJAX request
$division = $_GET['division'];

// Prepare and execute the query to fetch items from the eq_plan table based on division
$sql = "SELECT item_code, item_name FROM eq_plan WHERE division = '$division'";

// Execute the query
$result = $connect->query($sql);

// Initialize an array to store the fetched items
$items = [];

// Fetch each item and add it to the array
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

// Return the items as a JSON response
echo json_encode($items);
?>
