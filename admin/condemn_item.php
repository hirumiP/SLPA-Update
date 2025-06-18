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

// Fetch categories
$categories = [];
$sql = "SELECT category_code, description FROM categories";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch items
$items = [];
$sql = "SELECT item_code, name FROM items";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

// Fetch division names
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
    // Get form data
    $categoryCode = $_POST['categoryCode'];
    $itemCode = $_POST['itemCode'];
    $divisionName = $_POST['divisionName'];
    $assetNo = $_POST['assetNo'];
    $noOfItems = $_POST['noOfItems'];
    $condemnDate = $_POST['condemnDate'];
    $remark = $_POST['remark'] ?? null;

    // Insert into condemn_items table
    $sql = "INSERT INTO condemn_items 
            (category_code, item_code, division_name, asset_no, no_of_items, condemn_date, remark)
            VALUES 
            ('$categoryCode', '$itemCode', '$divisionName', '$assetNo', '$noOfItems', '$condemnDate', '$remark')";

    if ($connect->query($sql) === TRUE) {
        // Reduce items from qty_in_hand in items table
        $updateSql = "UPDATE items 
                      SET qty_in_hand = qty_in_hand - $noOfItems 
                      WHERE item_code = '$itemCode'";
        $connect->query($updateSql);

        echo "<script>alert('Condemned item added successfully');</script>";
    } else {
        echo "<script>alert('Error: " . $connect->error . "');</script>";
    }
}

// Close connection
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
        <h2>ADD CONDEMN ITEM</h2>
        <form method="POST">
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

            <label for="divisionName">Division Name*</label>
            <select id="divisionName" name="divisionName" required>
                <option value="">Select Division</option>
                <?php foreach ($divisions as $division): ?>
                    <option value="<?= $division['division_name']; ?>">
                        <?= $division['division_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="assetNo">Asset No*</label>
            <input type="text" id="assetNo" name="assetNo" placeholder="Enter asset number" required>

            <label for="noOfItems">No of Items*</label>
            <input type="number" id="noOfItems" name="noOfItems" placeholder="Enter number of items" required>

            <label for="condemnDate">Condemn Date*</label>
            <input type="date" id="condemnDate" name="condemnDate" required>

            <label for="remark">Remark</label>
            <textarea id="remark" name="remark" rows="3" placeholder="Enter any remarks"></textarea>

            <button type="submit">Add Condemn</button>
        </form>
    </div>
</div>

<?php
// Include the footer
include('includes/footer.php');
include('includes/scripts.php');
?>
