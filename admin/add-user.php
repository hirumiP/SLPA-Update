<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/dbc.php'); // Assuming the database connection is handled here
include('includes/header.php');

// Initialize variables for error and success messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input data
    $employeeNo = $connect->real_escape_string($_POST['employeeNo']);
    $userName = $connect->real_escape_string($_POST['userName']);
    $division = $connect->real_escape_string($_POST['division']); // Ensure division is included
    $email = $connect->real_escape_string($_POST['email']);
    $userCategory = $connect->real_escape_string($_POST['userCategory']);
    $password = password_hash($connect->real_escape_string($_POST['password']), PASSWORD_BCRYPT); // Secure password hashing

    // Step 1: Check if employee exists in employee_details
    $employeeCheckQuery = "SELECT * FROM employee_details WHERE employee_no = '$employeeNo'";
    $employeeCheckResult = $connect->query($employeeCheckQuery);

    if ($employeeCheckResult && $employeeCheckResult->num_rows > 0) {
        // Step 2: Employee found, update the email in employee_details
        $updateEmailQuery = "UPDATE employee_details SET email = '$email' WHERE employee_no = '$employeeNo'";

        if ($connect->query($updateEmailQuery) === TRUE) {
            // Step 3: Insert new user into the 'users' table
            $sql = "INSERT INTO users (employee_ID, username, pwd, role, division)
                    VALUES ('$employeeNo', '$userName', '$password', '$userCategory', '$division')";

            if ($connect->query($sql) === TRUE) {
                $success = "User and email added successfully!";
            } else {
                $error = "Error: " . $connect->error;
            }
        } else {
            $error = "Error updating email: " . $connect->error;
        }
    } else {
        // Step 4: Employee not found
        $error = "The employee number does not exist.";
    }
}

// Handle auto-fill for employee details when the employee number is entered
if (isset($_GET['employeeNo'])) {
    $employeeNo = $_GET['employeeNo'];
    $query = "SELECT name, division FROM employee_details WHERE employee_no = '$employeeNo'";
    $result = $connect->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userName = $row['name'];
        $division = $row['division'];
    } else {
        $userName = $division = '';
    }
} else {
    $userName = $division = '';
}

// Close the database connection
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
    rel="stylesheet">
    <!-- Add Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
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

        /* Form Container */
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

        /* Form Header */
        .form-container h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Labels */
        .form-container label {
            font-size: 16px;
            color: #555;
            display: block;
            text-align: left;
            margin-bottom: 6px;
        }

        /* Inputs and Textarea */
        .form-container input,
        .form-container select {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            transition: border-color 0.3s;
        }

        .form-container input:focus,
        .form-container select:focus {
            border-color: #4b4bf4;
            box-shadow: 0 0 4px rgba(75, 75, 244, 0.2);
        }

        /* Submit Button */
        .form-container button {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            font-weight: 500;
            color: #fff;
            background-color: #4b4bf4;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .form-container button:hover {
            background-color: #3737f0;
            box-shadow: 0 4px 10px rgba(59, 59, 224, 0.2);
        }

        /* Full Page Styling */
        .main-content {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 56px);
            padding: 20px 0;
            background-color: #f8f9fc;
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

<div class="main-content">
    <div class="form-container">
        <h2>ADD NEW USER</h2>

        <!-- Display success or error messages -->
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="employeeNo">Employee No*</label>
            <input type="text" id="employeeNo" name="employeeNo" placeholder="Type here..." required
                   oninput="fetchEmployeeDetails(this.value)">

            <label for="userName">User Name (Auto Generated)</label>
            <input type="text" id="userName" name="userName" readonly value="<?= $userName ?>">

            <label for="division">Division (Auto Generated)</label>
            <input type="text" id="division" name="division" readonly value="<?= $division ?>">

            <label for="email">Email*</label>
            <input type="email" id="email" name="email" placeholder="Type here..." required>

            <label for="userCategory">User Category*</label>
            <select id="userCategory" name="userCategory" required>
                <option value="">Select Category</option>
                <option value="admin">Admin</option>
                <option value="sub_admin">Sub Admin</option>
                <option value="user">User</option>
            </select>

            <label for="password">Password*</label>
            <input type="password" id="password" name="password" placeholder="Type here..." required>

            <button type="submit">Add User</button>
        </form>
    </div>
</div>

<script>
    function fetchEmployeeDetails(employeeNo) {
        if (employeeNo.length > 0) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_employee_details.php?employeeNo=' + employeeNo, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.name && data.division) {
                        document.getElementById('userName').value = data.name;
                        document.getElementById('division').value = data.division;
                    } else {
                        document.getElementById('userName').value = '';
                        document.getElementById('division').value = '';
                    }
                }
            };
            xhr.send();
        }
    }
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
