<?php
include('C:\xampp\htdocs\SLPA-Update\user\includes\dbc.php');

// Get the item_code passed from the AJAX request
$item_code = $_GET['item_code'];

// Prepare and execute the query to fetch item details based on item_code
$sql = "SELECT 
            items.name,  
            categories.description AS category_description, 
            items.qty_in_hand, 
            items.unit_price
        FROM items
        LEFT JOIN categories ON items.category_code = categories.category_code
        WHERE items.item_code = '$item_code'";  // Filter by item code

$result = $connect->query($sql);

// Initialize an array to store the fetched item details
$item_details = [];

// Fetch the item details and add them to the array
if ($row = $result->fetch_assoc()) {
    $item_details = $row;
}

// Return the item details as a JSON response
echo json_encode($item_details);
?>
