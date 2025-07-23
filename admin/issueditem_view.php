<?php
session_start();

// Check if the user is logged in
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
$yearResult = mysqli_query($connect, "SELECT DISTINCT budget_year FROM issued_items ORDER BY budget_year DESC");
$budgetResult = mysqli_query($connect, "SELECT DISTINCT budget_flag FROM issued_items ORDER BY budget_flag ASC");
$divisionResult = mysqli_query($connect, "SELECT DISTINCT division_name FROM issued_items ORDER BY division_name ASC");

// Build WHERE clause for filters
$where = "1=1";
if (!empty($selectedYear)) {
    $where .= " AND budget_year = '" . mysqli_real_escape_string($connect, $selectedYear) . "'";
}
if (!empty($selectedBudget)) {
    $where .= " AND budget_flag = '" . mysqli_real_escape_string($connect, $selectedBudget) . "'";
}
if (!empty($selectedDivision)) {
    $where .= " AND division_name = '" . mysqli_real_escape_string($connect, $selectedDivision) . "'";
}
?>
<div class="container-fluid px-4">
    <h1 class="mt-4 fw-bold text-primary">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4 bg-white shadow-sm rounded py-2 px-3">
        <li class="breadcrumb-item active fs-5">Item Issued</li>
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
                            <option value="<?= $row['budget_year'] ?>" <?= ($selectedYear == $row['budget_year']) ? 'selected' : '' ?>>
                                <?= $row['budget_year'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Budget Flag Dropdown -->
                <div class="col-md-3">
                    <label for="budget" class="form-label fw-semibold">Budget Flag</label>
                    <select name="budget" id="budget" class="form-select">
                        <option value="">All Budgets</option>
                        <?php
                        mysqli_data_seek($budgetResult, 0);
                        while ($row = mysqli_fetch_assoc($budgetResult)): ?>
                            <option value="<?= $row['budget_flag'] ?>" <?= ($selectedBudget == $row['budget_flag']) ? 'selected' : '' ?>>
                                <?= $row['budget_flag'] ?>
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
                            <option value="<?= $row['division_name'] ?>" <?= ($selectedDivision == $row['division_name']) ? 'selected' : '' ?>>
                                <?= $row['division_name'] ?>
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

    <!-- Issued Items Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Budget Year</th>
                            <th scope="col">Budget Flag</th>
                            <th scope="col">Category Code</th>
                            <th scope="col">Item Code</th>
                            <th scope="col">Division Name</th>
                            <th scope="col">Request Quantity</th>
                            <th scope="col">Issued Quantity</th>
                            <th scope="col">Issuing Quantity</th>
                            <th scope="col">Issue Date</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM issued_items WHERE $where";
                        $result = mysqli_query($connect, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '
                                <tr>
                                    <td>' . htmlspecialchars($row['budget_year']) . '</td>
                                    <td>' . htmlspecialchars($row['budget_flag']) . '</td>
                                    <td>' . htmlspecialchars($row['category_code']) . '</td>
                                    <td>' . htmlspecialchars($row['item_code']) . '</td>
                                    <td>' . htmlspecialchars($row['division_name']) . '</td>
                                    <td>' . htmlspecialchars($row['request_quantity']) . '</td>
                                    <td>' . htmlspecialchars($row['issued_quantity']) . '</td>
                                    <td>' . htmlspecialchars($row['issuing_quantity']) . '</td>
                                    <td>' . htmlspecialchars($row['issue_date']) . '</td>
                                    <td>' . htmlspecialchars($row['remark']) . '</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="10" class="text-muted">No issued items found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons (optional, for future use) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .card {
        border-radius: 0.75rem;
    }
    .card-header {
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }
    .form-label {
        font-size: 1rem;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<?php

include('includes/scripts.php');
?>
