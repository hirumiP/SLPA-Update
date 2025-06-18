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

// Fetch budget options
$budgetOptions = [];
$sql = "SELECT id, budget FROM budget";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $budgetOptions[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $categoryCode = $_POST['categoryCode'];
    $itemCode = $_POST['resivedcode'];
    $qtyReceived = $_POST['qtyInHand'];
    $unitPrice = $_POST['unitPrice'];
    $supplierName = $_POST['supplierName'];
    $invoiceNo = $_POST['invoiceNo'];
    $budgetYear = $_POST['budgetYear'];
    $budgetFlag = $_POST['budgetFlag']; // This now holds the name ("First Round" or "Second Round")
    $remark = $_POST['remark'] ?? null;

    // Insert into received_items table
    $sql = "INSERT INTO received_items 
            (item_code, supplier_name, invoice_no, qty_received, unit_price, budget_year, budget_flag, remark)
            VALUES 
            ('$itemCode', '$supplierName', '$invoiceNo', '$qtyReceived', '$unitPrice', '$budgetYear', '$budgetFlag', '$remark')";

    if ($connect->query($sql) === TRUE) {
        // Update quantity in hand in items table
        $updateSql = "UPDATE items 
                      SET qty_in_hand = qty_in_hand + $qtyReceived 
                      WHERE item_code = '$itemCode'";
        $connect->query($updateSql);

        echo "<script>alert('Received item added successfully');</script>";
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
        <h2>ADD RECEIVED ITEMS</h2>
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
            <label for="resivedcode">Items*</label>
                <select id="resivedcode" name="resivedcode" required>
                     <option value="">Select Item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['item_code']; ?>">
                        <?= $item['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>

            <label for="qtyInHand">Quantity*</label>
            <input type="number" id="qtyInHand" name="qtyInHand" placeholder="Enter quantity" required>

            <label for="unitPrice">Unit Price*</label>
            <input type="number" id="unitPrice" name="unitPrice" placeholder="Enter unit price" required>

            <label for="supplierName">Supplier Name*</label>
            <input type="text" id="supplierName" name="supplierName" placeholder="Enter supplier name" required>

            <label for="invoiceNo">Invoice No*</label>
            <input type="text" id="invoiceNo" name="invoiceNo" placeholder="Enter invoice number" required>

            <label for="budgetYear">Budget Year*</label>
            <input type="text" id="budgetYear" name="budgetYear" placeholder="Enter budget year" required>

            <label for="budgetFlag">Budget Flag*</label>
<select id="budgetFlag" name="budgetFlag" required>
    <option value="">Select Option</option>
    <?php foreach ($budgetOptions as $option): ?>
        <option value="<?= $option['budget']; ?>"><?= $option['budget']; ?></option>
    <?php endforeach; ?>
</select>


            <label for="remark">Remark</label>
            <textarea id="remark" name="remark" rows="3" placeholder="Enter any remarks"></textarea>

            <button type="submit">Add Received Items</button>
        </form>
    </div>
</div>

<?php
// Include the footer
include('includes/footer.php');
include('includes/scripts.php');
?>
