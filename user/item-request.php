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

$quantityError = "";
$categoryError = "";
$budgetError = "";

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

    // Validate category
    if (empty($category_code)) {
        $categoryError = "Please select a category.";
    }

    // Validate budget
    if (empty($budget_id) || !is_numeric($budget_id)) {
        $budgetError = "Please select a valid budget.";
    }

    // Validate quantity
    if (!isset($_POST['quantity']) || !is_numeric($_POST['quantity']) || intval($_POST['quantity']) <= 0) {
        $quantityError = "Please enter a valid quantity (greater than 0).";
    } else {
        $quantity = intval($_POST['quantity']);
    }

    // Proceed if no errors
    if (empty($quantityError) && empty($categoryError) && empty($budgetError)) {
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
            echo "<div class='alert alert-success text-center'>Item request successfully added!</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}
?>

<div class="container-fluid px-4">
    <h2 class="text-center mb-4">Item Request For Budget 2025</h2>
    <form method="POST" action="" class="row g-3 shadow-lg p-5 border border-2 border-primary rounded-3 bg-light">

        <!-- Division -->
        <div class="col-md-4">
            <label for="division" class="form-label">Division</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($division); ?>" readonly>
            <input type="hidden" name="division" value="<?= htmlspecialchars($division); ?>">
        </div>

        <!-- Category -->
        <div class="col-md-4">
            <label for="category" class="form-label">Category</label>
            <?php if (!empty($categoryError)): ?>
                <div class="text-danger mb-1"><?= $categoryError; ?></div>
            <?php endif; ?>
            <select id="category" name="category" class="form-select" required>
                <option disabled selected>Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_code']; ?>"
                        <?= (isset($_POST['category']) && $_POST['category'] === $category['category_code']) ? 'selected' : ''; ?>>
                        <?= $category['description']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Item -->
        <div class="col-md-4">
            <label for="item" class="form-label">Item</label>
            <select id="item" name="item" class="form-select" required>
                <option disabled selected>Select Item</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['item_code']; ?>" data-category="<?= $item['category_code']; ?>">
                        <?= $item['item_code']; ?> - <?= $item['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Year -->
        <div class="col-md-4">
            <label for="year" class="form-label">Year</label>
            <input type="number" class="form-control" id="year" name="year" placeholder="Enter year"
                   value="<?= date('Y'); ?>" min="2020" max="2100" required>
        </div>

        <!-- Budget -->
        <div class="col-md-4">
            <label for="budget" class="form-label">Budget</label>
            <select id="budget" name="budget" class="form-select" required>
                <option disabled selected>Choose Budget</option>
                <?php
                $budget_sql = "SELECT * FROM budget";
                $budget_result = $connect->query($budget_sql);
                while ($row = $budget_result->fetch_assoc()) {
                    echo "<option value='{$row['id']}' data-name='" . strtolower($row['budget']) . "'>{$row['budget']}</option>";
                }
                ?>
            </select>
            <?php if (!empty($budgetError)): ?>
                <div class="text-danger mb-1"><?= $budgetError; ?></div>
            <?php endif; ?>
        </div>

        <!-- Unit Price -->
        <div class="col-md-4">
            <label for="unit_price" class="form-label">Unit Price (Rs)</label>
            <input type="number" class="form-control" id="unit_price" name="unit_price" step="0.01" min="0" required>
        </div>

        <!-- Quantity -->
        <div class="col-md-4">
            <label for="quantity" class="form-label">Quantity</label>
            <?php if (!empty($quantityError)): ?>
                <div class="text-danger mb-1"><?= $quantityError; ?></div>
            <?php endif; ?>
            <input type="number" class="form-control <?= !empty($quantityError) ? 'is-invalid' : ''; ?>"
                   id="quantity" name="quantity" min="1" required>
        </div>

        <!-- Total Cost -->
        <div class="col-md-4">
            <label for="total_cost" class="form-label">Total Cost (Rs)</label>
            <input type="text" class="form-control" id="total_cost" name="total_cost" readonly>
        </div>

        <!-- Reason -->
        <div class="col-md-4">
            <label for="reason" class="form-label">Reason</label>
            <select id="reason" name="reason" class="form-select">
                <option value="New">New</option>
                <option value="Replace">Replace</option>
            </select>
        </div>

        <!-- Justification -->
        <div class="col-md-12">
            <label for="justification" class="form-label">Justification</label>
            <input type="text" class="form-control" id="justification" name="justification" required>
        </div>

        <!-- Remark -->
        <div class="col-md-12">
            <label for="remark" class="form-label">Remark</label>
            <input type="text" class="form-control" id="remark" name="remark">
        </div>

        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary px-5 py-2">ADD</button>
        </div>
    </form>
</div>

<?php include('includes/scripts.php'); ?>

<!-- ✅ JS Scripts -->
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
        }
    });

    // Update year based on selected budget
    budgetSelect.addEventListener("change", () => {
        const selected = budgetSelect.options[budgetSelect.selectedIndex];
        const name = selected.getAttribute('data-name');
        const currentYear = new Date().getFullYear();
        if (name.includes("next year")) {
            yearInput.value = currentYear + 1;
        } else if (name.includes("revised")) {
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
