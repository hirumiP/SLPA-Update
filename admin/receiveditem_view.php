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
        <li class="breadcrumb-item active">Recieved Item</li>
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
    <div class="table-container">
    <table class="table table-bordered text-center">
        <thead style="background-color: #003366; color: #ffffff;">
            <tr>
                <th scope="col">Received ID</th>
                <th scope="col">Item Code</th>
                <th scope="col">Supplier Name</th>
                <th scope="col">Invoice No</th>
                <th scope="col">Quantity Received</th>
                <th scope="col">Unit Price</th>
                <th scope="col">Budget Year</th>
                <th scope="col">Budget Flag</th>
                <th scope="col">Remark</th>
                <th scope="col">Received Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM received_items";
            $result = mysqli_query($connect, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $received_id = $row['received_id'];
                    $item_code = $row['item_code'];
                    $supplier_name = $row['supplier_name'];
                    $invoice_no = $row['invoice_no'];
                    $qty_received = $row['qty_received'];
                    $unit_price = $row['unit_price'];
                    $budget_year = $row['budget_year'];
                    $budget_flag = $row['budget_flag'];
                    $remark = $row['remark'];
                    $received_date = $row['received_date'];

                    echo '
                    <tr>
                        <th scope="row">' . $received_id . '</th>
                        <td>' . $item_code . '</td>
                        <td>' . $supplier_name . '</td>
                        <td>' . $invoice_no . '</td>
                        <td>' . $qty_received . '</td>
                        <td>' . $unit_price . '</td>
                        <td>' . $budget_year. '</td>
                        <td>' . $budget_flag . '</td>
                        <td>' . $remark . '</td>
                        <td>' . $received_date . '</td>
                    </tr>';
                
                }
            } else {
                die("Query failed: " . mysqli_error($connect));
            }
            ?>
        </tbody>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>


