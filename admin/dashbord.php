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
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .table-container {
            margin-top: 40px;
        }
        .print-button {
            margin-bottom: 10px;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/filter.php'); ?>

    <div class="container mt-4">
        <div class="row g-3">
            <!-- Metric Cards -->
            <!-- (Omitted here for brevity, assuming unchanged) -->
        </div>

        <!-- Approved Requests - Item Wise -->
        <div class="table-container mt-5">
            <h4 class="text-primary">Approved Requests - Item Wise (with Divisions & Totals)</h4>
            <div id="itemWiseTable" class="print-section">
                <table class="table table-bordered text-center">
                    <thead style="background-color: #003366; color: #ffffff;">
                        <tr>
                            <th>Item Name</th>
                            <th>Division</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT i.name AS item_name, ir.division, ir.quantity, ir.unit_price,
                                       (ir.quantity * ir.unit_price) AS total_cost
                                FROM item_requests ir
                                LEFT JOIN items i ON ir.item_code = i.item_code
                                WHERE ir.status = 'Approved'
                                ORDER BY i.name, ir.division";

                        $result = mysqli_query($connect, $sql);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $current_item = '';
                            $item_total_qty = 0;
                            $item_total_cost = 0;

                            while ($row = mysqli_fetch_assoc($result)) {
                                if ($current_item != '' && $row['item_name'] != $current_item) {
                                    echo "<tr style='font-weight:bold; background-color:#e6f2ff'>
                                            <td colspan='2'>Total for {$current_item}</td>
                                            <td>{$item_total_qty}</td>
                                            <td></td>
                                            <td>Rs. " . number_format($item_total_cost, 2) . "</td>
                                          </tr>";
                                    $item_total_qty = 0;
                                    $item_total_cost = 0;
                                }

                                $current_item = $row['item_name'];
                                $item_total_qty += $row['quantity'];
                                $item_total_cost += $row['total_cost'];

                                echo "<tr>
                                        <td>{$row['item_name']}</td>
                                        <td>{$row['division']}</td>
                                        <td>{$row['quantity']}</td>
                                        <td>Rs. " . number_format($row['unit_price'], 2) . "</td>
                                        <td>Rs. " . number_format($row['total_cost'], 2) . "</td>
                                      </tr>";
                            }

                            if ($current_item != '') {
                                echo "<tr style='font-weight:bold; background-color:#e6f2ff'>
                                        <td colspan='2'>Total for {$current_item}</td>
                                        <td>{$item_total_qty}</td>
                                        <td></td>
                                        <td>Rs. " . number_format($item_total_cost, 2) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No approved item-wise data found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- Print Button -->
            <button class="btn btn-sm btn-outline-primary print-button" onclick="printSection('itemWiseTable')">Print Item Wise Table</button>
        </div>

        <!-- Approved Requests - Division Wise -->
        <div class="table-container mt-5">
            <h4 class="text-primary">Approved Requests - Division Wise</h4>
            <div id="divisionWiseTable" class="print-section">
                <table class="table table-bordered text-center">
                    <thead style="background-color: #003366; color: #ffffff;">
                        <tr>
                            <th>Division</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT ir.division, i.name AS item_name, ir.quantity, ir.unit_price,
                                       (ir.quantity * ir.unit_price) AS total_cost
                                FROM item_requests ir
                                LEFT JOIN items i ON ir.item_code = i.item_code
                                WHERE ir.status = 'Approved'
                                ORDER BY ir.division, i.name";

                        $result = mysqli_query($connect, $sql);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $current_division = '';
                            $division_total_cost = 0;
                            $grand_total_cost = 0;

                            while ($row = mysqli_fetch_assoc($result)) {
                                if ($current_division != '' && $row['division'] != $current_division) {
                                    echo "<tr style='font-weight:bold; background-color:#e6f2ff'>
                                            <td colspan='2'>Total for {$current_division}</td>
                                            <td></td>
                                            <td></td>
                                            <td>Rs. " . number_format($division_total_cost, 2) . "</td>
                                          </tr>";
                                    $division_total_cost = 0;
                                }

                                $current_division = $row['division'];
                                $division_total_cost += $row['total_cost'];
                                $grand_total_cost += $row['total_cost'];

                                echo "<tr>
                                        <td>{$row['division']}</td>
                                        <td>{$row['item_name']}</td>
                                        <td>{$row['quantity']}</td>
                                        <td>Rs. " . number_format($row['unit_price'], 2) . "</td>
                                        <td>Rs. " . number_format($row['total_cost'], 2) . "</td>
                                      </tr>";
                            }

                            if ($current_division != '') {
                                echo "<tr style='font-weight:bold; background-color:#e6f2ff'>
                                        <td colspan='2'>Total for {$current_division}</td>
                                        <td></td>
                                        <td></td>
                                        <td>Rs. " . number_format($division_total_cost, 2) . "</td>
                                      </tr>";
                            }

                            echo "<tr style='font-weight:bold; background-color:#d9edf7'>
                                    <td colspan='4'>Grand Total for All Divisions</td>
                                    <td>Rs. " . number_format($grand_total_cost, 2) . "</td>
                                  </tr>";
                        } else {
                            echo "<tr><td colspan='5'>No approved division-wise data found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- Print Button -->
            <button class="btn btn-sm btn-outline-success print-button" onclick="printSection('divisionWiseTable')">Print Division Wise Table</button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Print Function Script -->
    <script>
        function printSection(id) {
            const originalContent = document.body.innerHTML;
            const printContent = document.getElementById(id).innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload(); // reload to restore event listeners
        }
    </script>
</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
