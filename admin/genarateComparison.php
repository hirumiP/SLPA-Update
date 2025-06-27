<?php
// Include the FPDF library for PDF generation
require('./fpdf/fpdf.php');
include('includes/dbc.php');

// Fetch unique years for the dropdown
$year_query = "SELECT DISTINCT year FROM item_requests ORDER BY year";
$year_result = mysqli_query($connect, $year_query);
if (!$year_result) {
    die("Year query failed: " . mysqli_error($connect));
}

// Handle the form submission
if (isset($_POST['generate_report'])) {
    $selected_year = $_POST['year'];
    $selected_budget_id = $_POST['budget_id'];

    // SQL query without description column
    $sql = "
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
            ir.division
    ";

    $result = mysqli_query($connect, $sql);
    if (!$result) {
        die("Query failed: " . mysqli_error($connect));
    }

    $data = [];
    $division_totals = [];
    $total_budget = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        $total_budget += $row['total_cost'];

        if (!isset($division_totals[$row['division']])) {
            $division_totals[$row['division']] = 0;
        }
        $division_totals[$row['division']] += $row['total_cost'];
    }

    // Generate PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 14);
    $budget_label = $selected_budget_id == '1' ? 'First Round' : 'Revised';
    $pdf->Cell(0, 10, "Yearly Report for $selected_year - $budget_label Budget", 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(8);

    // Table Header
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(200, 220, 255);

    // Table widths adjusted to fit page
$widths = [
    'head_code' => 25,
    'division' => 60,
    'item_name' => 60,
    'quantity' => 20,
    'total_cost' => 25,
];

// Table Headers
$pdf->Cell($widths['head_code'], 6, 'Head of Account Codes', 1, 0, 'C'); 
$pdf->Cell($widths['division'], 6, 'Division', 1, 0, 'C');
$pdf->Cell($widths['item_name'], 6, 'Item Name', 1, 0, 'C');
$pdf->Cell($widths['quantity'], 6, 'Quantity', 1, 0, 'C');
$pdf->Cell($widths['total_cost'], 6, 'Total Cost', 1, 1, 'C');

$pdf->SetFont('Arial', '', 8);  // smaller font for table data
$current_division = '';

foreach ($data as $row) {
    if ($current_division !== $row['division']) {
        if ($current_division !== '') {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(255, 255, 204);
            $pdf->Cell(
                $widths['head_code'] + $widths['division'] + $widths['item_name'] + $widths['quantity'],
                8,
                'Total for ' . utf8_decode($current_division),
                1, 0, 'R', true
            );
            $pdf->Cell($widths['total_cost'], 8, number_format($division_totals[$current_division], 2), 1, 1, 'R', true);
            $pdf->Ln(2);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetFillColor(255, 255, 255);
        }
        $current_division = $row['division'];
    }

    $pdf->Cell($widths['head_code'], 6, '', 1, 0, 'C');
    $pdf->Cell($widths['division'], 6, utf8_decode($row['division']), 1, 0, 'C');
    $pdf->Cell($widths['item_name'], 6, utf8_decode($row['item_name']), 1, 0, 'L');
    $pdf->Cell($widths['quantity'], 6, $row['quantity'], 1, 0, 'C');
    $pdf->Cell($widths['total_cost'], 6, number_format($row['total_cost'], 2), 1, 1, 'R');
}


    // Final division total
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(255, 255, 204);
    $pdf->Cell(
        $widths['head_code'] + $widths['division'] + $widths['item_name'] + $widths['quantity'],
        6,
        'Total for ' . utf8_decode($current_division),
        1, 0, 'R', true
    );
    $pdf->Cell($widths['total_cost'], 6, number_format($division_totals[$current_division], 2), 1, 1, 'R', true);

    // Overall Total
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 6, 'Overall Total Budget: LKR ' . number_format($total_budget, 2), 0, 1, 'R');

    // Output PDF
    $pdf->Output();
}
?>

<!-- HTML Form Section -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Generate Yearly Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
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
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
        }
        form {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.85);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
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
            margin-bottom: 18px;
            color: #333;
        }
        select {
            font-size: 1.3em;
            padding: 12px;
            width: 100%;
            max-width: 360px;
            margin-bottom: 25px;
            border: 2px solid #007bff;
            border-radius: 10px;
            background-color: #e3f2fd;
            color: #333;
        }
        select:focus {
            border-color: #0056b3;
            outline: none;
            box-shadow: 0 0 8px rgba(0, 86, 179, 0.5);
        }
        button {
            font-size: 1.4em;
            padding: 14px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.5);
        }
        button:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }
        button:active {
            transform: translateY(0);
        }
        /* Responsive */
        @media (max-width: 480px) {
            form {
                padding: 35px 20px;
                max-width: 90%;
            }
            label, select, button {
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h1>Generate Yearly Budget Report</h1>
        <label for="year">Select Year:</label>
        <select name="year" id="year" required>
            <option value="" disabled selected>Select Year</option>
            <?php
            while ($year_row = mysqli_fetch_assoc($year_result)) {
                echo '<option value="' . $year_row['year'] . '">' . $year_row['year'] . '</option>';
            }
            ?>
        </select>
        <label for="budget_id">Select Budget Round:</label>
        <select name="budget_id" id="budget_id" required>
            <option value="" disabled selected>Select Budget Round</option>
            <option value="1">First Round</option>
            <option value="2">Revised</option>
        </select>
        <button type="submit" name="generate_report">Generate Report</button>
    </form>
</body>
</html>
