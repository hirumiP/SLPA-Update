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

    // New inputs for second budget (optional)
    $selected_year2 = $_POST['year2'] ?? null;
    $selected_budget_id2 = $_POST['budget_id2'] ?? null;

    // Fetch first budget data
    $sql1 = "
        SELECT 
            ir.division, 
            i.name AS item_name, 
            ir.quantity, 
            ir.reason, 
            (ir.quantity * ir.unit_price) AS total_cost
        FROM 
            item_requests ir
        LEFT JOIN 
            items i ON ir.item_code = i.item_code
        WHERE 
            ir.year = '$selected_year' AND 
            ir.budget_id = '$selected_budget_id'
        ORDER BY 
            ir.division, i.name
    ";
    $result1 = mysqli_query($connect, $sql1);
    if (!$result1) {
        die("Query failed: " . mysqli_error($connect));
    }

    $data1 = [];
    $total_budget = 0;
    while ($row = mysqli_fetch_assoc($result1)) {
        $key = $row['division'] . '||' . $row['item_name'];
        $data1[$key] = $row;
        $total_budget += $row['total_cost'];
    }

    $data2 = [];
    $total_budget2 = 0;
    if ($selected_year2 && $selected_budget_id2) {
        $sql2 = "
            SELECT 
                ir.division, 
                i.name AS item_name, 
                ir.quantity, 
                ir.reason, 
                (ir.quantity * ir.unit_price) AS total_cost
            FROM 
                item_requests ir
            LEFT JOIN 
                items i ON ir.item_code = i.item_code
            WHERE 
                ir.year = '$selected_year2' AND 
                ir.budget_id = '$selected_budget_id2'
            ORDER BY 
                ir.division, i.name
        ";
        $result2 = mysqli_query($connect, $sql2);
        if (!$result2) {
            die("Query failed: " . mysqli_error($connect));
        }

        while ($row = mysqli_fetch_assoc($result2)) {
            $key = $row['division'] . '||' . $row['item_name'];
            $data2[$key] = $row;
            $total_budget2 += $row['total_cost'];
        }
    }

    // PDF Generation
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management', 0, 1, 'C');
    $pdf->Ln(5);

    // Budget labels
    $budget_label1 = ($selected_budget_id == '1') ? 'First Round' : (($selected_budget_id == '2') ? 'Revised' : '');
    $budget_label2 = ($selected_budget_id2 == '1') ? 'First Round' : (($selected_budget_id2 == '2') ? 'Revised' : '');

    // Report headers
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, "Yearly Report for $selected_year - $budget_label1 Budget", 0, 1, 'C');
    if ($selected_year2 && $selected_budget_id2) {
        $pdf->Cell(0, 10, "Compared with $selected_year2 - $budget_label2 Budget", 0, 1, 'C');
    }
    $pdf->Ln(5);

    // Generated on
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(8);

    // Table column widths
    $widths = [
        'head_code'   => 40,
        'description' => 60,
        'cost1'       => 30,
        'cost2'       => 30,
    ];

    // Header row
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell($widths['head_code'], 6, 'Head of Account Codes', 1, 0, 'C', true);
    $pdf->Cell($widths['description'], 6, 'Description (Item Names)', 1, 0, 'C', true);

    // Dynamic cost headers
    $header_cost1 = "$selected_year - $budget_label1";
    $pdf->Cell($widths['cost1'], 6, $header_cost1, 1, 0, 'C', true);

    if ($selected_year2 && $selected_budget_id2) {
        $header_cost2 = " $selected_year2 - $budget_label2";
        $pdf->Cell($widths['cost2'], 6, $header_cost2, 1, 1, 'C', true);
    } else {
        $pdf->Cell($widths['cost2'], 6, 'Total Cost (N/A)', 1, 1, 'C', true);
    }

    $pdf->SetFont('Arial', '', 8);
    $current_division = '';

    // Combine keys from both budgets to cover all items
    $all_keys = array_unique(array_merge(array_keys($data1), array_keys($data2)));

    foreach ($all_keys as $key) {
        list($division, $item_name) = explode('||', $key);

        // Print division row if division changes
        if ($current_division !== $division) {
            $current_division = $division;

            // Division title row spans only head_code + description columns
            $span_width = $widths['head_code'] + $widths['description'];

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell($span_width, 8, utf8_decode(" " . $current_division), 1, 0, 'L', true);

            // Empty cells for cost columns to fill the row
            $pdf->Cell($widths['cost1'], 8, '', 1, 0, 'C', true);
            $pdf->Cell($widths['cost2'], 8, '', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 8);
        }

        // Data for budget 1
        $row1 = $data1[$key] ?? null;
        $qty1 = $row1 ? $row1['quantity'] : 0;
        $cost1 = $row1 ? number_format($row1['total_cost'], 2) : '0.00';

        // Data for budget 2
        $row2 = $data2[$key] ?? null;
        $cost2 = $row2 ? number_format($row2['total_cost'], 2) : '0.00';

        $formatted_item = str_pad($qty1, 2, '0', STR_PAD_LEFT) . ' - ' . utf8_decode($item_name);

        $pdf->Cell($widths['head_code'], 6, '', 1, 0, 'C');
        $pdf->Cell($widths['description'], 6, $formatted_item, 1, 0, 'L');
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Generate Yearly Report</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0; padding: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: url('background.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }
    h1 {
        font-size: 2.8em;
        margin-bottom: 20px;
        color: #003366;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
    }
    form {
        text-align: center;
        background-color: rgba(255,255,255,0.85);
        padding: 50px;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 420px;
        animation: fadeIn 1s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-40px); }
        to { opacity: 1; transform: translateY(0); }
    }
    label {
        font-size: 1.4em;
        display: block;
        margin-bottom: 15px;
        color: #0055aa;
        font-weight: 700;
        text-align: left;
    }
    select {
        font-size: 1.1em;
        padding: 10px 15px;
        width: 100%;
        margin-bottom: 30px;
        border: 2px solid #0055aa;
        border-radius: 12px;
        transition: box-shadow 0.3s ease;
    }
    select:focus {
        outline: none;
        box-shadow: 0 0 10px #0055aa;
    }
    input[type="submit"] {
        font-size: 1.4em;
        font-weight: 700;
        background: linear-gradient(90deg, #0066cc, #004a99);
        color: white;
        padding: 12px 25px;
        border-radius: 25px;
        border: none;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(0,102,204,0.45);
        transition: background 0.3s ease;
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
    }
    input[type="submit"]:hover {
        background: linear-gradient(90deg, #004a99, #0066cc);
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
        if ($year_result) {
            mysqli_data_seek($year_result, 0); // Reset pointer for reuse
            while ($row = mysqli_fetch_assoc($year_result)) {
                echo '<option value="'.htmlspecialchars($row['year']).'">'.htmlspecialchars($row['year']).'</option>';
            }
        }
        ?>
    </select>

    <label for="budget_id">Select Budget 1</label>
    <select name="budget_id" id="budget_id" required>
        <option value="" disabled selected>-- Select Budget --</option>
        <option value="1">First Round Budget</option>
        <option value="2">Revised Budget</option>
    </select>

    <hr style="margin: 40px 0; border: 1px solid #ccc;">

    <label for="year2">Select Year 2 (Optional)</label>
    <select name="year2" id="year2">
        <option value="" selected>-- None --</option>
        <?php
        if ($year_result) {
            mysqli_data_seek($year_result, 0);
            while ($row = mysqli_fetch_assoc($year_result)) {
                echo '<option value="'.htmlspecialchars($row['year']).'">'.htmlspecialchars($row['year']).'</option>';
            }
        }
        ?>
    </select>

    <label for="budget_id2">Select Budget 2 (Optional)</label>
    <select name="budget_id2" id="budget_id2">
        <option value="" selected>-- None --</option>
        <option value="1">First Round Budget</option>
        <option value="2">Revised Budget</option>
    </select>

    <input type="submit" name="generate_report" value="Generate Report" />
</form>
</body>
</html>
