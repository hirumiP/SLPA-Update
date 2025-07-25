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
               (ir.quantity * ir.unit_price) AS total_cost,
               (ir.quantity * ir.unit_price * 1.10) AS estimated_cost
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        WHERE ir.year = '$selected_year' AND ir.budget_id = '$selected_budget_id' AND ir.status = 'Approved'
        ORDER BY ir.division, i.name
    ";
    $result1 = mysqli_query($connect, $sql1) or die("Query 1 failed: " . mysqli_error($connect));
    $data1 = []; $total_budget = 0; $total_estimated1 = 0; $i = 0;
    while ($row = mysqli_fetch_assoc($result1)) {
        $key = 'B1_' . $i++;
        $data1[$key] = [
            'division' => $row['division'],
            'item_code' => $row['item_code'],
            'item_name' => $row['item_name'],
            'quantity' => $row['quantity'],
            'total_cost' => $row['total_cost'],
            'estimated_cost' => $row['estimated_cost']
        ];
        $total_budget += $row['total_cost'];
        $total_estimated1 += $row['estimated_cost'];
    }

    // Budget 2 data
    $data2 = []; $total_budget2 = 0; $total_estimated2 = 0; $i = 0;
    if ($selected_year2 && $selected_budget_id2) {
        $sql2 = "
            SELECT ir.division, ir.item_code, i.name AS item_name, ir.unit_price, ir.quantity,
                   (ir.quantity * ir.unit_price) AS total_cost,
                   (ir.quantity * ir.unit_price * 1.10) AS estimated_cost
            FROM item_requests ir
            LEFT JOIN items i ON ir.item_code = i.item_code
            WHERE ir.year = '$selected_year2' AND ir.budget_id = '$selected_budget_id2'
            ORDER BY ir.division, i.name
        ";
        $result2 = mysqli_query($connect, $sql2) or die("Query 2 failed: " . mysqli_error($connect));
        while ($row = mysqli_fetch_assoc($result2)) {
            $key = 'B2_' . $i++;
            $data2[$key] = [
                'division' => $row['division'],
                'item_code' => $row['item_code'],
                'item_name' => $row['item_name'],
                'quantity' => $row['quantity'],
                'total_cost' => $row['total_cost'],
                'estimated_cost' => $row['estimated_cost']
            ];
            $total_budget2 += $row['total_cost'];
            $total_estimated2 += $row['estimated_cost'];
        }
    }

    // PDF Generation
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, 'SLPA Block Allocation', 0, 1, 'C');
    $pdf->Ln(1);

    // Determine the budget labels
$budget_label1 = $selected_budget_id == '1' ? 'First Round' : ($selected_budget_id == '2' ? 'Revised' : '');
$budget_label2 = $selected_budget_id2 == '1' ? 'First Round' : ($selected_budget_id2 == '2' ? 'Revised' : '');

// Prepare the full line of text
$report_title = " $selected_year - $budget_label1 Budget";
if ($selected_year2 && $selected_budget_id2) {
    $report_title .= "  &   $selected_year2 - $budget_label2 Budget";
}

// Output the full title in one line
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $report_title, 0, 1, 'C');

    $pdf->Ln(3);

    // Table headers
    $widths = [
        'head_code' => 33,
        'description' => 53,
        'qty' => 12,
        'cost1' => 25,
        'est_cost1' => 24,
        'cost2' => 25,
        'est_cost2' => 24
    ];

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell($widths['head_code'], 7, 'Head of Account Codes', 1, 0, 'C', true);
    $pdf->Cell($widths['description'], 7, 'Description', 1, 0, 'C', true);
    $pdf->Cell($widths['qty'], 7, 'Qty', 1, 0, 'C', true);
    $pdf->Cell($widths['cost1'], 7, "$selected_year - $budget_label1", 1, 0, 'C', true);
    $pdf->Cell($widths['est_cost1'], 7, "Est. Cost", 1, 0, 'C', true);
    $pdf->Cell($widths['cost2'], 7, "$selected_year2 - $budget_label2", 1, 0, 'C', true);
    $pdf->Cell($widths['est_cost2'], 7, "Est. Cost", 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 8);
    $current_division = '';
    $merged_rows = [];

    foreach ($data1 as $row) {
        $merged_rows[] = [
            'division' => $row['division'],
            'item_name' => $row['item_name'],
            'quantity' => $row['quantity'],
            'cost1' => $row['total_cost'],
            'est_cost1' => $row['estimated_cost'],
            'cost2' => '',
            'est_cost2' => ''
        ];
    }

    foreach ($data2 as $row) {
        $merged_rows[] = [
            'division' => $row['division'],
            'item_name' => $row['item_name'],
            'quantity' => $row['quantity'],
            'cost1' => '',
            'est_cost1' => '',
            'cost2' => $row['total_cost'],
            'est_cost2' => $row['estimated_cost']
        ];
    }

    usort($merged_rows, function ($a, $b) {
        return [$a['division'], $a['item_name']] <=> [$b['division'], $b['item_name']];
    });

    foreach ($merged_rows as $row) {
        if ($row['division'] !== $current_division) {
            $current_division = $row['division'];
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(
                $widths['head_code'] + $widths['description'] + $widths['qty'],
                8,
                utf8_decode(" $current_division"),
                1, 0, 'L', true
            );
            $pdf->Cell($widths['cost1'] + $widths['est_cost1'], 8, '', 1, 0, 'C', true);
            $pdf->Cell($widths['cost2'] + $widths['est_cost2'], 8, '', 1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 8);
        }

        $pdf->Cell($widths['head_code'], 5, '', 1, 0, 'C');
        $pdf->Cell($widths['description'], 5, utf8_decode($row['item_name']), 1, 0, 'L');
        $pdf->Cell($widths['qty'], 5, $row['quantity'], 1, 0, 'C');
        $pdf->Cell($widths['cost1'], 5, $row['cost1'] ? number_format($row['cost1'], 2) : '', 1, 0, 'R');
        $pdf->Cell($widths['est_cost1'], 5, $row['est_cost1'] ? number_format($row['est_cost1'], 2) : '', 1, 0, 'R');
        $pdf->Cell($widths['cost2'], 5, $row['cost2'] ? number_format($row['cost2'], 2) : '', 1, 0, 'R');
        $pdf->Cell($widths['est_cost2'], 5, $row['est_cost2'] ? number_format($row['est_cost2'], 2) : '', 1, 1, 'R');
    }

    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Output();
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
    td {
  word-wrap: break-word;
  white-space: normal;
  overflow-wrap: break-word;
  max-width: 200px; /* adjust as needed */
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
