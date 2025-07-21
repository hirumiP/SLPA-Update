<?php
// Include the FPDF library for PDF generation
require('./fpdf/fpdf.php');
include('includes/dbc.php');

// Fetch unique years for the dropdown from the 'year' column
$year_query = "SELECT DISTINCT year FROM item_requests ORDER BY year";
$year_result = mysqli_query($connect, $year_query);
if (!$year_result) {
    die("Year query failed: " . mysqli_error($connect));
}

// Handle the form submission
if (isset($_POST['generate_report'])) {
    $selected_year = $_POST['year']; // Get the selected year

    // SQL query to fetch data for the selected year
    $sql = "
    SELECT 
        ir.division, 
        i.name AS item_name, 
        ir.unit_price, 
        ir.quantity, 
        ir.reason, 
        (ir.quantity * ir.unit_price) AS total_cost
    FROM 
        item_requests ir
    LEFT JOIN 
        items i ON ir.item_code = i.item_code
    WHERE 
        ir.year = '$selected_year' AND ir.status = 'Approved'
    ORDER BY 
        ir.division
";

    $result = mysqli_query($connect, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($connect));
    }

    // Data storage for the report
    $data = [];
    $division_totals = [];
    $total_budget = 0;

    // Fetch and process data
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        $total_budget += $row['total_cost'];

        // Calculate total cost per division
        if (!isset($division_totals[$row['division']])) {
            $division_totals[$row['division']] = 0;
        }
        $division_totals[$row['division']] += $row['total_cost'];
    }

    // Create a new PDF document
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();

    // Title Section
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management', 0, 1, 'C');
    $pdf->Ln(5); // Line break
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, "Yearly Report for $selected_year", 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(8); // Line break

    // Table Header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 220, 255); // Header background color

    // Updated column widths (sum ~230)
    $widths = [
        'division'   => 90,
        'item_name'  => 50,
        'unit_price' => 30,
        'quantity'   => 20,
        'total_cost' => 40,
    ];

    // Header cells centered (without Description)
    $pdf->Cell($widths['division'], 6, 'Division', 1, 0, 'C', true);
    $pdf->Cell($widths['item_name'], 6, 'Item Name', 1, 0, 'C', true);
    $pdf->Cell($widths['unit_price'], 6, 'Unit Price', 1, 0, 'C', true);
    $pdf->Cell($widths['quantity'], 6, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell($widths['total_cost'], 6, 'Total Cost', 1, 1, 'C', true);

    // Table Data
    $pdf->SetFont('Arial', '', 9);
    $current_division = '';

    foreach ($data as $row) {
        if ($current_division !== $row['division']) {
            if ($current_division !== '') {
                // Add division total row with exact width match
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(255, 255, 204); // Background color for division total

                $pdf->Cell(array_sum($widths) - $widths['total_cost'], 8, 'Total for ' . utf8_decode($current_division), 1, 0, 'R', true);
                $pdf->Cell($widths['total_cost'], 8, number_format($division_totals[$current_division], 2), 1, 1, 'R', true);
                $pdf->Ln(2); // Line break
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetFillColor(255, 255, 255); // Reset background color
            }
            $current_division = $row['division'];
        }

        // Add row data without description column
        $pdf->Cell($widths['division'], 6, utf8_decode($row['division']), 1, 0, 'C');
        $pdf->Cell($widths['item_name'], 6, utf8_decode($row['item_name']), 1, 0, 'L');
        $pdf->Cell($widths['unit_price'], 6, number_format($row['unit_price'], 2), 1, 0, 'R');
        $pdf->Cell($widths['quantity'], 6, $row['quantity'], 1, 0, 'C');
        $pdf->Cell($widths['total_cost'], 6, number_format($row['total_cost'], 2), 1, 1, 'R');
    }

    // Add the final division total with exact width
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(255, 255, 204);
    $pdf->Cell(array_sum($widths) - $widths['total_cost'], 6, 'Total for ' . utf8_decode($current_division), 1, 0, 'R', true);
    $pdf->Cell($widths['total_cost'], 6, number_format($division_totals[$current_division], 2), 1, 1, 'R', true);

    // Add the overall budget total
    $pdf->Ln(8); // Line break
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 6, 'Overall Total Budget: LKR ' . number_format($total_budget, 2), 0, 1, 'R');

    // Footer Section
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'I', 9);
    
    // Output the generated PDF
    $pdf->Output();
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
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('background.jpg'); /* Path to your background image */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        h1 {
            font-size: 2.8em;
            margin-bottom: 20px;
            color: #003366; /* Dark blue for a professional look */
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4); /* Subtle shadow */
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
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            transition: border-color 0.3s, box-shadow 0.3s;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        button:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }
        button:active {
            background-color: #004085;
            transform: translateY(2px);
        }
    </style>
</head>
<body>
    <form method="POST">
        <h1>Generate Yearly Report</h1>
        <label for="year">Select Year:</label>
        <select name="year" id="year" required>
            <option value="">-- Select Year --</option>
            <?php
            // Populate dropdown with year values from the 'year' column
            mysqli_data_seek($year_result, 0); // reset pointer just in case
            while ($row = mysqli_fetch_assoc($year_result)) {
                echo "<option value='" . htmlspecialchars($row['year']) . "'>" . htmlspecialchars($row['year']) . "</option>";
            }
            ?>
        </select>
        <br />
        <button type="submit" name="generate_report">Generate Report</button>
    </form>
</body>
</html>
