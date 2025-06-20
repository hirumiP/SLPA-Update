<?php
session_start();

if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

$loggedDivision = $_SESSION['division'];

$sql = "SELECT 
            ir.request_id,
            ir.division, 
            i.name AS item_name, 
            b.budget AS budget_name, 
            ir.year, 
            ir.approval_qty, 
            ir.unit_price, 
            ir.quantity, 
            ir.reason, 
            ir.description AS justification,
            ir.remark,
            ir.status
        FROM 
            item_requests ir
        LEFT JOIN 
            items i ON ir.item_code = i.item_code
        LEFT JOIN 
            budget b ON ir.budget_id = b.id
        WHERE
            ir.division = '$loggedDivision'";

$result = mysqli_query($connect, $sql);
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<div class="container-fluid px-4">
    <h2 class="text-center mb-4">Item Request Plans (Division: <?php echo $loggedDivision; ?>)</h2>
    <div class="table-container">
        <table class="table table-bordered table-left">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Budget Name</th>
                    <th>Year</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Justification</th>
                    <th>Remark</th>
                    <th>Status</th>
                    <th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "
                        <tr>
                            <td>{$row['item_name']}</td>
                            <td>{$row['budget_name']}</td>
                            <td>{$row['year']}</td>
                            <td>{$row['unit_price']}</td>
                            <td>{$row['quantity']}</td>
                            <td>{$row['reason']}</td>
                            <td>{$row['justification']}</td>
                            <td>{$row['remark']}</td>
                            <td>";

                        if ($row['status'] == 'Approved') {
                            echo "<span class='badge bg-success'>Approved</span>";
                        } else {
                            echo "<span class='badge bg-warning text-dark'>Pending</span>";
                        }

                        echo "</td>
                            <td class='no-print'>
                                <button 
                                    class='btn btn-primary btn-sm'
                                    data-bs-toggle='modal'
                                    data-bs-target='#approveModal'
                                    onclick='fillForm(\"{$row['request_id']}\", \"{$row['unit_price']}\", \"{$row['quantity']}\")'
                                >
                                    Approve
                                </button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ✅ Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="process_item_request.php" method="POST">
          <div class="modal-header">
              <h5 class="modal-title" id="approveModalLabel">Approve Item Request</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="request_id" id="request_id">
              
              <div class="mb-3">
                  <label for="unit_price" class="form-label">Unit Price</label>
                  <input type="text" class="form-control" id="unit_price" name="unit_price" required>
              </div>
              <div class="mb-3">
                  <label for="quantity" class="form-label">Quantity</label>
                  <input type="text" class="form-control" id="quantity" name="quantity" required>
              </div>
          </div>
          <div class="modal-footer">
              <button type="submit" name="approve" class="btn btn-success">Approve Item Request</button>
              <button type="submit" name="delete" class="btn btn-danger">Delete Item Request</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function printPage() {
    window.print();
}

// ✅ Fills modal with selected request data
function fillForm(id, unit_price, quantity) {
    document.getElementById('request_id').value = id;
    document.getElementById('unit_price').value = unit_price;
    document.getElementById('quantity').value = quantity;
}
</script>

<?php include('includes/scripts.php'); ?>
