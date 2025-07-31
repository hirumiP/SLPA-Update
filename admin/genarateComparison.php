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

    // Budget 1 data - Include justification
    $sql1 = "
        SELECT ir.division, ir.item_code, i.name AS item_name, ir.unit_price, ir.quantity,
               ir.description AS justification,
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
            'justification' => $row['justification'],
            'total_cost' => $row['total_cost'],
            'estimated_cost' => $row['estimated_cost']
        ];
        $total_budget += $row['total_cost'];
        $total_estimated1 += $row['estimated_cost'];
    }

    // Budget 2 data - Include justification
    $data2 = []; $total_budget2 = 0; $total_estimated2 = 0; $i = 0;
    if ($selected_year2 && $selected_budget_id2) {
        $sql2 = "
            SELECT ir.division, ir.item_code, i.name AS item_name, ir.unit_price, ir.quantity,
                   ir.description AS justification,
                   (ir.quantity * ir.unit_price) AS total_cost,
                   (ir.quantity * ir.unit_price * 1.10) AS estimated_cost
            FROM item_requests ir
            LEFT JOIN items i ON ir.item_code = i.item_code
            WHERE ir.year = '$selected_year2' AND ir.budget_id = '$selected_budget_id2' AND ir.status = 'Approved'
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
                'justification' => $row['justification'],
                'total_cost' => $row['total_cost'],
                'estimated_cost' => $row['estimated_cost']
            ];
            $total_budget2 += $row['total_cost'];
            $total_estimated2 += $row['estimated_cost'];
        }
    }

    // PDF Generation
    $pdf = new FPDF('L', 'mm', 'A4'); // Changed to Landscape for more space
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, 'SLPA Block Allocation Comparison', 0, 1, 'C');
    $pdf->Ln(1);

    // Determine the budget labels
    $budget_label1 = $selected_budget_id == '1' ? 'First Round' : ($selected_budget_id == '2' ? 'Revised' : '');
    $budget_label2 = $selected_budget_id2 == '1' ? 'First Round' : ($selected_budget_id2 == '2' ? 'Revised' : '');

    // Prepare the full line of text
    $report_title = " $selected_year - $budget_label1 Budget";
    if ($selected_year2 && $selected_budget_id2) {
        $report_title .= "  vs  $selected_year2 - $budget_label2 Budget";
    }

    // Output the full title in one line
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, $report_title, 0, 1, 'C');
    $pdf->Ln(3);

    // Updated table headers with separate columns for each budget
    $widths = [
        'head_code' => 25,
        'description' => 40,
        'qty1' => 15,
        'justification1' => 35,
        'cost1' => 20,
        'est_cost1' => 20,
        'qty2' => 15,
        'justification2' => 35,
        'cost2' => 20,
        'est_cost2' => 20
    ];

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetFillColor(200, 220, 255);
    
    // First header row
    $pdf->Cell($widths['head_code'], 14, 'Head Code', 1, 0, 'C', true);
    $pdf->Cell($widths['description'], 14, 'Item Description', 1, 0, 'C', true);
    
    // Budget 1 header group
    $budget1_width = $widths['qty1'] + $widths['justification1'] + $widths['cost1'] + $widths['est_cost1'];
    $pdf->Cell($budget1_width, 7, "$selected_year - $budget_label1", 1, 0, 'C', true);
    
    // Budget 2 header group (if exists)
    if ($selected_year2 && $selected_budget_id2) {
        $budget2_width = $widths['qty2'] + $widths['justification2'] + $widths['cost2'] + $widths['est_cost2'];
        $pdf->Cell($budget2_width, 7, "$selected_year2 - $budget_label2", 1, 1, 'C', true);
    } else {
        $pdf->Ln();
    }
    
    // Second header row - subheadings
    $pdf->Cell($widths['head_code'], 7, '', 0, 0, 'C');
    $pdf->Cell($widths['description'], 7, '', 0, 0, 'C');
    
    // Budget 1 subheadings
    $pdf->Cell($widths['qty1'], 7, 'Qty', 1, 0, 'C', true);
    $pdf->Cell($widths['justification1'], 7, 'Justification', 1, 0, 'C', true);
    $pdf->Cell($widths['cost1'], 7, 'Cost', 1, 0, 'C', true);
    $pdf->Cell($widths['est_cost1'], 7, 'Est. Cost', 1, 0, 'C', true);
    
    // Budget 2 subheadings (if exists)
    if ($selected_year2 && $selected_budget_id2) {
        $pdf->Cell($widths['qty2'], 7, 'Qty', 1, 0, 'C', true);
        $pdf->Cell($widths['justification2'], 7, 'Justification', 1, 0, 'C', true);
        $pdf->Cell($widths['cost2'], 7, 'Cost', 1, 0, 'C', true);
        $pdf->Cell($widths['est_cost2'], 7, 'Est. Cost', 1, 1, 'C', true);
    } else {
        $pdf->Ln();
    }

    $pdf->SetFont('Arial', '', 7);
    $current_division = '';
    
    // Create merged data structure to show items side by side
    $all_items = [];
    
    // Add all items from both budgets
    foreach ($data1 as $row) {
        $key = $row['division'] . '|' . $row['item_name'];
        $all_items[$key]['division'] = $row['division'];
        $all_items[$key]['item_name'] = $row['item_name'];
        $all_items[$key]['budget1'] = $row;
    }
    
    foreach ($data2 as $row) {
        $key = $row['division'] . '|' . $row['item_name'];
        if (!isset($all_items[$key])) {
            $all_items[$key]['division'] = $row['division'];
            $all_items[$key]['item_name'] = $row['item_name'];
        }
        $all_items[$key]['budget2'] = $row;
    }
    
    // Sort by division and item name
    ksort($all_items);

    foreach ($all_items as $item) {
        // Division header
        if ($item['division'] !== $current_division) {
            $current_division = $item['division'];
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(230, 230, 230);
            $total_width = array_sum($widths);
            $pdf->Cell($total_width, 8, utf8_decode(" $current_division"), 1, 1, 'L', true);
            $pdf->SetFont('Arial', '', 7);
        }

        // Item row
        $pdf->Cell($widths['head_code'], 10, '', 1, 0, 'C');
        $pdf->Cell($widths['description'], 10, utf8_decode(substr($item['item_name'], 0, 25)), 1, 0, 'L');
        
        // Budget 1 data
        if (isset($item['budget1'])) {
            $b1 = $item['budget1'];
            $pdf->Cell($widths['qty1'], 10, $b1['quantity'], 1, 0, 'C');
            $pdf->Cell($widths['justification1'], 10, utf8_decode(substr($b1['justification'], 0, 20)), 1, 0, 'L');
            $pdf->Cell($widths['cost1'], 10, number_format($b1['total_cost'], 0), 1, 0, 'R');
            $pdf->Cell($widths['est_cost1'], 10, number_format($b1['estimated_cost'], 0), 1, 0, 'R');
        } else {
            $pdf->Cell($widths['qty1'], 10, '-', 1, 0, 'C');
            $pdf->Cell($widths['justification1'], 10, '-', 1, 0, 'C');
            $pdf->Cell($widths['cost1'], 10, '-', 1, 0, 'C');
            $pdf->Cell($widths['est_cost1'], 10, '-', 1, 0, 'C');
        }
        
        // Budget 2 data (if exists)
        if ($selected_year2 && $selected_budget_id2) {
            if (isset($item['budget2'])) {
                $b2 = $item['budget2'];
                $pdf->Cell($widths['qty2'], 10, $b2['quantity'], 1, 0, 'C');
                $pdf->Cell($widths['justification2'], 10, utf8_decode(substr($b2['justification'], 0, 20)), 1, 0, 'L');
                $pdf->Cell($widths['cost2'], 10, number_format($b2['total_cost'], 0), 1, 0, 'R');
                $pdf->Cell($widths['est_cost2'], 10, number_format($b2['estimated_cost'], 0), 1, 1, 'R');
            } else {
                $pdf->Cell($widths['qty2'], 10, '-', 1, 0, 'C');
                $pdf->Cell($widths['justification2'], 10, '-', 1, 0, 'C');
                $pdf->Cell($widths['cost2'], 10, '-', 1, 0, 'C');
                $pdf->Cell($widths['est_cost2'], 10, '-', 1, 1, 'C');
            }
        } else {
            $pdf->Ln();
        }
    }

    // Remove all the summary section code and just output the PDF
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
