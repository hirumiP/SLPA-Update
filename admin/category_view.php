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
        <li class="breadcrumb-item active fs-5">Category</li>
    </ol>

<?php include('includes/filter.php'); ?>

    <!-- Category Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Category Code</th>
                            <th scope="col">Description</th>
                            <th scope="col">Remark</th>
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM categories";
                        $result = mysqli_query($connect, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>
                                    <td>' . htmlspecialchars($row['category_code']) . '</td>
                                    <td>' . htmlspecialchars($row['description']) . '</td>
                                    <td>' . htmlspecialchars($row['remark']) . '</td>
                                    <td>' . htmlspecialchars($row['created_at']) . '</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-muted">No categories found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons (optional, for future icons/buttons) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .card {
        border-radius: 0.75rem;
    }
    .card-header {
        font-size: 1.1rem;
        font-weight: bold;
        color: #003366;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table thead th {
        background-color: #003366;
        color: white;
    }
    .table tbody tr:hover {
        background-color: #f1f1f1;
    }
</style>
<?php
include('includes/scripts.php');
?>
