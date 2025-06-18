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
        <li class="breadcrumb-item active">Items</li>
    </ol>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include('includes/filter.php'); ?>
    <br>
    <div class="table-container">
    <table class="table table-bordered text-center">
        <thead style="background-color: #003366; color: #ffffff;">
            <tr>
                <th scope="col">Item Code</th>
                <th scope="col">Category Code</th>
                <th scope="col">Name</th>
                <th scope="col">Unit Price</th>
                <th scope="col">Quantity In Hand</th>
                <th scope="col">Description</th>
                <th scope="col">Remark</th>
                <th scope="col">Created At</th>
                <th scope="col">Update</th> <!-- New Column for Update Icon -->
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM items";
            $result = mysqli_query($connect, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $item_code = $row['item_code'];
                    $category_code = $row['category_code'];
                    $name = $row['name'];
                    $unit_price = $row['unit_price'];
                    $qty_in_hand = $row['qty_in_hand'];
                    $description = $row['description'];
                    $remark = $row['remark'];
                    $created_at = $row['created_at'];
                    
                    echo '<tr>
                        <th scope="row">' . $item_code . '</th>
                        <td>' . $category_code . '</td>
                        <td>' . $name . '</td>
                        <td>' . $unit_price . '</td>
                        <td>' . $qty_in_hand . '</td>
                        <td>' . $description . '</td>
                        <td>' . $remark . '</td>
                        <td>' . $created_at . '</td>
                        <td>
                            <a href="edit_item.php?id=' . $item_code . '" class="text-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>';
                }
            } else {
                die("Query failed: " . mysqli_error($connect));
            }
            ?>
        </tbody>
    </table>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include('includes/scripts.php');
?>
