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
.bg-light-red {
    background-color: #f8d7da !important;
}
.bg-light-green {
    background-color: #d4edda !important;
}
.table th, .table td {
    border: 1px solid #dee2e6 !important;
    vertical-align: middle !important;
    font-size: 1rem;
}
.table th {
    background-color: #0d2957;
    color: #fff;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.table {
    border-collapse: collapse;
    background: #fff;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(13,41,87,0.07);
}
.badge.bg-success, .badge.bg-warning {
    font-size: 1em;
    padding: 0.5em 1em;
    border-radius: 0.5em;
}
.btn-primary.btn-sm {
    background: #0d6efd;
    border: none;
    font-weight: 500;
}
.btn-primary.btn-sm:hover {
    background: #0b5ed7;
}
.btn-success, .btn-danger {
    min-width: 140px;
    font-weight: 500;
}
.modal-content {
    border-radius: 0.75rem;
}
.modal-header {
    background: #0d2957;
    color: #fff;
    border-bottom: 1px solid #dee2e6;
}
.modal-title {
    font-weight: 600;
    letter-spacing: 0.5px;
}
.form-label {
    font-weight: 500;
}
.alert {
    font-size: 1rem;
}
.table-container {
    margin-top: 2rem;
}
@media (max-width: 768px) {
    .table th, .table td {
        font-size: 0.95rem;
    }
    .btn-success, .btn-danger {
        min-width: 100px;
        font-size: 0.95rem;
    }
}
</style>

<div class="container-fluid px-4">
    <h2 class="text-center mb-4 fw-bold text-primary">Item Requests - Division: <?php echo htmlspecialchars($loggedDivision); ?></h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'approved'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> Item request approved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle-fill"></i> Item request deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table class="table table-bordered table-hover align-middle text-center shadow-sm">
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
                        $rowClass = ($row['status'] == 'Approved') ? 'bg-light-green' : 'bg-light-red';
                        ?>
                        <tr class="<?= $rowClass; ?>">
                            <td><?= htmlspecialchars($row['item_name']); ?></td>
                            <td><?= htmlspecialchars($row['budget_name']); ?></td>
                            <td><?= htmlspecialchars($row['year']); ?></td>
                            <td><?= htmlspecialchars($row['unit_price']); ?></td>
                            <td><?= htmlspecialchars($row['quantity']); ?></td>
                            <td><?= htmlspecialchars($row['reason']); ?></td>
                            <td><?= htmlspecialchars($row['justification']); ?></td>
                            <td><?= htmlspecialchars($row['remark']); ?></td>
                            <td>
                                <?php if ($row['status'] == 'Approved'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="no-print">
                                <?php if ($row['status'] !== 'Approved'): ?>
                                    <button 
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#approveModal"
                                        onclick="fillForm('<?= $row['request_id']; ?>', '<?= $row['unit_price']; ?>', '<?= $row['quantity']; ?>')"
                                    >
                                        <i class="bi bi-check2-square"></i> Approve
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
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
              <h5 class="modal-title" id="approveModalLabel"><i class="bi bi-check2-square"></i> Approve Item Request</h5>
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
              <button type="submit" name="approve" class="btn btn-success">
                  <i class="bi bi-check-circle"></i> Approve Item Request
              </button>
              <button type="submit" name="delete" class="btn btn-danger">
                  <i class="bi bi-trash"></i> Delete Item Request
              </button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function fillForm(id, unit_price, quantity) {
    document.getElementById('request_id').value = id;
    document.getElementById('unit_price').value = unit_price;
    document.getElementById('quantity').value = quantity;
}
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php include('includes/scripts.php');
