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
    <h1 class="mt-4 fw-bold text-primary" style="letter-spacing: 1px;">
        SLPA Budget Management System
        <?php if ($loggedDivision): ?>
            <small class="fw-bold text-primary" style="font-size: 2rem; letter-spacing: 1px;">
                - <?= htmlspecialchars($loggedDivision) ?>
            </small>
        <?php endif; ?>
    </h1>
    <ol class="breadcrumb mb-4 bg-white shadow-sm rounded py-2 px-3">
        <li class="breadcrumb-item active fs-5">Dashboard</li>
    </ol>

    <!-- Filter Section -->
    <form method="GET" class="row mb-4 g-3 align-items-end justify-content-center">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Year</label>
            <select name="year" class="form-select" required>
                <option value="">Select Year</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?= $year ?>" <?= ($selectedYear == $year) ? 'selected' : '' ?>>
                        <?= $year ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Budget Round</label>
            <select name="budget" class="form-select" required>
                <option value="">Select Budget</option>
                <?php foreach ($budgets as $budget): ?>
                    <option value="<?= $budget ?>" <?= ($selectedBudget == $budget) ? 'selected' : '' ?>>
                        <?= ($budget == 1) ? 'First Round' : (($budget == 2) ? 'Revised' : 'Unknown') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary fw-semibold">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>
        </div>
    </form>

    <!-- Summary Section -->
    <?php if ($selectedYear && $selectedBudget): ?>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <a href="eq_plan.php?year=<?= urlencode($selectedYear) ?>&budget=<?= urlencode($selectedBudget) ?>" 
                   class="text-decoration-none">
                    <div class="card text-white shadow-sm h-100 card-hover" 
                         style="background: linear-gradient(135deg, #003366 70%, #00509e 100%);">
                        <div class="card-body text-center">
                            <div class="mb-2"><i class="bi bi-clipboard-data" style="font-size: 2rem;"></i></div>
                            <h5 class="card-title">Total Requests</h5>
                            <h2 class="fw-bold"><?= $total_requests ?></h2>
                            <small class="opacity-75">
                                <i class="bi bi-arrow-right-circle me-1"></i>View Details
                            </small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="eq_plan.php?year=<?= urlencode($selectedYear) ?>&budget=<?= urlencode($selectedBudget) ?>" 
                   class="text-decoration-none">
                    <div class="card text-white shadow-sm h-100 card-hover" 
                         style="background: linear-gradient(135deg, #00509e 70%, #0074d9 100%);">
                        <div class="card-body text-center">
                            <div class="mb-2"><i class="bi bi-box-seam" style="font-size: 2rem;"></i></div>
                            <h5 class="card-title">Total Items Requested</h5>
                            <h2 class="fw-bold"><?= $total_items ?></h2>
                            <small class="opacity-75">
                                <i class="bi bi-arrow-right-circle me-1"></i>View Details
                            </small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <div class="card text-white shadow-sm h-100" 
                     style="background: linear-gradient(135deg, #0074d9 70%, #00b8d9 100%);">
                    <div class="card-body text-center">
                        <div class="mb-2"><i class="bi bi-cash-coin" style="font-size: 2rem;"></i></div>
                        <h5 class="card-title">Total Budget (LKR)</h5>
                        <h2 class="fw-bold"><?= number_format($total_budget, 2) ?></h2>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Additional Debug Section for Current Access Status -->
    <div class="alert alert-info mb-4">
        <h6><i class="bi bi-info-circle"></i> Current Access Status:</h6>
        <?php
        $current_time = date('Y-m-d H:i:s');
        $active_periods = $connect->query("SELECT * FROM access_control WHERE '$current_time' BETWEEN access_start AND access_end");
        
        if ($active_periods->num_rows > 0) {
            echo "<p class='text-success mb-2'><strong>✅ Active Access Periods:</strong></p><ul class='mb-0'>";
            while ($period = $active_periods->fetch_assoc()) {
                echo "<li class='text-success'>{$period['year']} - {$period['budget']}: " . 
                     date('M j, Y g:i A', strtotime($period['access_start'])) . " to " . 
                     date('M j, Y g:i A', strtotime($period['access_end'])) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='text-warning mb-2'><strong>⚠️ No Active Access Periods</strong></p>";
            
            // Show upcoming periods
            $upcoming_periods = $connect->query("SELECT * FROM access_control WHERE access_start > '$current_time' ORDER BY access_start ASC LIMIT 3");
            if ($upcoming_periods->num_rows > 0) {
                echo "<p class='mb-2'><strong>📅 Upcoming Access Periods:</strong></p><ul class='mb-0'>";
                while ($period = $upcoming_periods->fetch_assoc()) {
                    echo "<li class='text-info'>{$period['year']} - {$period['budget']}: " . 
                         date('M j, Y g:i A', strtotime($period['access_start'])) . " to " . 
                         date('M j, Y g:i A', strtotime($period['access_end'])) . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>

    <!-- Pie Chart Section -->
    <?php /* Chart removed as requested
    <?php if (!empty($itemsChartLabels)): ?>
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-pie-chart-fill me-2"></i>
                Item Requests by Total Cost (LKR)
            </div>
            <div class="card-body">
                <canvas id="itemRequestsChart" height="100"></canvas>
            </div>
        </div>
    <?php endif; ?>
    */ ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .card {
        border-radius: 0.75rem;
    }
    .card-header {
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }
    .form-label {
        font-size: 1rem;
    }
    .breadcrumb {
        font-size: 1.1rem;
    }
    .alert ul {
        padding-left: 20px;
    }
    .alert li {
        margin-bottom: 5px;
    }
    @media (max-width: 768px) {
        .card-title { font-size: 1rem; }
        .card-body h2 { font-size: 1.3rem; }
        .form-label { font-size: 0.95rem; }
        .alert h6 { font-size: 1rem; }
    }
</style>

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
