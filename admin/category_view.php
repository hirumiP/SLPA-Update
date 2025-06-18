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
        <li class="breadcrumb-item active">Category</li>
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
                <th scope="col">Category Code</th>
                <th scope="col">Description</th>
                <th scope="col">Remark</th>
                <th scope="col">Created At</th>    
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM categories";
            $result = mysqli_query($connect, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $category_code = $row['category_code'];
                    $description = $row['description'];
                    $remark = $row['remark'];
                    $created_at = $row['created_at'];

                    echo '
                    <tr>
                        <td>' . $category_code . '</td>
                        <td>' . $description . '</td>
                        <td>' . $remark . '</td>
                        <td>' . $created_at . '</td>
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
