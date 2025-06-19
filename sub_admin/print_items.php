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
/* ... your existing styles ... */

@media print {
    @page {
        margin: 0;
        size: A4 portrait;
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
        font-size: 13px;
    }
}


body {
    margin: 10px;
    font-size: 13px;
}

table.table th,
table.table td {
    font-size: 11px;
    padding: 4px 5px;
    border: 1px solid #ccc;
    word-wrap: break-word;
    white-space: normal;
}

/* Apply specific minimum widths to reduce space */
table.table th:nth-child(1),
table.table td:nth-child(1) { width: 50px; }

table.table th:nth-child(2),
table.table td:nth-child(2) { width: 50px; }

table.table th:nth-child(3),
table.table td:nth-child(3) { width: 50px; }

table.table th:nth-child(4),
table.table td:nth-child(4) { width: 40px; }

table.table th:nth-child(5),
table.table td:nth-child(5) { width: 150px; }

table.table th:nth-child(6),
table.table td:nth-child(6) { width: 80px; }

table.table th:nth-child(7),
table.table td:nth-child(7) { width: 80px; }

table.table th:nth-child(8),
table.table td:nth-child(8) { width: 200px; }

table.table th:nth-child(9),
table.table td:nth-child(9) { width: 100px; }


table.table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
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
        <button class="btn btn-primary" onclick="window.print()">Print</button>
    </div>

    <?php if (!empty($item_list)): ?>
        <table class="table table-bordered text-center">
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
