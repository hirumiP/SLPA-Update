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
        <li class="breadcrumb-item active">EQ Plan</li>
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
    .custom-btn-group {
        display: flex;
        gap: 10px; /* Add spacing between buttons */
    }

    .btn-custom {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 500;
        color: white;
        text-decoration: none; /* Remove underline */
        transition: background-color 0.3s ease;
    }

    .btn-view-user {
        background-color: #5252f8;
        flex: 1; /* Equal width for both buttons */
    }

    .btn-add-user {
        background-color: #1c1b44;
        flex: 1; /* Equal width for both buttons */
    }

    .btn-custom:hover {
        opacity: 0.9;
    }
    
    .custom-btn {
        background: linear-gradient(45deg, #003366, #00509e);
        color: #ffffff;
        font-weight: bold;
        border: none;
        border-radius: 25px;
        padding: 10px 30px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease-in-out;
    }

    .custom-btn:hover {
        background: linear-gradient(45deg, #00509e, #003366);
        transform: scale(1.05);
    }

    .custom-btn:active {
        transform: scale(0.98);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .col-12.text-end {
        position: absolute;
        right: 10px;
        bottom: 10px;
    }
</style>

</head>
<body>
<div class="d-flex justify-content-center mt-5">
    <div class="custom-btn-group">
        <a href="dashbord_user.php" class="btn btn-custom btn-view-user">
            <i class="bi bi-person"></i> ITEM REQUEST
        </a>
        <a href="eq_view.php" class="btn btn-custom btn-add-user">
            <i class="bi bi-person"></i> EQ PLAN
        </a>
    </div>
</div>
  <br>
    
<div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead style="background-color: #003366; color: #ffffff;">
                <tr>
                    <th>Division</th>
                    <th>Item Name</th>
                    <th>Budget Name</th>
                    <th>Year</th>
                    <th>Approval Quantity</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Description</th>
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT 
                            ir.division, 
                            i.name AS item_name, 
                            b.budget AS budget_name, 
                            ir.year, 
                            ir.approval_qty, 
                            ir.unit_price, 
                            ir.quantity, 
                            ir.reason, 
                            ir.description
                        FROM 
                            item_requests ir
                        LEFT JOIN 
                            items i ON ir.item_code = i.item_code
                        LEFT JOIN 
                            budget b ON ir.budget_id = b.id";

                $result = mysqli_query($connect, $sql);

                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>{$row['division']}</td>
                                <td>{$row['item_name']}</td>
                                <td>{$row['budget_name']}</td>
                                <td>{$row['year']}</td>
                                <td>{$row['approval_qty']}</td>
                                <td>" . number_format($row['unit_price'], 2) . "</td>
                                <td>{$row['quantity']}</td>
                                <td>{$row['reason']}</td>
                                <td>{$row['description']}</td>
                                <td>
                                    <a href=''class='btn btn-sm btn-warning me-1'>
                                        <i class='fas fa-edit'></i>
                                    </a>
                                    <a href='' 
                                       class='btn btn-sm btn-danger' 
                                       onclick='return confirm(\"Are you sure you want to delete this request?\")'>
                                        <i class='fas fa-trash-alt'></i>
                                    </a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
          
    </div>
    </table><div class="col-12 text-end" style="position: relative; margin-top: 20px;">
    <button class="btn btn-primary" onclick="printPage()">Print</button>
</div>
        <br>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      function printPage() {
        window.print();
    }
</script>

</body>
</html>
<?php
include('includes/scripts.php');
?>

