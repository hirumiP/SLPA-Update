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
        <li class="breadcrumb-item active fs-5">Items</li>
    </ol>

   <?php include('includes/filter.php'); ?>

    <!-- Data Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Item Code</th>
                            <th scope="col">Category Code</th>
                            <th scope="col">Name</th>
                            <th scope="col">Unit Price</th>
                            <th scope="col">Quantity In Hand</th>
                            <th scope="col">Description</th>
                            <th scope="col">Remark</th>
                            <th scope="col">Created At</th>
                            <th scope="col">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM items";
                        $result = mysqli_query($connect, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $item_code = $row['item_code'];
                                $category_code = $row['category_code'];
                                $name = $row['name'];
                                $unit_price = $row['unit_price'];
                                $qty_in_hand = $row['qty_in_hand'];
                                $description = $row['description'];
                                $remark = $row['remark'];
                                $created_at = $row['created_at'];

                                echo '<tr>
                                    <th scope="row">' . htmlspecialchars($item_code) . '</th>
                                    <td>' . htmlspecialchars($category_code) . '</td>
                                    <td>' . htmlspecialchars($name) . '</td>
                                    <td>' . number_format($unit_price, 2) . '</td>
                                    <td>' . htmlspecialchars($qty_in_hand) . '</td>
                                    <td>' . htmlspecialchars($description) . '</td>
                                    <td>' . htmlspecialchars($remark) . '</td>
                                    <td>' . htmlspecialchars($created_at) . '</td>
                                    <td>
                                        <a href="edit_item.php?id=' . urlencode($item_code) . '" class="btn btn-outline-primary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="9" class="text-muted">No items found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons & FontAwesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .card {
        border-radius: 0.75rem;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        .table {
            font-size: 12px;
        }
    }
</style>

<?php
include('includes/scripts.php');
?>
