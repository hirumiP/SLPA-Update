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
    <h1 class="mt-4 fw-bold text-primary">SLPA Budget Management System</h1>
    <ol class="breadcrumb mb-4 bg-white shadow-sm rounded py-2 px-3">
        <li class="breadcrumb-item active fs-5">Item Issued</li>
    </ol>

  <?php include('includes/filter.php'); ?>

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
                        $sql = "SELECT * FROM issued_items";
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
