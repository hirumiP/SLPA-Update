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
        <li class="breadcrumb-item active">Condemned Item</li>
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
                <th scope="col">Condemn Item</th>
                <th scope="col">Category Code</th>
                <th scope="col">Item Code</th>
                <th scope="col">Division Name</th>
                <th scope="col">Asset No</th>
                <th scope="col">No Of Item</th>
                <th scope="col">Condemn Date</th>
                <th scope="col">Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM condemn_items";
            $result = mysqli_query($connect, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $condemn_id = $row['condemn_id'];
                    $category_code = $row['category_code'];
                    $item_code = $row['item_code'];
                    $division_name = $row['division_name'];
                    $asset_no = $row['asset_no'];
                    $no_of_items = $row['no_of_items'];
                    $condemn_date = $row['condemn_date'];
                    $remark = $row['remark'];
                    echo '
                    <tr>
                        <th scope="row">' . $condemn_id . '</th>
                        <td>' . $category_code . '</td>
                        <td>' . $item_code . '</td>
                        <td>' . $division_name . '</td>
                        <td>' . $asset_no . '</td>
                        <td>' . $no_of_items . '</td>
                        <td>' . $condemn_date . '</td>
                        <td>' . $remark . '</td>
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

<?php
include('includes/scripts.php');
?>