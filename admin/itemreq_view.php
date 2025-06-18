<?php
session_start();

// Check if the user is logged in
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
        <li class="breadcrumb-item active">Approved Item Requests</li>
    </ol>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Budget Management</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            @media print {
                .no-print {
                    display: none !important;
                }
            }
        </style>
    </head>
    <body>
        <?php include('includes/filter.php'); ?>
        
        <br>
        <div class="table-container">
            <table class="table table-bordered text-center">
                <thead style="background-color: #003366; color: #ffffff;">
                    <tr>
                        <th scope="col">Division</th>
                        <th scope="col">Item Name</th>
                        <th scope="col">Budget Name</th>
                        <th scope="col">Year</th>
                        <th scope="col">Unit Price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Reason</th>
                        <th scope="col">Justification</th>
                        <th scope="col">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Updated SQL query to show only approved item requests
                    $sql = "SELECT 
                                ir.division, 
                                i.name AS item_name, 
                                b.budget AS budget_name, 
                                ir.year, 
                                ir.unit_price, 
                                ir.quantity, 
                                ir.reason, 
                                ir.description AS justification,
                                ir.remark
                            FROM 
                                item_requests ir
                            LEFT JOIN 
                                items i ON ir.item_code = i.item_code
                            LEFT JOIN 
                                budget b ON ir.budget_id = b.id
                            WHERE 
                                ir.status = 'Approved'";

                    $result = mysqli_query($connect, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "
                            <tr>
                                <td>" . htmlspecialchars($row['division']) . "</td>
                                <td>" . htmlspecialchars($row['item_name']) . "</td>
                                <td>" . htmlspecialchars($row['budget_name']) . "</td>
                                <td>" . htmlspecialchars($row['year']) . "</td>
                                <td>" . htmlspecialchars($row['unit_price']) . "</td>
                                <td>" . htmlspecialchars($row['quantity']) . "</td>
                                <td>" . htmlspecialchars($row['reason']) . "</td>
                                <td>" . htmlspecialchars($row['justification']) . "</td>
                                <td>" . htmlspecialchars($row['remark']) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No approved data found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Print Button -->
        <div class="text-end mb-3 no-print">
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>

    </body>
    </html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
