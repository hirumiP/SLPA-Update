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
    $year = $_POST['year'];
    $justification = $_POST['justification'];
    $reason = $_POST['reason'];
    $unit_price = $_POST['unit_price'];
    $quantity = $_POST['quantity'];
    $budget_id = $_POST['budget'];
    $remark = $_POST['remark'];

    $sql = "INSERT INTO item_requests (division, item_code, year, description, reason, unit_price, quantity, budget_id, remark)
    VALUES ('$division', '$item_code', 
    " . (!empty($year) ? "'$year'" : "NULL") . ", 
    '$justification', '$reason', 
    " . (!empty($unit_price) ? "'$unit_price'" : "NULL") . ", 
    " . (!empty($quantity) ? "'$quantity'" : "NULL") . ", 
    " . (!empty($budget_id) ? "'$budget_id'" : "NULL") . ", 
    " . (!empty($remark) ? "'$remark'" : "NULL") . ")";

    if ($connect->query($sql) === TRUE) {
        echo "<div class='alert alert-success text-center'>Item request successfully added!</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error: " . $connect->error . "</div>";
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
                <option selected disabled>Select Item</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['item_code']; ?>">
                        <?= $item['item_code']; ?> - <?= $item['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="budget" class="form-label">Budget</label>
            <select id="budget" name="budget" class="form-select">
                <option selected>Choose Budget</option>
                <?php
                $sql = "SELECT * FROM budget";
                $result = $connect->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['budget'] . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-4">
    <label for="year" class="form-label">Year</label>
    <input type="number" class="form-control" id="year" name="year" placeholder="Enter year" value="<?= date('Y'); ?>" min="2020" max="2100" required>
</div>


        <div class="col-md-4">
            <label for="unit_price" class="form-label">Unit Price (Rs)</label>
            <input type="number" class="form-control" id="unit_price" name="unit_price" placeholder="Enter unit price" step="0.01" min="0">
        </div>

        <div class="col-md-4">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity" min="1">
        </div>

        <div class="col-md-12">
            <label for="reason" class="form-label">Reason</label>
            <select id="reason" name="reason" class="form-select">
                <option value="New">New</option>
                <option value="Replace">Replace</option>
            </select>
        </div>

        <div class="col-md-12">
            <label for="justification" class="form-label">Justification</label>
            <input type="text" class="form-control" id="justification" name="justification" placeholder="Enter justification">
        </div>

        <div class="col-md-12">
            <label for="remark" class="form-label">Remark</label>
            <input type="text" class="form-control" id="remark" name="remark" placeholder="Enter remark (optional)">
        </div>

        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary px-5 py-2">ADD</button>
        </div>
    </form>
</div>

<?php include('includes/scripts.php'); ?>

<!-- ✅ JavaScript for Auto-Filling Unit Price -->
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
});
</script>
