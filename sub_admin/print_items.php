<?php
session_start();

if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php');

$loggedDivision = $_SESSION['division'];
$loggedOrganization = "SRI LANKA PORTS AUTHORITY";
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date("Y"); // Get year from GET or default to current

// Fetch years for dropdown
$yearQuery = "SELECT DISTINCT year FROM item_requests ORDER BY year DESC";
$yearResult = mysqli_query($connect, $yearQuery);
$years = [];
while ($row = mysqli_fetch_assoc($yearResult)) {
    $years[] = $row['year'];
}

// Main data query
$sql = "SELECT 
            i.category_code,
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
            ir.division = '$loggedDivision' AND ir.status = 'Approved' AND ir.year = '$selectedYear'
        ORDER BY
            i.category_code";

$result = mysqli_query($connect, $sql);

$item_list = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $item_list[] = $row;
    }
}
?>

<style>
@media print {
    @page {
        margin: 0;
        size: A4 landscape;
    }

    .no-print,
    header, 
    footer, 
    .navbar, 
    .sidebar,
    .filter-form {
        display: none !important;
    }

    .print-only {
        display: flex !important;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 16px !important;
        background: #fff !important;
        color: #000 !important;
    }
    table.table th,
    table.table td {
        font-size: 14px !important;
        color: #000 !important;
    }
}

body {
    margin: 10px;
    font-size: 16px;
    background: #f8f9fa;
}

.table {
    border-collapse: collapse;
    background: #fff;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(13,41,87,0.07);
}

.table th, .table td {
    font-size: 14px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    word-wrap: break-word;
    white-space: normal;
    vertical-align: middle !important;
}

.table th {
    background-color: #0d2957;
    color: #fff;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.table thead tr:nth-child(2) th {
    background: #e9ecef;
    color: #0d2957;
    font-weight: 500;
}

.print-only {
    display: none;
}

@media (max-width: 768px) {
    .table th, .table td {
        font-size: 12px;
        padding: 4px 4px;
    }
}

</style>

<div class="container-fluid px-4 mt-4">
    <!-- Organization and Division Details -->
    <div class="text-center mb-3">
        <h4><?php echo $loggedOrganization; ?></h4>
        <h5>Approved Item Requests - Division: <?php echo $loggedDivision; ?> | Year: <?php echo $selectedYear; ?></h5>
    </div>

    <!-- ✅ Year Filter Dropdown -->
    <form method="GET" class="filter-form no-print mb-3">
        <div style="display: flex; align-items: center; gap: 10px;">
            <label for="year"><strong>Select Year:</strong></label>
            <select name="year" id="year" class="form-select w-auto" onchange="this.form.submit()">
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php if ($selectedYear == $year) echo 'selected'; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <!-- ✅ Print Button -->
    <div class="text-end mb-3 no-print">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>

    <?php if (!empty($item_list)): ?>
        <table class="table table-bordered table-left">
            <thead class="thead-dark">
                <tr>
                    <th>Budget Responsibility Code</th>
                    <th>Cost Centre Code / Location of the Item</th>
                    <th>C.E.P / Budget Number</th>
                    <th>Qty</th>
                    <th>Item Name</th>
                    <th>Total Estimated Cost</th>
                    <th>Allocation Required for <?php echo $selectedYear + 1; ?> Rs.</th>
                    <th>Justification Report</th>
                    <th>Remark</th>
                </tr>
                <tr>
                    <th>[1]</th>
                    <th>[2]</th>
                    <th>[3]</th>
                    <th>[4]</th>
                    <th>[5]</th>
                    <th>[6]</th>
                    <th>[7]</th>
                    <th>[8]</th>
                    <th>[9]</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($item_list as $row): ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><?php echo number_format($row['unit_price'] * $row['quantity'], 2); ?></td>
                        <td></td>
                        <td><?php echo htmlspecialchars($row['justification']); ?></td>
                        <td><?php echo htmlspecialchars($row['remark']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ✅ Note and Signature Section (Print Only) -->
        <div class="print-only" style="margin-top: 10px; flex-direction: column; width: 100%;">
            <div style="margin-bottom: 60px; font-size: 11px;">
                <p>Note: [1] Individual forms should be submitted for C.E.P items, Special Works, Plant & Equipment and Vehicles</p>
                <div style="margin-left: 30px;">
                    <p>[2] Cost Centre Code (Location of the Item) should be clearly indicated in Column No.[2] above for each and every item</p>
                    <p>[3] Column No.[3] above is applicable only for continuation Items</p>
                    <p>[4] A brief but comprehensive Justification report must be included for all items exceeding Rs.500,000/=</p>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; width: 100%;">
                <div style="text-align: center; width: 30%;">
                    ___________________________<br>
                    Prepared By
                </div>
                <div style="text-align: center; width: 30%;">
                    ___________________________<br>
                    Checked By
                </div>
                <div style="text-align: center; width: 30%;">
                    ___________________________<br>
                    Head of Division/Section
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>No approved item requests found for the selected year.</p>
    <?php endif; ?>
</div>

<?php include('includes/scripts.php'); ?>
