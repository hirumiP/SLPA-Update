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
    $categoryCode = trim($_POST['categoryCode']);
    $itemCode = trim($_POST['itemCode']);
    $name = trim($_POST['name']);
    $unitPrice = floatval($_POST['unitPrice']);
    $qtyInHand = intval($_POST['qtyInHand']);
    $description = trim($_POST['description'] ?? '');
    $remark = trim($_POST['remark'] ?? '');

    // Validate required fields
    if (empty($categoryCode) || empty($itemCode) || empty($name) || $unitPrice <= 0 || $qtyInHand < 0) {
        echo "<script>alert('Please fill in all required fields with valid values.');</script>";
    } else {
        // Check for duplicate item_code using prepared statement
        $check_stmt = $connect->prepare("SELECT item_code FROM items WHERE item_code = ?");
        $check_stmt->bind_param("s", $itemCode);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $itemCodeError = "Item Code already exists. Please use a different code.";
        } else {
            // Insert new item using prepared statement
            $insert_stmt = $connect->prepare("INSERT INTO items (item_code, category_code, name, unit_price, qty_in_hand, description, remark) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssdiis", $itemCode, $categoryCode, $name, $unitPrice, $qtyInHand, $description, $remark);

            if ($insert_stmt->execute()) {
                echo "<script>
                    alert('Item added successfully!');
                    window.location.href='add-item.php';
                </script>";
                exit();
            } else {
                echo "<script>alert('Error adding item: " . addslashes($insert_stmt->error) . "');</script>";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
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
    box-sizing: border-box;
}

.form-container input:focus,
.form-container textarea:focus,
.form-container select:focus {
    border-color: #4b4bf4;
    box-shadow: 0 0 4px rgba(75, 75, 244, 0.2);
    outline: none;
}

.form-container textarea {
    resize: vertical;
    min-height: 120px;
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

.form-container button:active {
    transform: translateY(1px);
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
    color: #dc3545;
    text-align: left;
    margin-top: -15px;
    margin-bottom: 15px;
    font-size: 14px;
    font-weight: 500;
}

.success-text {
    color: #28a745;
    text-align: center;
    margin-bottom: 20px;
    font-size: 16px;
    font-weight: 500;
}

/* Form validation styling */
.form-container input:invalid,
.form-container select:invalid {
    border-color: #dc3545;
}

.form-container input:valid,
.form-container select:valid {
    border-color: #28a745;
}

@media (max-width: 768px) {
    .form-container {
        width: 95%;
        padding: 20px;
        margin: 20px auto;
    }
    
    .form-container h2 {
        font-size: 20px;
    }
    
    .form-container input,
    .form-container textarea,
    .form-container select,
    .form-container button {
        font-size: 16px;
    }
}
</style>

<!-- Form HTML -->
<div class="main-content">
    <div class="form-container">
        <h2>ADD NEW ITEM</h2>
        <form method="POST" novalidate>
            <label for="categoryCode">Category Code*</label>
            <select id="categoryCode" name="categoryCode" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['category_code']); ?>" 
                            <?= ($categoryCode == $category['category_code']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_code']); ?> - <?= htmlspecialchars($category['description']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="itemCode">Item Code*</label>
            <input type="text" 
                   id="itemCode" 
                   name="itemCode" 
                   value="<?= htmlspecialchars($itemCode) ?>" 
                   placeholder="Enter unique item code..." 
                   maxlength="50"
                   required>
            <?php if (!empty($itemCodeError)): ?>
                <div class="error-text"><?= htmlspecialchars($itemCodeError) ?></div>
            <?php endif; ?>

            <label for="name">Item Name*</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="<?= htmlspecialchars($name) ?>" 
                   placeholder="Enter item name..." 
                   maxlength="255"
                   required>

            <label for="unitPrice">Unit Price (LKR)*</label>
            <input type="number" 
                   id="unitPrice" 
                   name="unitPrice" 
                   value="<?= htmlspecialchars($unitPrice) ?>" 
                   placeholder="Enter unit price..." 
                   min="0.01" 
                   step="0.01"
                   required>

            <label for="qtyInHand">Quantity in Hand*</label>
            <input type="number" 
                   id="qtyInHand" 
                   name="qtyInHand" 
                   value="<?= htmlspecialchars($qtyInHand) ?>" 
                   placeholder="Enter current quantity..." 
                   min="0"
                   required>

            <label for="description">Description</label>
            <textarea id="description" 
                      name="description" 
                      rows="3" 
                      maxlength="500"
                      placeholder="Enter item description (optional)..."><?= htmlspecialchars($description) ?></textarea>

            <label for="remark">Remarks</label>
            <textarea id="remark" 
                      name="remark" 
                      rows="3" 
                      maxlength="500"
                      placeholder="Enter any remarks (optional)..."><?= htmlspecialchars($remark) ?></textarea>

            <button type="submit">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </form>
    </div>
</div>

<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const itemCode = document.getElementById('itemCode');
    const unitPrice = document.getElementById('unitPrice');
    
    // Convert item code to uppercase
    itemCode.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Format unit price
    unitPrice.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#dc3545';
                isValid = false;
            } else {
                field.style.borderColor = '#28a745';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>

<?php include('includes/footer.php'); include('includes/scripts.php'); ?>
