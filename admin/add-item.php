<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

// Include the header and DB connection
include('includes/header.php');
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

// Initialize variables
$itemCodeError = "";
$categoryCode = $itemCode = $name = $unitPrice = $qtyInHand = $description = $remark = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryCode = $_POST['categoryCode'];
    $itemCode = $_POST['itemCode'];
    $name = $_POST['name'];
    $unitPrice = $_POST['unitPrice'];
    $qtyInHand = $_POST['qtyInHand'];
    $description = $_POST['description'] ?? null;
    $remark = $_POST['remark'] ?? null;

    // Check for duplicate item_code
    $check_sql = "SELECT item_code FROM items WHERE item_code = '$itemCode'";
    $check_result = $connect->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        $itemCodeError = "Item Code already exists. Please use a different code.";
    } else {
        $sql = "INSERT INTO items (item_code, category_code, name, unit_price, qty_in_hand, description, remark)
                VALUES ('$itemCode', '$categoryCode', '$name', '$unitPrice', '$qtyInHand', '$description', '$remark')";

        if ($connect->query($sql) === TRUE) {
            echo "<script>alert('Item added successfully'); window.location.href='add-item.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error: " . $connect->error . "');</script>";
        }
    }
}

$connect->close();
?>

<!-- Styles -->
<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fc;
    margin: 0;
    padding: 0;
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

.form-container h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.form-container label {
    font-size: 16px;
    color: #555;
    display: block;
    text-align: left;
    margin-bottom: 6px;
}

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

.form-container textarea {
    resize: none;
    height: 120px;
}

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

.main-content {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: calc(100vh - 56px);
    padding: 20px 0;
    background-color: #f8f9fc;
}

.error-text {
    color: red;
    text-align: left;
    margin-top: -15px;
    margin-bottom: 15px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-container {
        width: 95%;
        padding: 20px;
    }
}
</style>

<!-- Form HTML -->
<div class="main-content">
    <div class="form-container">
        <h2>ADD NEW ITEM</h2>
        <form method="POST">
            <label for="categoryCode">Category Code*</label>
            <select id="categoryCode" name="categoryCode" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_code']; ?>" <?= ($categoryCode == $category['category_code']) ? 'selected' : '' ?>>
                        <?= $category['category_code']; ?> - <?= $category['description']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="itemCode">Item Code*</label>
            <input type="text" id="itemCode" name="itemCode" value="<?= htmlspecialchars($itemCode) ?>" placeholder="Type here..." required>
            <?php if (!empty($itemCodeError)): ?>
                <div class="error-text"><?= $itemCodeError ?></div>
            <?php endif; ?>

            <label for="name">Name*</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Type here..." required>

            <label for="unitPrice">Unit Price*</label>
            <input type="number" id="unitPrice" name="unitPrice" value="<?= htmlspecialchars($unitPrice) ?>" placeholder="Type here..." required>

            <label for="qtyInHand">QTY in Hand*</label>
            <input type="number" id="qtyInHand" name="qtyInHand" value="<?= htmlspecialchars($qtyInHand) ?>" placeholder="Type here..." required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Type here..."><?= htmlspecialchars($description) ?></textarea>

            <label for="remark">Remark</label>
            <textarea id="remark" name="remark" rows="3" placeholder="Type here..."><?= htmlspecialchars($remark) ?></textarea>

            <button type="submit">Add Item</button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); include('includes/scripts.php'); ?>
