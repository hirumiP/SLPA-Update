<?php
session_start();
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include('includes/dbc.php');
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .table-container {
            margin-top: 40px;
        }
    </style>
</head>
<body>
<?php include('includes/filter.php'); ?>

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

<?php
// 1. Item-wise total cost
$item_sql = "SELECT i.name AS item_name, SUM(ir.quantity * ir.unit_price) AS total_cost
             FROM item_requests ir
             LEFT JOIN items i ON ir.item_code = i.item_code
             WHERE ir.status = 'Approved'
             GROUP BY i.name
             ORDER BY i.name";

$item_result = mysqli_query($connect, $item_sql);

$item_labels = [];
$item_data = [];

while ($row = mysqli_fetch_assoc($item_result)) {
    $item_labels[] = $row['item_name'];
    $item_data[] = $row['total_cost'];
}

// 2. Division-wise total cost
$division_sql = "SELECT ir.division, SUM(ir.quantity * ir.unit_price) AS total_cost
                 FROM item_requests ir
                 LEFT JOIN items i ON ir.item_code = i.item_code
                 WHERE ir.status = 'Approved'
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

</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
