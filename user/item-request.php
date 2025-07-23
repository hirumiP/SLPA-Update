<?php
session_start();

if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

$items = [];
$division = $_SESSION['division'];

$quantityError = ""; // ✨ Error message variable

// Fetch items
$item_query = "SELECT item_code, name FROM items";
$item_result = $connect->query($item_query);
if ($item_result->num_rows > 0) {
    while ($row = $item_result->fetch_assoc()) {
        $items[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $division = $_POST['division'];
    $item_code = $_POST['item'];
    $year = !empty($_POST['year']) ? $_POST['year'] : null;
    if (empty(trim($_POST['justification']))) {
        echo "<div class='alert alert-danger text-center'>Justification is required.</div>";
        exit();
    }
    $justification = trim($_POST['justification']);
    $reason = $_POST['reason'];
    $unit_price = !empty($_POST['unit_price']) ? $_POST['unit_price'] : null;
    $budget_id = !empty($_POST['budget']) ? $_POST['budget'] : null;
    $remark = !empty($_POST['remark']) ? $_POST['remark'] : null;

    // ✨ Quantity validation
    if (!isset($_POST['quantity']) || !is_numeric($_POST['quantity']) || intval($_POST['quantity']) <= 0) {
        $quantityError = "Please enter a valid quantity (greater than 0).";
    } else {
        $quantity = intval($_POST['quantity']);
    }

    // ✨ If no quantity error, proceed with DB insert
    if (empty($quantityError)) {
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

        <div class="col-md-4">
            <label for="division" class="form-label">Division</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($division); ?>" readonly>
            <input type="hidden" name="division" value="<?= htmlspecialchars($division); ?>">
        </div>

        <div class="col-md-4">
            <label for="item" class="form-label">Item</label>
            <select id="item" name="item" class="form-select" required>
                <option disabled <?= !isset($_POST['item']) ? 'selected' : ''; ?>>Select Item</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['item_code']; ?>" <?= (isset($_POST['item']) && $_POST['item'] === $item['item_code']) ? 'selected' : ''; ?>>
                        <?= $item['item_code']; ?> - <?= $item['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="year" class="form-label">Year</label>
            <input type="number" class="form-control" id="year" name="year" placeholder="Enter year"
                   value="<?= isset($_POST['year']) ? htmlspecialchars($_POST['year']) : date('Y'); ?>" min="2020" max="2100" required>
        </div>

        <div class="col-md-4">
            <label for="budget" class="form-label">Budget</label>
            <select id="budget" name="budget" class="form-select">
                <option disabled <?= !isset($_POST['budget']) ? 'selected' : ''; ?>>Choose Budget</option>
                <?php
                $sql = "SELECT * FROM budget";
                $result = $connect->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $selected = (isset($_POST['budget']) && $_POST['budget'] == $row['id']) ? 'selected' : '';
                    echo "<option value='" . $row['id'] . "' data-name='" . strtolower($row['budget']) . "' $selected>" . $row['budget'] . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="unit_price" class="form-label">Unit Price (Rs)</label>
            <input type="number" class="form-control" id="unit_price" name="unit_price"
                   placeholder="Enter unit price" step="0.01" min="0"
                   value="<?= isset($_POST['unit_price']) ? htmlspecialchars($_POST['unit_price']) : ''; ?>">
        </div>

        <div class="col-md-4">
            <label for="quantity" class="form-label">Quantity</label>
            <?php if (!empty($quantityError)): ?>
                <div class="text-danger mb-2"><?= $quantityError; ?></div>
            <?php endif; ?>
            <input
                type="number"
                class="form-control <?= !empty($quantityError) ? 'is-invalid' : ''; ?>"
                id="quantity"
                name="quantity"
                placeholder="Enter quantity"
                min="1"
                required
                value="<?= isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>"
            >
        </div>

        <div class="col-md-12">
            <label for="reason" class="form-label">Reason</label>
            <select id="reason" name="reason" class="form-select">
                <option value="New" <?= (isset($_POST['reason']) && $_POST['reason'] === 'New') ? 'selected' : ''; ?>>New</option>
                <option value="Replace" <?= (isset($_POST['reason']) && $_POST['reason'] === 'Replace') ? 'selected' : ''; ?>>Replace</option>
            </select>
        </div>

        <div class="col-md-12">
            <label for="justification" class="form-label">Justification</label>
            <input 
                type="text" 
                class="form-control" 
                id="justification" 
                name="justification" 
                placeholder="Enter justification" 
                required
            >
        </div>

        <div class="col-md-12">
            <label for="remark" class="form-label">Remark</label>
            <input type="text" class="form-control" id="remark" name="remark"
                   placeholder="Enter remark (optional)"
                   value="<?= isset($_POST['remark']) ? htmlspecialchars($_POST['remark']) : ''; ?>">
        </div>

        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary px-5 py-2">ADD</button>
        </div>
    </form>
</div>

<?php include('includes/scripts.php'); ?>

<!-- ✅ Auto-fill unit price -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const itemSelect = document.getElementById("item");
    const unitPriceInput = document.getElementById("unit_price");

    itemSelect.addEventListener("change", function () {
        const selectedItem = itemSelect.value;

        if (selectedItem) {
            fetch(`get_item_price.php?item_code=${selectedItem}`)
                .then(response => response.json())
                .then(data => {
                    if (data.price !== undefined && data.price !== null) {
                        unitPriceInput.value = data.price;
                    } else {
                        unitPriceInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Error fetching unit price:', error);
                    unitPriceInput.value = '';
                });
        }
    });

    // ✅ Auto-update year based on budget selection
    const budgetSelect = document.getElementById("budget");
    const yearInput = document.getElementById("year");
    const currentYear = new Date().getFullYear();

    function updateYearFromBudget() {
        const selectedOption = budgetSelect.options[budgetSelect.selectedIndex];
        const budgetName = selectedOption.getAttribute('data-name');

        if (budgetName && budgetName.includes("next year")) {
            yearInput.value = currentYear + 1;
        } else if (budgetName && budgetName.includes("revised")) {
            yearInput.value = currentYear;
        }
    }

    budgetSelect.addEventListener("change", updateYearFromBudget);

    // Trigger on load if already selected
    updateYearFromBudget();
});
</script>
