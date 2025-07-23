<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

// Retrieve the logged-in user's role and division
$user_role = $_SESSION['role'];
$user_division = $_SESSION['division'];

// Only allow access for 'user' role
if ($user_role !== 'user') {
    echo "<p>You do not have permission to view this page.</p>";
    exit();
}

// SQL query to fetch item requests for the logged-in user's division
$sql = "SELECT 
            ir.division, 
            i.name AS item_name, 
            b.budget AS budget_name, 
            ir.year, 
            ir.approval_qty, 
            ir.unit_price, 
            ir.quantity, 
            ir.reason, 
            ir.description AS justification,
            ir.remark,
            ir.status
        FROM 
            item_requests ir
        LEFT JOIN 
            items i ON ir.item_code = i.item_code
        LEFT JOIN 
            budget b ON ir.budget_id = b.id
        WHERE
            ir.division = ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $user_division);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!-- ✅ Hide elements when printing -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
}
.justify-left {
    text-align: left;
}

</style>

<div class="container-fluid px-4">
    <h2 class="text-center mb-4">Item Requests - Division: <?php echo htmlspecialchars($user_division); ?></h2>
    
    <div class="table-container">
        <table class="table table-bordered table-left">
            <thead>
                <tr>
                    
                    <th scope="col">Item Name</th>
                    <th scope="col">Budget Name</th>
                    <th scope="col">Year</th>
                    <th scope="col">Unit Price</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Reason</th>
                    <th scope="col">Justification</th>
                    <th scope="col">Remark</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            
                            <td>" . htmlspecialchars($row['item_name']) . "</td>
                            <td>" . htmlspecialchars($row['budget_name']) . "</td>
                            <td>" . htmlspecialchars($row['year']) . "</td>
                            <td>" . htmlspecialchars($row['unit_price']) . "</td>
                            <td>" . htmlspecialchars($row['quantity']) . "</td>
                            <td>" . htmlspecialchars($row['reason']) . "</td>
                            <td class='justify-left'>" . htmlspecialchars($row['justification']) . "</td>
                            <td>" . htmlspecialchars($row['remark']) . "</td>
                            <td>";

                        if ($row['status'] === 'Approved') {
                            echo "<span class='badge bg-success'>Approved</span>";
                        } else {
                            echo "<span class='badge bg-warning text-dark'>Pending</span>";
                        }

                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ✅ Print Button -->
<div class="text-end mb-3 no-print">
    <button class="btn btn-primary" onclick="printPage()">Print</button>
</div>

<script>
function printPage() {
    window.print();
}
</script>

<?php include('includes/scripts.php'); ?>
