<?php

include('includes/dbc.php'); // Database connection

if (isset($_GET['employeeNo'])) {
    $employeeNo = $connect->real_escape_string($_GET['employeeNo']);

    $query = "SELECT name, division FROM employee_details WHERE employee_no = '$employeeNo'";
    $result = $connect->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['name' => $row['name'], 'division' => $row['division']]);
    } else {
        echo json_encode(['name' => '', 'division' => '']);
    }
}

$connect->close();
?>
