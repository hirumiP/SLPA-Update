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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mt-5">
                <div class="card-header bg-primary text-white fw-semibold text-center" style="font-size: 1.3rem;">
                    <i class="bi bi-clipboard-data"></i> Item Requests -
                    <span class="fw-normal" style="font-size: 1.3rem;">Division: <?php echo htmlspecialchars($loggedDivision); ?></span>
                </div>
                <div class="card-body">
                    <?php if ($pendingCount > 0): ?>
                        <a href="item_req.php" style="text-decoration: none;">
                            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>
                                    <strong>Attention!</strong> You have <?php echo $pendingCount; ?> item request(s) pending approval.
                                </div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="item_req.php" style="text-decoration: none;">
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div>
                                    <strong>Good News!</strong> There are no pending item requests for approval.
                                </div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </a>
                    <?php endif; ?>
                    <div class="text-center mt-4">
                        <a href="item_req.php" class="btn btn-outline-primary btn-lg px-4">
                            <i class="bi bi-list-task"></i> View All Item Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .card {
        border-radius: 0.75rem;
    }
    .card-header {
        letter-spacing: 0.5px;
    }
    .alert {
        font-size: 1.1rem;
        border-radius: 0.5rem;
    }
    .btn-outline-primary {
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 1.1rem;
    }
</style>

<?php include('includes/scripts.php'); ?>
