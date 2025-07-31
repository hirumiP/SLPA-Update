<?php
session_start();

if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');


// Remove this debug section after testing

// Include access control functions
function canAccessBudget($connect, $year, $budget_id) {
    $current_time = date('Y-m-d H:i:s');
    
    // Get budget name from budget_id
    $budget_query = $connect->prepare("SELECT budget FROM budget WHERE id = ?");
    $budget_query->bind_param('i', $budget_id);
    $budget_query->execute();
    $budget_result = $budget_query->get_result()->fetch_assoc();
    
    if (!$budget_result) {
        error_log("Budget ID $budget_id not found");
        return false;
    }
    
    $budget_name = $budget_result['budget'];
    
    // Check if this specific year-budget combination is accessible
    $access_query = $connect->prepare(
        "SELECT access_start, access_end FROM access_control 
         WHERE year = ? AND budget = ?"
    );
    $access_query->bind_param('ss', $year, $budget_name);
    $access_query->execute();
    $access_result = $access_query->get_result()->fetch_assoc();
    
    if (!$access_result) {
        error_log("No access period found for Year: $year, Budget: $budget_name");
        return false;
    }
    
    $is_active = ($current_time >= $access_result['access_start'] && 
                  $current_time <= $access_result['access_end']);
    
    // Debug logging
    error_log("Access Check: Year=$year, Budget=$budget_name, Current=$current_time, Start={$access_result['access_start']}, End={$access_result['access_end']}, Active=" . ($is_active ? 'YES' : 'NO'));
    
    return $is_active;
}

// Get available budget options based on active access periods
function getAvailableBudgets($connect) {
    $current_time = date('Y-m-d H:i:s');
    
    // Get all active access periods
    $access_query = $connect->prepare(
        "SELECT DISTINCT ac.year, ac.budget, b.id as budget_id 
         FROM access_control ac
         JOIN budget b ON ac.budget = b.budget
         WHERE ? BETWEEN ac.access_start AND ac.access_end
         ORDER BY ac.year ASC, b.budget ASC"
    );
    $access_query->bind_param('s', $current_time);
    $access_query->execute();
    
    return $access_query->get_result();
}

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

// Get available budgets based on access periods
$available_budgets = getAvailableBudgets($connect);
$budget_options = [];
while ($row = $available_budgets->fetch_assoc()) {
    $budget_options[] = $row;
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
        // Check if user can access this specific year-budget combination
        if (!canAccessBudget($connect, $year, $budget_id)) {
            $errorMsg = "Access denied for the selected year and budget combination. Please check the access periods.";
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
}

// Get active access periods to display to user
$current_time = date('Y-m-d H:i:s');
$access_periods_query = $connect->prepare(
    "SELECT year, budget, access_start, access_end FROM access_control 
     WHERE ? BETWEEN access_start AND access_end 
     ORDER BY year DESC, budget ASC"
);
$access_periods_query->bind_param('s', $current_time);
$access_periods_query->execute();
$active_periods = $access_periods_query->get_result();
?>

<div class="container-fluid px-4">
    <h2 class="text-center mb-4 fw-bold text-primary" style="letter-spacing: 1px;">Item Request For Budget</h2>
    
    <!-- Show active access periods -->
    <?php if ($active_periods->num_rows > 0): ?>
        <div class="alert alert-info mb-4">
            <h6><i class="bi bi-clock me-2"></i>Active Access Periods</h6>
            <ul class="mb-0 small">
                <?php 
                $active_periods->data_seek(0); // Reset pointer
                while ($period = $active_periods->fetch_assoc()): ?>
                    <li><strong><?= $period['year'] ?> - <?= $period['budget'] ?></strong>: 
                        <?= date('M j, Y g:i A', strtotime($period['access_start'])) ?> to 
                        <?= date('M j, Y g:i A', strtotime($period['access_end'])) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mb-4">
            <h6><i class="bi bi-exclamation-triangle me-2"></i>No Active Access Periods</h6>
            <p class="mb-0">The system is currently not accepting requests. Please contact your administrator.</p>
        </div>
    <?php endif; ?>
    
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
            <select id="year" name="year" class="form-select" required>
                <option value="" selected disabled>Select Year</option>
                <?php 
                // Get unique years from active access periods
                $unique_years = array_unique(array_column($budget_options, 'year'));
                sort($unique_years);
                foreach ($unique_years as $year): ?>
                    <option value="<?= $year ?>"><?= $year ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Budget -->
        <div class="col-md-4">
            <label for="budget" class="form-label fw-semibold">Budget <span class="text-danger">*</span></label>
            <select id="budget" name="budget" class="form-select" required>
                <option value="" selected disabled>Choose Budget</option>
                <?php foreach ($budget_options as $budget): ?>
                    <option value="<?= $budget['budget_id'] ?>" 
                            data-year="<?= $budget['year'] ?>" 
                            data-name="<?= strtolower($budget['budget']) ?>">
                        <?= $budget['budget'] ?>
                    </option>
                <?php endforeach; ?>
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
    const yearSelect = document.getElementById("year");
    const form = document.querySelector("form");
    
    // Available budget options from PHP
    const availableBudgets = <?= json_encode($budget_options) ?>;
    
    // Filter items based on selected category
    categorySelect.addEventListener("change", () => {
        const selectedCategory = categorySelect.value;
        Array.from(itemSelect.options).forEach(option => {
            option.hidden = option.getAttribute("data-category") !== selectedCategory && option.value !== "";
        });
        itemSelect.value = "";
    });

    // Filter budgets based on selected year
    yearSelect.addEventListener("change", () => {
        const selectedYear = yearSelect.value;
        
        Array.from(budgetSelect.options).forEach(option => {
            if (option.value === "") {
                option.hidden = false;
            } else {
                const optionYear = option.getAttribute("data-year");
                option.hidden = optionYear !== selectedYear;
            }
        });
        
        budgetSelect.value = "";
    });

    // Validate form submission
    form.addEventListener("submit", function(e) {
        const selectedYear = yearSelect.value;
        const selectedBudgetId = budgetSelect.value;
        
        // Check if the selected combination is in available budgets
        const isValid = availableBudgets.some(budget => 
            budget.year === selectedYear && budget.budget_id === selectedBudgetId
        );
        
        if (!isValid) {
            e.preventDefault();
            alert("The selected year-budget combination is not currently accessible. Please check the active access periods.");
            return false;
        }
        
        // Additional validation for required fields
        if (!categorySelect.value || !itemSelect.value || !yearSelect.value || 
            !budgetSelect.value || !unitPriceInput.value || !quantityInput.value) {
            e.preventDefault();
            alert("Please fill in all required fields.");
            return false;
        }
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
                })
                .catch(err => {
                    console.log('Price fetch failed:', err);
                });
        } else {
            unitPriceInput.value = '';
            updateTotalCost();
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
    
    // Initialize form - hide all budget options initially
    Array.from(budgetSelect.options).forEach(option => {
        if (option.value !== "") {
            option.hidden = true;
        }
    });
});
</script>
