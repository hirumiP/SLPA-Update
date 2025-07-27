<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}
// Include the database connection
include('includes/dbc.php');
include('includes/header.php');
// Initialize variables for error and success messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input data
    $divisionName = $connect->real_escape_string($_POST['divisionName']);
    $accountCode = $connect->real_escape_string($_POST['accountCode']);
    $subAccountCode = $connect->real_escape_string($_POST['subAccountCode']);
    $remark = isset($_POST['remark']) ? $connect->real_escape_string($_POST['remark']) : null;

    // Validate uniqueness of account_code and sub_account_code
    $uniqueCheckQuery = "SELECT * FROM divisions WHERE account_code = '$accountCode' OR sub_account_code = '$subAccountCode'";
    $result = $connect->query($uniqueCheckQuery);

    if ($result->num_rows > 0) {
        $error = "Error: Account Code or Sub Account Code already exists!";
    } else {
        // Insert data into the database
        $sql = "INSERT INTO divisions (division_name, account_code, sub_account_code, remark)
                VALUES ('$divisionName', '$accountCode', '$subAccountCode', '$remark')";

        if ($connect->query($sql) === TRUE) {
            $success = "Division added successfully!";
        } else {
            $error = "Error: " . $connect->error;
        }
    }
}

// Close the database connection
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Division</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fc;
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
        .form-container textarea {
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
        .form-container textarea:focus {
            border-color: #4b4bf4;
            box-shadow: 0 0 4px rgba(75, 75, 244, 0.2);
        }

        /* Textarea */
        .form-container textarea {
            resize: none;
            height: 120px;
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

        /* Alert Messages */
        .alert {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 6px;
            font-size: 16px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-container {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <h2>ADD NEW DIVISION</h2>

            <!-- Display success or error messages -->
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="divisionName">Division Name*</label>
                <input type="text" id="divisionName" name="divisionName" placeholder="Type here..." required>

                <label for="accountCode">Account Code*</label>
                <input type="text" id="accountCode" name="accountCode" placeholder="Type here..." required>

                <label for="subAccountCode">Sub Account Code*</label>
                <input type="text" id="subAccountCode" name="subAccountCode" placeholder="Type here..." required>

                <label for="remark">Remark</label>
                <textarea id="remark" name="remark" rows="7" placeholder="Type here..."></textarea>

                <button type="submit">Add Division</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>