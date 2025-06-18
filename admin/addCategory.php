<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}
// Include database connection
include('includes/dbc.php');

include('includes/header.php');
if (!isset($connect)) {
    die('Database connection not found. Please check dbc.php.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Category</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
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

        /* Error and Success Messages */
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 6px;
            font-size: 16px;
            text-align: left;
        }

        .message.error {
            background-color: #ffe6e6;
            color: #d9534f;
        }

        .message.success {
            background-color: #e6ffe6;
            color: #5cb85c;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>ADD NEW CATEGORY</h2>
    <?php
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $categoryCode = trim($_POST['categoryCode']);
        $description = trim($_POST['description']);
        $remark = trim($_POST['remark']);

        // Validate inputs
        $errors = [];
        if (empty($categoryCode)) {
            $errors[] = 'Category Code is required.';
        }
        if (empty($description)) {
            $errors[] = 'Description is required.';
        }
        if (empty($remark)) {
            $errors[] = 'Remark is required.';
        }

        if (empty($errors)) {
            // Insert into the database
            try {
                $sql = "INSERT INTO categories(category_code, description, remark) VALUES (?, ?, ?)";
                $stmt = $connect ->prepare($sql);
                $stmt->bind_param("sss", $categoryCode, $description, $remark);

                if ($stmt->execute()) {
                    echo '<div class="message success">Category added successfully!</div>';
                } else {
                    throw new Exception('Error adding category: ' . $stmt->error);
                }

                $stmt->close();
            } catch (Exception $e) {
                echo '<div class="message error">' . $e->getMessage() . '</div>';
            }
        } else {
            echo '<div class="message error">';
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
            echo '</div>';
        }
    }
    ?>
    <form method="POST">
        <label for="categoryCode">Category Code*</label>
        <input type="text" id="categoryCode" name="categoryCode" placeholder="Type here..." required>

        <label for="description">Description*</label>
        <textarea id="description" name="description" rows="7" placeholder="Type here..." required></textarea>

        <label for="remark">Remark*</label>
        <textarea id="remark" name="remark" rows="7" placeholder="Type here..." required></textarea>

        <button type="submit">Add Category</button>
    </form>
</div>


</body>
</html>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>