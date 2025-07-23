<?php
session_start();
include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

$loggedDivision = $_SESSION['division'] ?? null;

$selectedYear = $_GET['year'] ?? '';
$selectedBudget = $_GET['budget'] ?? '';

// Fetch dropdown values
$years = [];
$budgets = [];

$yearsResult = mysqli_query($connect, "SELECT DISTINCT year FROM item_requests ORDER BY year DESC");
while ($row = mysqli_fetch_assoc($yearsResult)) {
    $years[] = $row['year'];
}

$budgetResult = mysqli_query($connect, "SELECT DISTINCT budget_id FROM item_requests ORDER BY budget_id");
while ($row = mysqli_fetch_assoc($budgetResult)) {
    $budgets[] = $row['budget_id'];
}

// Escape input
$escapedDivision = mysqli_real_escape_string($connect, $loggedDivision);
$escapedYear = mysqli_real_escape_string($connect, $selectedYear);
$escapedBudget = mysqli_real_escape_string($connect, $selectedBudget);

// Summary
$total_requests = $total_items = $total_budget = 0;
if ($loggedDivision && $selectedYear && $selectedBudget) {
    $query = "
        SELECT 
            COUNT(*) AS total_requests,
            SUM(quantity) AS total_items,
            SUM(unit_price * quantity) AS total_budget
        FROM item_requests
        WHERE 
            division = '$escapedDivision'
            AND year = '$escapedYear'
            AND budget_id = '$escapedBudget'
    ";
    $result = mysqli_query($connect, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $total_requests = $row['total_requests'];
        $total_items = $row['total_items'];
        $total_budget = $row['total_budget'];
    }
}

// Pie chart data (item name vs total cost)
$itemsChartLabels = [];
$itemsChartData = [];
if ($loggedDivision && $selectedYear && $selectedBudget) {
    $itemQuery = "
        SELECT i.name AS item_name, SUM(ir.unit_price * ir.quantity) AS total_cost
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        WHERE 
            ir.division = '$escapedDivision'
            AND ir.year = '$escapedYear'
            AND ir.budget_id = '$escapedBudget'
        GROUP BY i.name
    ";
    $itemResult = mysqli_query($connect, $itemQuery);
    while ($row = mysqli_fetch_assoc($itemResult)) {
        $itemsChartLabels[] = $row['item_name'];
        $itemsChartData[] = $row['total_cost'];
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">
        SLPA Budget Management System
        <?php if ($loggedDivision): ?>
            <small style="font-size: 35px; color: #555;"> - <?= htmlspecialchars($loggedDivision) ?></small>
        <?php endif; ?>
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <!-- Filter Section -->
    <form method="GET" class="row mb-4">
        <div class="col-md-3">
            <label>Year</label>
            <select name="year" class="form-control" required>
                <option value="">Select Year</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?= $year ?>" <?= ($selectedYear == $year) ? 'selected' : '' ?>>
                        <?= $year ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Budget Round</label>
            <select name="budget" class="form-control" required>
                <option value="">Select Budget</option>
                <?php foreach ($budgets as $budget): ?>
                    <option value="<?= $budget ?>" <?= ($selectedBudget == $budget) ? 'selected' : '' ?>>
    <?= ($budget == 1) ? 'First Round' : (($budget == 2) ? 'Revised' : 'Unknown') ?>
</option>

                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- Summary Section -->
    <?php if ($selectedYear && $selectedBudget): ?>
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white mb-4" style="background-color: #003366;">
                    <div class="card-body">
                        <h5>Total Requests</h5>
                        <h3><?= $total_requests ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white mb-4" style="background-color: #003366;">
                    <div class="card-body">
                        <h5>Total Items Requested</h5>
                        <h3><?= $total_items ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white mb-4" style="background-color: #003366;">
                    <div class="card-body">
                        <h5>Total Budget (LKR)</h5>
                        <h3><?= number_format($total_budget, 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Pie Chart Section -->
    <!-- <?php if (!empty($itemsChartLabels)): ?>
        <div class="card mb-4" style="border: 2px solid #003366;">
            <div class="card-header" style="background-color: #003366; color: white;">
                <i class="fas fa-chart-pie me-1"></i>
                Item Requests by Total Cost (LKR)
            </div>
            <div class="card-body">
                <canvas id="itemRequestsChart" height="100"></canvas>
            </div>
        </div>
    <?php endif; ?> -->
</div>

<?php include('includes/footer.php'); ?>
<?php include('includes/scripts.php'); ?>

<?php if (!empty($itemsChartLabels)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('itemRequestsChart').getContext('2d');
    const itemLabels = <?= json_encode($itemsChartLabels) ?>;
    const itemData = <?= json_encode($itemsChartData) ?>;

    const backgroundColors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#8E44AD', '#3498DB', '#1ABC9C', '#F39C12',
        '#D35400', '#2ECC71'
    ];

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: itemLabels,
            datasets: [{
                data: itemData,
                backgroundColor: backgroundColors.slice(0, itemData.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: {
                    display: true,
                    text: 'Item Requests by Total Cost (LKR)',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: LKR ${Number(value).toLocaleString()}`;
                        }
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>
