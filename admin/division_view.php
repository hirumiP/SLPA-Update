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
        <li class="breadcrumb-item active fs-5">Division</li>
    </ol>

   <?php include('includes/filter.php'); ?>

    <!-- Division Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Division Name</th>
                            <th scope="col">Division Code</th>
                            <th scope="col">Division ID</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM divisions";
                        $result = mysqli_query($connect, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '
                                <tr>
                                    <td>' . htmlspecialchars($row['division_name']) . '</td>
                                    <td>' . htmlspecialchars($row['account_code']) . '</td>
                                    <td>' . htmlspecialchars($row['sub_account_code']) . '</td>
                                    <td>' . htmlspecialchars($row['remark']) . '</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-muted">No divisions found.</td></tr>';
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


