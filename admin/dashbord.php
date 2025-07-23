<?php
session_start();
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include('includes/dbc.php');

// --- Filter Logic ---
$selectedYear = $_GET['year'] ?? '';
$selectedBudget = $_GET['budget'] ?? '';
$selectedDivision = $_GET['division'] ?? '';

// Get filter values for dropdowns
$yearResult = mysqli_query($connect, "SELECT DISTINCT year FROM item_requests ORDER BY year DESC");
$budgetResult = mysqli_query($connect, "SELECT DISTINCT budget FROM budget ORDER BY budget ASC");
$divisionResult = mysqli_query($connect, "SELECT DISTINCT division FROM item_requests ORDER BY division ASC");

// Build WHERE clause for filters
$where = "ir.status = 'Approved'";
if (!empty($selectedYear)) {
    $where .= " AND ir.year = '" . mysqli_real_escape_string($connect, $selectedYear) . "'";
}
if (!empty($selectedBudget)) {
    $where .= " AND b.budget = '" . mysqli_real_escape_string($connect, $selectedBudget) . "'";
}
if (!empty($selectedDivision)) {
    $where .= " AND ir.division = '" . mysqli_real_escape_string($connect, $selectedDivision) . "'";
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 fw-bold text-primary">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4 bg-white shadow-sm rounded py-2 px-3">
        <li class="breadcrumb-item active fs-5">Dashboard</li>
    </ol>

   <?php include('includes/filter.php'); ?>
    <!-- Filter Section -->
    <div class="card shadow-sm mb-4 no-print" style="background-color: #d4e8fdff;">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end justify-content-center">
                <!-- Year Dropdown -->
                <div class="col-md-3">
                    <label for="year" class="form-label fw-semibold">Year</label>
                    <select name="year" id="year" class="form-select">
                        <option value="">All Years</option>
                        <?php
                        mysqli_data_seek($yearResult, 0);
                        while ($row = mysqli_fetch_assoc($yearResult)): ?>
                            <option value="<?= $row['year'] ?>" <?= ($selectedYear == $row['year']) ? 'selected' : '' ?>>
                                <?= $row['year'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Budget Type Dropdown -->
                <div class="col-md-3">
                    <label for="budget" class="form-label fw-semibold">Budget Type</label>
                    <select name="budget" id="budget" class="form-select">
                        <option value="">All Budgets</option>
                        <?php
                        mysqli_data_seek($budgetResult, 0);
                        while ($row = mysqli_fetch_assoc($budgetResult)): ?>
                            <option value="<?= $row['budget'] ?>" <?= ($selectedBudget == $row['budget']) ? 'selected' : '' ?>>
                                <?= $row['budget'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Division Dropdown -->
                <div class="col-md-3">
                    <label for="division" class="form-label fw-semibold">Division (Optional)</label>
                    <select name="division" id="division" class="form-select">
                        <option value="">All Divisions</option>
                        <?php
                        mysqli_data_seek($divisionResult, 0);
                        while ($row = mysqli_fetch_assoc($divisionResult)): ?>
                            <option value="<?= $row['division'] ?>" <?= ($selectedDivision == $row['division']) ? 'selected' : '' ?>>
                                <?= $row['division'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Submit Button -->
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Charts -->
    <div class="container mt-4">
        <!-- Chart 1: Item-wise Total Cost -->
        <div class="table-container mt-5">
            <h4 class="text-primary">Item-wise Total Cost</h4>
            <canvas id="itemWiseChart" height="120"></canvas>
        </div>
        <!-- Chart 2: Division-wise Total Cost -->
        <div class="table-container mt-5">
            <h4 class="text-primary">Division-wise Total Cost</h4>
            <canvas id="divisionWiseChart" height="120"></canvas>
        </div>
    </div>
</div>

<?php
// 1. Item-wise total cost (with filters)
$item_sql = "SELECT i.name AS item_name, SUM(ir.quantity * ir.unit_price) AS total_cost
             FROM item_requests ir
             LEFT JOIN items i ON ir.item_code = i.item_code
             LEFT JOIN budget b ON ir.budget_id = b.id
             WHERE $where
             GROUP BY i.name
             ORDER BY i.name";
$item_result = mysqli_query($connect, $item_sql);

$item_labels = [];
$item_data = [];
while ($row = mysqli_fetch_assoc($item_result)) {
    $item_labels[] = $row['item_name'];
    $item_data[] = $row['total_cost'];
}

// 2. Division-wise total cost (with filters)
$division_sql = "SELECT ir.division, SUM(ir.quantity * ir.unit_price) AS total_cost
                 FROM item_requests ir
                 LEFT JOIN items i ON ir.item_code = i.item_code
                 LEFT JOIN budget b ON ir.budget_id = b.id
                 WHERE $where
                 GROUP BY ir.division
                 ORDER BY ir.division";
$division_result = mysqli_query($connect, $division_sql);

$division_labels = [];
$division_data = [];
while ($row = mysqli_fetch_assoc($division_result)) {
    $division_labels[] = $row['division'];
    $division_data[] = $row['total_cost'];
}
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const itemLabels = <?php echo json_encode($item_labels); ?>;
    const itemData = <?php echo json_encode($item_data); ?>;
    const divisionLabels = <?php echo json_encode($division_labels); ?>;
    const divisionData = <?php echo json_encode($division_data); ?>;

    new Chart(document.getElementById('itemWiseChart'), {
        type: 'bar',
        data: {
            labels: itemLabels,
            datasets: [{
                label: 'Total Cost (Rs.)',
                data: itemData,
                backgroundColor: '#0f4c81'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    new Chart(document.getElementById('divisionWiseChart'), {
        type: 'bar',
        data: {
            labels: divisionLabels,
            datasets: [{
                label: 'Total Cost (Rs.)',
                data: divisionData,
                backgroundColor: '#28a745'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<?php
include('includes/scripts.php');
?>
