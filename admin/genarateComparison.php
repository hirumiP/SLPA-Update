<?php
require('./fpdf/fpdf.php');
include('includes/dbc.php');

// Fetch years for dropdown
$year_query = "SELECT DISTINCT year FROM item_requests ORDER BY year";
$year_result = mysqli_query($connect, $year_query);
if (!$year_result) {
    die("Year query failed: " . mysqli_error($connect));
}

if (isset($_POST['generate_report'])) {
    $selected_year = $_POST['year'];
    $selected_budget_id = $_POST['budget_id'];
    $selected_year2 = $_POST['year2'] ?? null;
    $selected_budget_id2 = $_POST['budget_id2'] ?? null;

    // Budget 1 data
    $sql1 = "
        SELECT ir.division, ir.item_code, i.name AS item_name, ir.unit_price, ir.quantity,
               (ir.quantity * ir.unit_price) AS total_cost
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        WHERE ir.year = '$selected_year' AND ir.budget_id = '$selected_budget_id'
        ORDER BY ir.division, i.name
    ";
    $result1 = mysqli_query($connect, $sql1) or die("Query 1 failed: " . mysqli_error($connect));
    $data1 = []; $total_budget = 0;
    while ($row = mysqli_fetch_assoc($result1)) {
        $key = $row['division'] . '||' . $row['item_code'] . '||' . $row['unit_price'];
        if (!isset($data1[$key])) {
            $data1[$key] = [
                'item_name' => $row['item_name'],
                'unit_price' => $row['unit_price'],
                'quantity' => 0,
                'total_cost' => 0
            ];
        }
        $data1[$key]['quantity'] += $row['quantity'];
        $data1[$key]['total_cost'] += $row['total_cost'];
        $total_budget += $row['total_cost'];
    }

    // Budget 2 data
    $data2 = []; $total_budget2 = 0;
    if ($selected_year2 && $selected_budget_id2) {
        $sql2 = "
            SELECT ir.division, ir.item_code, i.name AS item_name, ir.unit_price, ir.quantity,
                   (ir.quantity * ir.unit_price) AS total_cost
            FROM item_requests ir
            LEFT JOIN items i ON ir.item_code = i.item_code
            WHERE ir.year = '$selected_year2' AND ir.budget_id = '$selected_budget_id2'
            ORDER BY ir.division, i.name
        ";
        $result2 = mysqli_query($connect, $sql2) or die("Query 2 failed: " . mysqli_error($connect));
        while ($row = mysqli_fetch_assoc($result2)) {
            $key = $row['division'] . '||' . $row['item_code'] . '||' . $row['unit_price'];
            if (!isset($data2[$key])) {
                $data2[$key] = [
                    'item_name' => $row['item_name'],
                    'unit_price' => $row['unit_price'],
                    'quantity' => 0,
                    'total_cost' => 0
                ];
            }
            $data2[$key]['quantity'] += $row['quantity'];
            $data2[$key]['total_cost'] += $row['total_cost'];
            $total_budget2 += $row['total_cost'];
        }
    }

    // PDF Generation
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Title and metadata
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management', 0, 1, 'C');
    $pdf->Ln(5);

    $budget_label1 = $selected_budget_id == '1' ? 'First Round' : ($selected_budget_id == '2' ? 'Revised' : '');
    $budget_label2 = $selected_budget_id2 == '1' ? 'First Round' : ($selected_budget_id2 == '2' ? 'Revised' : '');

    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, "Yearly Report for $selected_year - $budget_label1 Budget", 0, 1, 'C');
    if ($selected_year2 && $selected_budget_id2) {
        $pdf->Cell(0, 10, "Compared with $selected_year2 - $budget_label2 Budget", 0, 1, 'C');
    }
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(8);

    // Table headers
    $widths = [
        'head_code' => 34,
        'description' => 45,
        'qty1' => 15,
        'qty2' => 15,
        'cost1' => 30,
        'cost2' => 30
    ];

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell($widths['head_code'], 6, 'Head of Account Codes', 1, 0, 'C', true);
    $pdf->Cell($widths['description'], 6, 'Description', 1, 0, 'C', true);
    $pdf->Cell($widths['qty1'], 6, "Qty $selected_year", 1, 0, 'C', true);
    $pdf->Cell($widths['qty2'], 6, "Qty $selected_year2", 1, 0, 'C', true);
    $pdf->Cell($widths['cost1'], 6, "$selected_year - $budget_label1", 1, 0, 'C', true);
    $pdf->Cell($widths['cost2'], 6, "$selected_year2 - $budget_label2", 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 8);
    $current_division = '';
    $all_keys = array_unique(array_merge(array_keys($data1), array_keys($data2)));
    sort($all_keys);

    foreach ($all_keys as $key) {
    list($division, $item_code, $unit_price) = explode('||', $key);

    $row1 = $data1[$key] ?? null;
    $row2 = $data2[$key] ?? null;

    $item_name = $row1['item_name'] ?? $row2['item_name'] ?? 'Unknown';

    // Show empty if quantity is zero or null
    $qty1 = (!empty($row1) && $row1['quantity'] > 0) ? $row1['quantity'] : '';
    $qty2 = (!empty($row2) && $row2['quantity'] > 0) ? $row2['quantity'] : '';

    $cost1 = $row1 ? number_format($row1['total_cost'], 2) : '';
    $cost2 = $row2 ? number_format($row2['total_cost'], 2) : '';

    if ($current_division !== $division) {
        $current_division = $division;
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(
            $widths['head_code'] + $widths['description'] + $widths['qty1'] + $widths['qty2'],
            8,
            utf8_decode(" $current_division"),
            1, 0, 'L', true
        );
        $pdf->Cell($widths['cost1'], 8, '', 1, 0, 'C', true);
        $pdf->Cell($widths['cost2'], 8, '', 1, 1, 'C', true);
        $pdf->SetFont('Arial', '', 8);
    }

    $formatted_item = utf8_decode($item_name);
    $pdf->Cell($widths['head_code'], 6, '', 1, 0, 'C');
    $pdf->Cell($widths['description'], 6, $formatted_item, 1, 0, 'L');
    $pdf->Cell($widths['qty1'], 6, $qty1, 1, 0, 'C');
    $pdf->Cell($widths['qty2'], 6, $qty2, 1, 0, 'C');
    $pdf->Cell($widths['cost1'], 6, $cost1, 1, 0, 'R');
    $pdf->Cell($widths['cost2'], 6, $cost2, 1, 1, 'R');
}


    // Totals
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 6, 'Overall Total Budget (Budget 1): LKR ' . number_format($total_budget, 2), 0, 1, 'R');
    if ($selected_year2 && $selected_budget_id2) {
        $pdf->Cell(0, 6, 'Overall Total Budget (Budget 2): LKR ' . number_format($total_budget2, 2), 0, 1, 'R');
    }

    $pdf->Output();
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Generate Yearly Report</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #eef2f3 url('background.jpg') no-repeat center center fixed;
        background-size: cover;
        display: flex; align-items: center; justify-content: center;
        height: 100vh; margin: 0;
    }
    form {
        background-color: rgba(255,255,255,0.95);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        max-width: 500px;
        width: 90%;
    }
    h1 {
        text-align: center;
        color: #003366;
        margin-bottom: 25px;
    }
    label {
        display: block;
        font-weight: bold;
        margin: 15px 0 5px;
        color: #0055aa;
    }
    select, input[type="submit"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        font-size: 1em;
        border-radius: 10px;
        border: 2px solid #0055aa;
    }
    input[type="submit"] {
        background: linear-gradient(to right, #0066cc, #004a99);
        color: white;
        border: none;
        font-weight: bold;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background: linear-gradient(to right, #004a99, #0066cc);
    }
</style>
</head>
<body>
<form action="" method="post" autocomplete="off">
    <h1>Generate Yearly Report</h1>
    
    <label for="year">Select Year 1</label>
    <select name="year" id="year" required>
        <option value="" disabled selected>-- Select Year --</option>
        <?php
        mysqli_data_seek($year_result, 0);
        while ($row = mysqli_fetch_assoc($year_result)) {
            echo '<option value="' . htmlspecialchars($row['year']) . '">' . htmlspecialchars($row['year']) . '</option>';
        }
        ?>
    </select>

    <label for="budget_id">Select Budget 1</label>
    <select name="budget_id" id="budget_id" required>
        <option value="" disabled selected>-- Select Budget --</option>
        <option value="1">First Round</option>
        <option value="2">Revised</option>
    </select>

    <label for="year2">Select Year 2 (optional)</label>
    <select name="year2" id="year2">
        <option value="">-- Select Year --</option>
        <?php
        mysqli_data_seek($year_result, 0);
        while ($row = mysqli_fetch_assoc($year_result)) {
            echo '<option value="' . htmlspecialchars($row['year']) . '">' . htmlspecialchars($row['year']) . '</option>';
        }
        ?>
    </select>

    <label for="budget_id2">Select Budget 2 (optional)</label>
    <select name="budget_id2" id="budget_id2">
        <option value="">-- Select Budget --</option>
        <option value="1">First Round</option>
        <option value="2">Revised</option>
    </select>

    <input type="submit" name="generate_report" value="Generate Report">
</form>
</body>
</html>
