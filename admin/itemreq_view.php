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
    <h1 class="mt-4 fw-bold text-primary">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4 bg-white shadow-sm rounded py-2 px-3">
        <li class="breadcrumb-item active fs-5">Approved Item Requests</li>
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

    <!-- Data Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
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
                                    <td><?= number_format($row['unit_price'], 2) ?></td>
                                    <td class="fw-bold text-success">
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
                            <tr><td colspan="8" class="text-muted">No approved data found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <!-- <div class="text-end mb-3 no-print">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div> -->
</div>

<!-- Optional: Add Bootstrap Icons CDN for icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .table {
            font-size: 12px;
        }
    }
    .card {
        border-radius: 0.75rem;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
</style>

<?php

include('includes/scripts.php');
?>
