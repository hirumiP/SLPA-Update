<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include('includes/dbc.php');

// Filters
$selectedYear = $_GET['year'] ?? '';
$selectedBudget = $_GET['budget'] ?? '';
$selectedDivision = $_GET['division'] ?? '';

// Get filter values
$yearResult = mysqli_query($connect, "SELECT DISTINCT year FROM item_requests ORDER BY year DESC");
$budgetResult = mysqli_query($connect, "SELECT DISTINCT budget FROM budget ORDER BY budget ASC");
$divisionResult = mysqli_query($connect, "SELECT DISTINCT division FROM item_requests ORDER BY division ASC");

// SQL query with filters
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

if (!empty($selectedYear)) {
    $sql .= " AND ir.year = '" . mysqli_real_escape_string($connect, $selectedYear) . "'";
}
if (!empty($selectedBudget)) {
    $sql .= " AND b.budget = '" . mysqli_real_escape_string($connect, $selectedBudget) . "'";
}
if (!empty($selectedDivision)) {
    $sql .= " AND ir.division = '" . mysqli_real_escape_string($connect, $selectedDivision) . "'";
}

$result = mysqli_query($connect, $sql);
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Approved Item Requests</li>
        <?php include('includes/filter.php'); ?>
    </ol>


    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }

        /* Table enhancements */
        table th, table td {
            border: 1px solid black !important;
        }
    </style>

    <!-- ✅ Filter Form -->
    <div class="d-flex justify-content-center mb-4 no-print">
        <form method="GET" class="d-flex flex-wrap align-items-end gap-3">
            <!-- Year Dropdown -->
            <div>
                <label for="year" class="form-label">Year</label>
                <select name="year" id="year" class="form-select" style="min-width: 200px;">
                    <option value="">All Years</option>
                    <?php while ($row = mysqli_fetch_assoc($yearResult)): ?>
                        <option value="<?= $row['year'] ?>" <?= ($selectedYear == $row['year']) ? 'selected' : '' ?>>
                            <?= $row['year'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Budget Type Dropdown -->
            <div>
                <label for="budget" class="form-label">Budget Type</label>
                <select name="budget" id="budget" class="form-select" style="min-width: 200px;">
                    <option value="">All Budgets</option>
                    <?php while ($row = mysqli_fetch_assoc($budgetResult)): ?>
                        <option value="<?= $row['budget'] ?>" <?= ($selectedBudget == $row['budget']) ? 'selected' : '' ?>>
                            <?= $row['budget'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Division Dropdown -->
            <div>
                <label for="division" class="form-label">Division (Optional)</label>
                <select name="division" id="division" class="form-select" style="min-width: 200px;">
                    <option value="">All Divisions</option>
                    <?php while ($row = mysqli_fetch_assoc($divisionResult)): ?>
                        <option value="<?= $row['division'] ?>" <?= ($selectedDivision == $row['division']) ? 'selected' : '' ?>>
                            <?= $row['division'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="btn btn-primary mt-4">Filter</button>
            </div>
        </form>
    </div>

    <!-- ✅ Data Table -->
    <div class="table-responsive">
        <table class="table table-bordered text-center w-100">
            <thead style="background-color: #003366; color: #ffffff;">
                <tr>
                    <th>Division</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Cost</th>
                    <th>Reason</th>
                    <th>Justification</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['division']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td><?= htmlspecialchars($row['unit_price']) ?></td>
                            <td>
                                <?php
                                    $total = floatval($row['unit_price']) * floatval($row['quantity']);
                                    echo number_format($total, 2);
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td><?= htmlspecialchars($row['justification']) ?></td>
                            <td><?= htmlspecialchars($row['remark']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No approved data found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ✅ Print Button -->
    <!-- <div class="text-end mb-3 no-print">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
    </div> -->
</div>

<?php

include('includes/scripts.php');
?>
