<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}
include('includes/header.php');
include('includes/dbc.php'); // Database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
        }
        .custom-btn-group {
            display: flex;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            border: none;
            border-radius: 0;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 500;
            color: white;
            transition: background-color 0.3s ease;
        }
        .btn-add-user {
            background-color: #1c1b44;
        }
        .btn-view-user {
            background-color: #5252f8;
        }
        .btn-custom:hover {
            opacity: 0.9;
        }
        .form-container {
            background: #ffffff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 1200px;
            margin: 50px auto;
            text-align: center;
            min-height: 600px;
        }
        .btn-activate, .btn-deactivate {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin: 2px;
            display: inline-block;
        }
        .btn-activate {
            background-color: #28a745;
            color: white;
        }
        .btn-deactivate {
            background-color: #dc3545;
            color: white;
        }
        .btn-activate:hover {
            background-color: #218838;
        }
        .btn-deactivate:hover {
            background-color: #c82333;
        }
        .table-container {
            margin: 30px auto;
            width: 95%;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 20px;
        }
        .table thead {
            background-color: #003366;
            color: #ffffff;
        }
        .table th, .table td {
            vertical-align: middle !important;
        }
    </style>
</head>
<body>
<div class="d-flex justify-content-center mt-5">
    <div class="custom-btn-group">
        <a href="add-user.php" class="btn btn-custom btn-add-user">
            <i class="bi bi-person-plus"></i> Add User
        </a>
        <a href="view_user.php" class="btn btn-custom btn-view-user">
            <i class="bi bi-people"></i> View Users
        </a>
    </div>
</div>
<br>

<div class="table-container">
    <h2 class="mb-4" style="color:#003366;">User Management</h2>
    <table class="table table-bordered table-hover text-center align-middle">
        <thead>
            <tr>
                <th scope="col">Employee ID</th>
                <th scope="col">Username</th>
                <th scope="col">Role</th>
                <th scope="col">Division</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM users";
            $result = mysqli_query($connect, $sql);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $employee_ID = htmlspecialchars($row['employee_ID']);
                    $username = htmlspecialchars($row['username']);
                    $role = ucfirst(htmlspecialchars($row['role']));
                    $division = ucfirst(htmlspecialchars($row['division']));
                    $status = $row['status'] ? 'Active' : 'Inactive';

                    echo '
                    <tr>
                        <td>' . $employee_ID . '</td>
                        <td>' . $username . '</td>
                        <td>' . $role . '</td>
                        <td>' . $division . '</td>
                        <td>
                            <span class="badge ' . ($row['status'] ? 'bg-success' : 'bg-secondary') . '">' . $status . '</span>
                        </td>
                        <td>
                            <a href="update_status.php?employee_ID=' . $employee_ID . '&status=1" class="btn-activate" title="Activate"><i class="bi bi-check-circle"></i> Activate</a>
                            <a href="update_status.php?employee_ID=' . $employee_ID . '&status=0" class="btn-deactivate" title="Deactivate"><i class="bi bi-x-circle"></i> Deactivate</a>
                        </td>
                    </tr>';
                }
            } else {
                echo '<tr><td colspan="6" class="text-danger">Query failed: ' . htmlspecialchars(mysqli_error($connect)) . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
