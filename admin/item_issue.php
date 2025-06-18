<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

// Include the header
include('includes/header.php');

// Database connection
include('includes/dbc.php');

// Fetch budgets from the `budget` table
$budgets = [];
$sql = "SELECT id, budget FROM budget";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $budgets[] = $row;
    }
}

// Fetch categories from the database
$categories = [];
$sql = "SELECT category_code, description FROM categories";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch items from the database
$items = [];
$sql = "SELECT item_code, name FROM items";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

// Fetch divisions from the database
$divisions = [];
$sql = "SELECT division_name FROM divisions";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $divisions[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $budgetYear = $_POST['budgetYear'];
    $budgetFlag = $_POST['budgetFlag']; // Now the selected budget ID
    $categoryCode = $_POST['categoryCode'];
    $itemCode = $_POST['itemCode'];
    $divisionName = $_POST['divisionCode'];
    $requestQuantity = $_POST['requestQuantity'];
    $issuedQuantity = $_POST['issuedQuantity'];
    $issuingQuantity = $_POST['issuingQuantity'];
    $issueDate = $_POST['issueDate'];
    $remark = $_POST['remark'] ?? null;

    $sql = "INSERT INTO issued_items 
            (budget_year, budget_flag, category_code, item_code, division_name, request_quantity, issued_quantity, issuing_quantity, issue_date, remark)
            VALUES 
            ('$budgetYear', '$budgetFlag', '$categoryCode', '$itemCode', '$divisionName', '$requestQuantity', '$issuedQuantity', '$issuingQuantity', '$issueDate', '$remark')";

    if ($connect->query($sql) === TRUE) {
        echo "<script>alert('Item issued successfully');</script>";
    } else {
        echo "<script>alert('Error: " . $connect->error . "');</script>";
    }
}

$connect->close();
?>

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
.form-container textarea,
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
.form-container textarea:focus,
.form-container select:focus {
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

<div class="main-content">
    <div class="form-container">
        <h2>ADD ISSUED ITEMS</h2>
        <form method="POST">
            <label for="budgetYear">Budget Year*</label>
            <input type="text" id="budgetYear" name="budgetYear" placeholder="Enter budget year" required>

            <label for="budgetFlag">Budget*</label>
            <select id="budgetFlag" name="budgetFlag" required>
                <option value="">Select Budget</option>
                <?php foreach ($budgets as $budget): ?>
                    <option value="<?= $budget['id']; ?>">
                        <?= $budget['budget']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="categoryCode">Category*</label>
            <select id="categoryCode" name="categoryCode" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_code']; ?>">
                        <?= $category['description']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="itemCode">Item*</label>
            <select id="itemCode" name="itemCode" required>
                <option value="">Select Item</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['item_code']; ?>">
                        <?= $item['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="divisionCode">Division Name*</label>
            <select id="divisionCode" name="divisionCode" required>
                <option value="">Select Division</option>
                <?php foreach ($divisions as $division): ?>
                    <option value="<?= $division['division_name']; ?>">
                        <?= $division['division_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="requestQuantity">Request Quantity*</label>
            <input type="number" id="requestQuantity" name="requestQuantity" placeholder="Enter requested quantity" required>

            <label for="issuedQuantity">Issued Quantity*</label>
            <input type="number" id="issuedQuantity" name="issuedQuantity" placeholder="Enter issued quantity" required>

            <label for="issuingQuantity">Issuing Quantity*</label>
            <input type="number" id="issuingQuantity" name="issuingQuantity" placeholder="Enter issuing quantity" required>

            <label for="issueDate">Issue Date*</label>
            <input type="date" id="issueDate" name="issueDate" required>

            <label for="remark">Remark</label>
            <textarea id="remark" name="remark" rows="4" placeholder="Add any remark"></textarea>

            <button type="submit">Add Issued Item</button>
        </form>
    </div>
</div>
