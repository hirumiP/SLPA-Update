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
        <li class="breadcrumb-item active">Item Issued</li>
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
    <br>
<div class="table-container">
    <table class="table table-bordered text-center">
        <thead style="background-color: #003366; color: #ffffff;">
            <tr>
                <th scope="col">Budget Year</th>
                <th scope="col">Budget Flag</th>
                <th scope="col">Catagory Code</th>
                <th scope="col">Item Code</th>
                <th scope="col">Division Name</th>
                <th scope="col">Request Quantity</th>
                <th scope="col">Issued Quantity</th>
                <th scope="col">Issuing Quantity</th>
                <th scope="col">Issue Date</th>
                <th scope="col">Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Updated SQL query to join with items and budget tables
            $sql = "SELECT * FROM issued_items";
            $result = mysqli_query($connect, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $budget_year = $row['budget_year'];
                    $budget_flag = $row['budget_flag'];
                    $category_code= $row['category_code'];
                    $item_code = $row['item_code'];
                    $division_name = $row['division_name'];
                    $request_quantity = $row['request_quantity'];
                    $issued_quantity = $row['issued_quantity'];
                    $issuing_quantity = $row['issuing_quantity'];
                    $issue_date = $row['issue_date'];
                    $remark= $row['remark'];

                    echo '
                    <tr>
                        <td>' . $budget_year . '</td>
                        <td>' . $budget_flag . '</td>
                        <td>' . $category_code. '</td>
                        <td>' . $item_code . '</td>
                        <td>' . $division_name . '</td>
                        <td>' . $request_quantity . '</td>
                        <td>' . $issued_quantity . '</td>
                        <td>' . $issuing_quantity . '</td>
                        <td>' . $issue_date. '</td>
                        <td>' . $remark . '</td>
                    </tr>';
                
                }
            } else {
                die("Query failed: " . mysqli_error($connect));
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
