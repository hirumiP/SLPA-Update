<?php
session_start();

if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

$loggedDivision = $_SESSION['division'];

// Fetch pending item requests for that division
$sql = "SELECT 
            ir.division, 
            i.name AS item_name, 
            b.budget AS budget_name, 
            ir.year, 
            ir.unit_price, 
            ir.quantity, 
            ir.reason, 
            ir.description,
            ir.status
        FROM 
            item_requests ir
        LEFT JOIN 
            items i ON ir.item_code = i.item_code
        LEFT JOIN 
            budget b ON ir.budget_id = b.id
        WHERE
            ir.division = '$loggedDivision' AND ir.status = 'Pending'";

$result = mysqli_query($connect, $sql);

// Check if there are pending requests
$pendingCount = mysqli_num_rows($result);
?>

<div class="container-fluid px-4">
    <h2 class="text-center mb-4">Item Request Plans (Division: <?php echo $loggedDivision; ?>)</h2>

    <?php if ($pendingCount > 0): ?>
        <!-- Alert for Pending Requests -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Attention!</strong> You have <?php echo $pendingCount; ?> item request(s) pending approval.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php else: ?>
        <!-- Alert for No Pending Requests -->
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Good News!</strong> There are no pending item requests for approval.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

</div>

<?php include('includes/scripts.php'); ?>
