<?php
session_start();

if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

$division = $_SESSION['division'];
$quantityError = "";

// Fetch categories
$categories = [];
$category_query = "SELECT category_code, description FROM categories";
$category_result = $connect->query($category_query);
if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch budgets
$budgets = [];
$budget_query = "SELECT * FROM budget";
$budget_result = $connect->query($budget_query);
if ($budget_result->num_rows > 0) {
    while ($row = $budget_result->fetch_assoc()) {
        $budgets[] = $row;
    }
}

// Process form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $division = $_POST['division'];
    $item_code = $_POST['item'];
    $year = !empty($_POST['year']) ? $_POST['year'] : null;
    $justification = trim($_POST['justification']);
    $reason = $_POST['reason'];
    $unit_price = $_POST['unit_price'];
    $quantity = $_POST['quantity'];
    $budget_id = $_POST['budget'];
    $remark = $_POST['remark'];

    if (empty($justification)) {
        echo "<div class='alert alert-danger text-center'>Justification is required.</div>";
    } elseif (!is_numeric($quantity) || intval($quantity) <= 0) {
        $quantityError = "Please enter a valid quantity.";
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
    <form method="POST" class="row g-3 shadow-lg p-5 border border-2 border-primary rounded-3 bg-light">

        <!-- Division -->
        <div class="col-md-4">
            <label for="division" class="form-label">Division</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($division); ?>" readonly>
            <input type="hidden" name="division" value="<?= htmlspecialchars($division); ?>">
        </div>

        <!-- Category -->
        <div class="col-md-4">
            <label for="category" class="form-label">Category</label>
            <select id="category" name="category" class="form-select">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_code']; ?>">
                        <?= htmlspecialchars($cat['category_code'] . ' - ' . $cat['description']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Item -->
        <div class="col-md-4">
            <label for="item" class="form-label">Item</label>
            <select id="item" name="item" class="form-select" required>
                <option value="">Select Item</option>
            </select>
        </div>

        <!-- Year -->
        <div class="col-md-4">
            <label for="year" class="form-label">Year</label>
            <input type="number" class="form-control" id="year" name="year"
                   value="<?= date('Y'); ?>" min="2020" max="2100" required>
        </div>

        <!-- Budget -->
        <div class="col-md-4">
            <label for="budget" class="form-label">Budget</label>
            <select id="budget" name="budget" class="form-select" required>
                <option value="">Choose Budget</option>
                <?php foreach ($budgets as $b): ?>
                    <option value="<?= $b['id']; ?>" data-name="<?= strtolower($b['budget']); ?>">
                        <?= htmlspecialchars($b['budget']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Unit Price -->
        <div class="col-md-4">
            <label for="unit_price" class="form-label">Unit Price (Rs)</label>
            <input type="number" class="form-control" id="unit_price" name="unit_price"
                   placeholder="Enter unit price" step="0.01" min="0">
        </div>

        <!-- Quantity -->
        <div class="col-md-4">
            <label for="quantity" class="form-label">Quantity</label>
            <?php if (!empty($quantityError)): ?>
                <div class="text-danger mb-1"><?= $quantityError; ?></div>
            <?php endif; ?>
            <input type="number" class="form-control <?= !empty($quantityError) ? 'is-invalid' : ''; ?>"
                   name="quantity" min="1" required>
        </div>

        <!-- Reason -->
        <div class="col-md-4">
            <label for="reason" class="form-label">Reason</label>
            <select name="reason" id="reason" class="form-select">
                <option value="New">New</option>
                <option value="Replace">Replace</option>
            </select>
        </div>

        <!-- Justification -->
        <div class="col-md-12">
            <label for="justification" class="form-label">Justification</label>
            <input type="text" class="form-control" name="justification" required>
        </div>

        <!-- Remark -->
        <div class="col-md-12">
            <label for="remark" class="form-label">Remark</label>
            <input type="text" class="form-control" name="remark">
        </div>

        <!-- Submit -->
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary px-5">ADD</button>
        </div>
    </form>
</div>

<?php include('includes/scripts.php'); ?>

<!-- JavaScript for dynamic functionality -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const categorySelect = document.getElementById("category");
    const itemSelect = document.getElementById("item");
    const unitPriceInput = document.getElementById("unit_price");
    const budgetSelect = document.getElementById("budget");
    const yearInput = document.getElementById("year");

    // Load items based on category
    categorySelect.addEventListener("change", function () {
        const categoryCode = this.value;

        itemSelect.innerHTML = '<option disabled selected>Select Item</option>';

        if (categoryCode) {
            fetch(`get_items_by_category.php?category_code=${encodeURIComponent(categoryCode)}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const option = document.createElement("option");
                        option.value = item.item_code;
                        option.textContent = `${item.item_code} - ${item.name}`;
                        itemSelect.appendChild(option);
                    });
                });
        }
    });

    // Load unit price on item selection
    itemSelect.addEventListener("change", function () {
        const itemCode = this.value;
        if (itemCode) {
            fetch(`get_item_price.php?item_code=${itemCode}`)
                .then(response => response.json())
                .then(data => {
                    unitPriceInput.value = data.price || '';
                });
        }
    });

    // Auto-fill year based on budget name
    function updateYearFromBudget() {
        const selected = budgetSelect.options[budgetSelect.selectedIndex];
        const name = selected?.dataset?.name || '';
        const currentYear = new Date().getFullYear();

        if (name.includes("next year")) {
            yearInput.value = currentYear + 1;
        } else if (name.includes("revised")) {
            yearInput.value = currentYear;
        }
    }

    budgetSelect.addEventListener("change", updateYearFromBudget);
    updateYearFromBudget(); // Initial call
});
</script>
