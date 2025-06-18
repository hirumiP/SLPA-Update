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
        <li class="breadcrumb-item active">Division</li>
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
        
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <div class="table-container">
    <table class="table table-bordered text-center">
        <thead style="background-color: #003366; color: #ffffff;">
            <tr>
                <th scope="col">Division Name</th>
                <th scope="col">Division Code</th>
                <th scope="col">Division ID</th>
                <th scope="col">Remark</th> 
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM divisions";
            $result = mysqli_query($connect, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $division_name = $row['division_name'];
                    $account_code = $row['account_code'];
                    $sub_account_code = $row['sub_account_code'];
                    $remark = $row['remark'];
                   

                    echo '
                    <tr>
                        <th scope="row">' . $division_name . '</th>
                        <td>' . $account_code . '</td>
                        <td>' . $sub_account_code . '</td>
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


