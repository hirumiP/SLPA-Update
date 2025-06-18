<?php
include('C:\xampp\htdocs\SLPA-Update\user\includes\dbc.php');

// Get the division passed from the AJAX request
$division = $_GET['division'];

// Prepare and execute the query to fetch items and year based on division
$sql = "SELECT DISTINCT equipment_plan.year, 
                equipment_plan.item_code, 
                items.name 
        FROM equipment_plan
        INNER JOIN items ON equipment_plan.item_code = items.item_code
        WHERE equipment_plan.division = '$division'"; 

$result = $connect->query($sql);

// Initialize arrays to store the fetched data
$items = [];
$year = null;

// Fetch items and year
while ($row = $result->fetch_assoc()) {
    $items[] = ['item_code' => $row['item_code'], 'name' => $row['name']];
    $year = $row['year']; // Year is the same for all rows, so it will overwrite with the same value
}

// Return items and year as a JSON response
echo json_encode(['items' => $items, 'year' => $year]);
?>
