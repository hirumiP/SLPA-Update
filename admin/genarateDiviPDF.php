<?php
// Include the FPDF library for PDF generation
require('./fpdf/fpdf.php');
include('includes/dbc.php');

// Step 1: Fetch all unique divisions for the dropdown
$division_query = "SELECT DISTINCT division FROM item_requests ORDER BY division";
$division_result = mysqli_query($connect, $division_query);
if (!$division_result) {
    die("Division query failed: " . mysqli_error($connect));
}

// Step 2: Fetch all distinct years for the year dropdown
$year_query = "SELECT DISTINCT year FROM item_requests ORDER BY year DESC";
$year_result = mysqli_query($connect, $year_query);
if (!$year_result) {
    die("Year query failed: " . mysqli_error($connect));
}

// Step 3: Handle the form submission
if (isset($_POST['generate_report'])) {
    $selected_division = $_POST['division'];
    $selected_year = $_POST['year'];

    $sql = "
        SELECT 
            ir.division, 
            i.name AS item_name, 
            ir.unit_price, 
            ir.quantity, 
            ir.description, 
            (ir.quantity * ir.unit_price) AS total_cost
        FROM 
            item_requests ir
        LEFT JOIN 
            items i ON ir.item_code = i.item_code
        WHERE 
            ir.division = '$selected_division' AND ir.year = '$selected_year'
        ORDER BY 
            ir.division
    ";

    $result = mysqli_query($connect, $sql);
    if (!$result) {
        die("Query failed: " . mysqli_error($connect));
    }

    $data = [];
    $total_budget = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        $total_budget += $row['total_cost'];
    }

    // Landscape orientation
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management 2025', 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->Cell(0, 10, "Division Report: $selected_division for $selected_year", 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(5);

    // Table Header
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(200, 220, 255);
    $col_widths = [70, 30, 25, 100, 30];
    $row_height = 6;

    $pdf->Cell($col_widths[0], $row_height, 'Item Name', 1, 0, 'C', true);
    $pdf->Cell($col_widths[1], $row_height, 'Unit Price', 1, 0, 'C', true);
    $pdf->Cell($col_widths[2], $row_height, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell($col_widths[3], $row_height, 'Description', 1, 0, 'C', true);
    $pdf->Cell($col_widths[4], $row_height, 'Total Cost', 1, 1, 'C', true);

    // Table Data
    $pdf->SetFont('Arial', '', 9);
    foreach ($data as $row) {
        $pdf->Cell($col_widths[0], $row_height, $row['item_name'], 1);
        $pdf->Cell($col_widths[1], $row_height, number_format($row['unit_price'], 2), 1, 0, 'R');
        $pdf->Cell($col_widths[2], $row_height, $row['quantity'], 1, 0, 'C');
        $pdf->Cell($col_widths[3], $row_height, $row['description'], 1);
        $pdf->Cell($col_widths[4], $row_height, number_format($row['total_cost'], 2), 1, 1, 'R');
    }

    // Total
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, "Total for $selected_division in $selected_year: LKR " . number_format($total_budget, 2), 0, 1, 'R');

    // Footer
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 8, 'End of Report', 0, 1, 'C');

    // Output PDF
    $pdf->Output('Division_Report.pdf', 'I');
    exit;
}
?>

<!-- HTML FORM SECTION -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Division Report Generator</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: url('background.jpg') center/cover no-repeat fixed;
        }
        form {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
        }
        h1 {
            color: #003366;
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #222;
        }
        select, button {
            width: 100%;
            padding: 12px;
            font-size: 1em;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        select:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            transition: background 0.3s ease;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <form method="POST" target="_blank">
        <h1>Generate Division Report</h1>
        <label for="division">Select Division:</label>
        <select name="division" id="division" required>
            <option value="">-- Select Division --</option>
            <?php while ($row = mysqli_fetch_assoc($division_result)) {
                echo "<option value='{$row['division']}'>{$row['division']}</option>";
            } ?>
        </select>

        <label for="year">Select Year:</label>
        <select name="year" id="year" required>
            <option value="">-- Select Year --</option>
            <?php while ($row = mysqli_fetch_assoc($year_result)) {
                echo "<option value='{$row['year']}'>{$row['year']}</option>";
            } ?>
        </select>

        <button type="submit" name="generate_report">Generate Report</button>
    </form>
</body>
</html>
