<?php
session_start();
include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

// Query to get the items distribution by division/category
$distributionQuery = "SELECT division, COUNT(*) AS request_count FROM item_requests GROUP BY division";
$distributionResult = mysqli_query($connect, $distributionQuery);
$divisionLabels = [];
$divisionData = [];
while ($row = mysqli_fetch_assoc($distributionResult)) {
    $divisionLabels[] = $row['division'];
    $divisionData[] = $row['request_count'];
}

// Query to get the budget allocation by division/category
$budgetQuery = "SELECT division, SUM(unit_price * quantity) AS total_budget FROM item_requests GROUP BY division";
$budgetResult = mysqli_query($connect, $budgetQuery);
$budgetLabels = [];
$budgetData = [];
while ($row = mysqli_fetch_assoc($budgetResult)) {
    $budgetLabels[] = $row['division'];
    $budgetData[] = $row['total_budget'];
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <!-- Summary Section -->
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white mb-4" style="background-color: #003366;">
                <div class="card-body">
                    <?php
                    $result = mysqli_query($connect, "SELECT COUNT(*) AS total_requests FROM item_requests");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h5>Total Requests</h5>";
                    echo "<h3>{$row['total_requests']}</h3>";
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white mb-4" style="background-color: #003366;">
                <div class="card-body">
                    <?php
                    $result = mysqli_query($connect, "SELECT SUM(quantity) AS total_items FROM item_requests");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h5>Total Items Requested</h5>";
                    echo "<h3>{$row['total_items']}</h3>";
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white mb-4" style="background-color: #003366;">
                <div class="card-body">
                    <?php
                    $result = mysqli_query($connect, "SELECT SUM(unit_price * quantity) AS total_budget FROM item_requests");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h5>Total Budget (LKR)</h5>";
                    echo "<h3>" . number_format($row['total_budget'], 2) . "</h3>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4" style="border: 2px solid #003366;">
                <div class="card-header" style="background-color: #003366; color: #ffffff;">
                    <i class="fas fa-chart-pie me-1"></i>
                    Items Distribution
                </div>
                <div class="card-body">
                    <canvas id="itemsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4" style="border: 2px solid #003366;">
                <div class="card-header" style="background-color: #003366; color: #ffffff;">
                    <i class="fas fa-chart-bar me-1"></i>
                    Budget Allocation
                </div>
                <div class="card-body">
                    <canvas id="budgetChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Items Distribution Chart (Pie)
    const itemsCtx = document.getElementById('itemsChart').getContext('2d');
    const itemsChart = new Chart(itemsCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($divisionLabels); ?>,  // Division names dynamically from DB
            datasets: [{
                data: <?php echo json_encode($divisionData); ?>,  // Request count dynamically from DB
                backgroundColor: ['#003366', '#007bff', '#28a745', '#ff5733', '#f39c12'],
            }]
        }
    });

    // Budget Allocation Chart (Bar)
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($budgetLabels); ?>,  // Division names dynamically from DB
            datasets: [{
                label: 'Budget (LKR)',
                data: <?php echo json_encode($budgetData); ?>,  // Total budget dynamically from DB
                backgroundColor: ['#003366', '#007bff', '#28a745', '#ff5733', '#f39c12'],
            }]
        }
    });
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
