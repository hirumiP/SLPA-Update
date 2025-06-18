<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}
include('includes/header.php');
include('includes/dbc.php');
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">EQ Plan</li>
    </ol>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body>
    <?php
        include('includes/filter.php');
    ?>
    <!-- view  Item -Requested-->
<br>
<div class="table-container">
    <table class="table table-bordered text-center">
        <thead style="background-color: #003366; color: #ffffff;">
            <tr>
                <th scope="col">Division</th>
                <th scope="col">Year</th>
                <th scope="col">Category Description</th> <!-- Updated header -->
                <th scope="col">Item Name</th>            <!-- Updated header -->
                <th scope="col">Available Quantity</th>
                <th scope="col">To Purchased</th>
                <th scope="col">To Condemned</th>
                <th scope="col">Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php

include(__DIR__ . '/../user/includes/dbc.php');
$sql = "SELECT 
            ep.division AS division,
            ep.year AS year,
            c.description AS description, 
            i.name AS item_name, 
            ep.available_quantity AS available_quantity,
            ep.to_purchased AS to_purchased,
            ep.to_condemned AS to_condemned,
            ep.remark AS remark
        FROM 
            equipment_plan ep
        JOIN 
            items i ON ep.item_code = i.item_code
        JOIN 
            categories c ON i.category_code = c.category_code";

            // Execute the updated SQL query
            $result = mysqli_query($connect, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $division = $row['division'];
                    $year = $row['year'];
                    $category_description = $row['description']; // Fetch category description
                    $item_name = $row['item_name'];                       // Fetch item name
                    $available_quantity = $row['available_quantity'];
                    $to_purchased = $row['to_purchased'];
                    $to_condemned = $row['to_condemned'];
                    $remark = $row['remark'];

                    echo '
                    <tr>
                        <td>' . $division . '</td>
                        <td>' . $year . '</td>
                        <td>' . $category_description . '</td> <!-- Display category description -->
                        <td>' . $item_name . '</td>           <!-- Display item name -->
                        <td>' . $available_quantity . '</td>
                        <td>' . $to_purchased . '</td>
                        <td>' . $to_condemned . '</td>
                        <td>' . $remark . '</td>
                    </tr>';
                }
            } else {
                die("Query failed: " . mysqli_error($connect));
            }
            ?>
        </tbody>
    </table>
</div>
        <br>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php
include('includes/scripts.php');
?>

