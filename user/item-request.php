<?php
session_start();

if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

$items = [];
$categories = [];
$division = $_SESSION['division'];

// Fetch categories
$category_query = "SELECT category_code, description FROM categories";
$category_result = $connect->query($category_query);
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch items
$item_query = "SELECT item_code, name, category_code FROM items";
$item_result = $connect->query($item_query);
while ($row = $item_result->fetch_assoc()) {
    $items[] = $row;
}

// Handle form submission
$successMsg = $errorMsg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $division = $_POST['division'];
    $category_code = $_POST['category'] ?? '';
    $item_code = $_POST['item'];
    $year = !empty($_POST['year']) ? $_POST['year'] : null;
    $justification = trim($_POST['justification']);
    $reason = $_POST['reason'];
    $unit_price = $_POST['unit_price'];
    $budget_id = $_POST['budget'] ?? null;
    $remark = $_POST['remark'] ?? null;
    $quantity = intval($_POST['quantity']);

    // Basic validation
    if (!$category_code || !$budget_id || !$item_code || !$unit_price || !$quantity || !$year || !$justification) {
        $errorMsg = "Please fill in all required fields.";
    } else {
        $stmt = $connect->prepare("INSERT INTO item_requests 
            (division, item_code, year, description, reason, unit_price, quantity, budget_id, remark)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "ssisssiss",
            $division,
            $item_code,
            $year,
            $justification,
            $reason,
            $unit_price,
            $quantity,
            $budget_id,
            $remark
        );

        if ($stmt->execute()) {
            $successMsg = "Item request successfully added!";
        } else {
            $errorMsg = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<div class="container-fluid px-4">
    <h2 class="text-center mb-4 fw-bold text-primary" style="letter-spacing: 1px;">Item Request For Budget</h2>
    <?php if ($successMsg): ?>
        <div class="alert alert-success text-center"><?= $successMsg; ?></div>
    <?php elseif ($errorMsg): ?>
        <div class="alert alert-danger text-center"><?= $errorMsg; ?></div>
    <?php endif; ?>
    <form method="POST" action="" class="row g-4 shadow-lg p-5 border border-2 border-primary rounded-4 bg-light">

        <!-- Division -->
        <div class="col-md-4">
            <label for="division" class="form-label fw-semibold">Division</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($division); ?>" readonly>
            <input type="hidden" name="division" value="<?= htmlspecialchars($division); ?>">
        </div>

        <!-- Category -->
        <div class="col-md-4">
            <label for="category" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
            <select id="category" name="category" class="form-select" required>
                <option value="" selected disabled>Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_code']; ?>">
                        <?= $category['description']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Item -->
        <div class="col-md-4">
            <label for="item" class="form-label fw-semibold">Item <span class="text-danger">*</span></label>
            <select id="item" name="item" class="form-select" required>
                <option value="" selected disabled>Select Item</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['item_code']; ?>" data-category="<?= $item['category_code']; ?>">
                        <?= $item['item_code']; ?> - <?= $item['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Year -->
        <div class="col-md-4">
            <label for="year" class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="year" name="year" value="<?= date('Y'); ?>" min="2020" max="2100" required>
        </div>

        <!-- Budget -->
        <div class="col-md-4">
            <label for="budget" class="form-label fw-semibold">Budget <span class="text-danger">*</span></label>
            <select id="budget" name="budget" class="form-select" required>
                <option value="" selected disabled>Choose Budget</option>
                <?php
                $budget_sql = "SELECT * FROM budget";
                $budget_result = $connect->query($budget_sql);
                while ($row = $budget_result->fetch_assoc()) {
                    echo "<option value='{$row['id']}' data-name='" . strtolower($row['budget']) . "'>{$row['budget']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Unit Price -->
        <div class="col-md-4">
            <label for="unit_price" class="form-label fw-semibold">Unit Price (Rs) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="unit_price" name="unit_price" step="0.01" min="0" required>
        </div>

        <!-- Quantity -->
        <div class="col-md-4">
            <label for="quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
        </div>

        <!-- Total Cost -->
        <div class="col-md-4">
            <label for="total_cost" class="form-label fw-semibold">Total Cost (Rs)</label>
            <input type="text" class="form-control" id="total_cost" name="total_cost" readonly>
        </div>

        <!-- Reason -->
        <div class="col-md-4">
            <label for="reason" class="form-label fw-semibold">New/Replace</label>
            <select id="reason" name="reason" class="form-select">
                <option value="New">New</option>
                <option value="Replace">Replace</option>
            </select>
        </div>

        <!-- Justification -->
        <div class="col-md-12">
            <label for="justification" class="form-label fw-semibold">Justification <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="justification" name="justification" required>
        </div>

        <!-- Remark -->
        <div class="col-md-12">
            <label for="remark" class="form-label fw-semibold">Remark(User ID)</label>
            <input type="text" class="form-control" id="remark" name="remark">
        </div>

        <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                <i class="bi bi-plus-circle"></i> ADD
            </button>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .container-fluid {
        max-width: 1100px;
    }
    .form-label {
        font-size: 1rem;
        font-weight: 500;
    }
    .form-select, .form-control {
        border-radius: 0.5rem;
        font-size: 1rem;
    }
    .btn-primary {
        border-radius: 0.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .shadow-lg {
        box-shadow: 0 8px 32px rgba(13,41,87,0.13) !important;
    }
    .border-primary {
        border-width: 2px !important;
    }
</style>

<?php include('includes/scripts.php'); ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const itemSelect = document.getElementById("item");
    const categorySelect = document.getElementById("category");
    const unitPriceInput = document.getElementById("unit_price");
    const quantityInput = document.getElementById("quantity");
    const totalCostInput = document.getElementById("total_cost");
    const budgetSelect = document.getElementById("budget");
    const yearInput = document.getElementById("year");

    // Filter items based on selected category
    categorySelect.addEventListener("change", () => {
        const selectedCategory = categorySelect.value;
        Array.from(itemSelect.options).forEach(option => {
            option.hidden = option.getAttribute("data-category") !== selectedCategory && option.value !== "";
        });
        itemSelect.value = ""; // Reset item selection
    });

    // Fetch unit price based on selected item
    itemSelect.addEventListener("change", () => {
        const itemCode = itemSelect.value;
        if (itemCode) {
            fetch(`get_item_price.php?item_code=${itemCode}`)
                .then(res => res.json())
                .then(data => {
                    unitPriceInput.value = data.price || '';
                    updateTotalCost();
                });
        } else {
            unitPriceInput.value = '';
            updateTotalCost();
        }
    });

    // Update year based on selected budget
    budgetSelect.addEventListener("change", () => {
        const selected = budgetSelect.options[budgetSelect.selectedIndex];
        const name = selected ? selected.getAttribute('data-name') : '';
        const currentYear = new Date().getFullYear();
        if (name && name.includes("next year")) {
            yearInput.value = currentYear + 1;
        } else if (name && name.includes("revised")) {
            yearInput.value = currentYear;
        }
    });

    // Auto-calculate total cost
    function updateTotalCost() {
        const price = parseFloat(unitPriceInput.value);
        const qty = parseInt(quantityInput.value);
        totalCostInput.value = (!isNaN(price) && !isNaN(qty)) ? (price * qty).toFixed(2) : '';
    }

    unitPriceInput.addEventListener("input", updateTotalCost);
    quantityInput.addEventListener("input", updateTotalCost);
});
</script>
