<?php
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

    // SQL query to fetch the required data for the selected year, ordered by item name
    $sql = "
        SELECT 
            i.name AS item_name, 
            ir.division, 
            ir.unit_price, 
            ir.quantity, 
            ir.reason, 
            ir.description, 
            (ir.quantity * ir.unit_price) AS total_cost
        FROM 
            item_requests ir
        LEFT JOIN 
            items i ON ir.item_code = i.item_code
        WHERE 
            ir.year = '$selected_year'
        ORDER BY 
            i.name, ir.division
    ";

    $result = mysqli_query($connect, $sql);
    if (!$result) {
        die("Query failed: " . mysqli_error($connect));
    }

    $data = [];
    $item_totals = [];
    $total_budget = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        $total_budget += $row['total_cost'];

        if (!isset($item_totals[$row['item_name']])) {
            $item_totals[$row['item_name']] = [
                'total_quantity' => 0,
                'total_cost' => 0
            ];
        }
        $item_totals[$row['item_name']]['total_quantity'] += $row['quantity'];
        $item_totals[$row['item_name']]['total_cost'] += $row['total_cost'];
    }

    // Create a new PDF in landscape mode
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();

    // Title Section
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->Cell(0, 10, 'Item Report (Requested) for ' . $selected_year, 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(8);

    // Table Header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 220, 255);

    $rowHeight = 6;

    $pdf->Cell(40, $rowHeight, 'Item Name', 1, 0, 'C', true);
    $pdf->Cell(65, $rowHeight, 'Division', 1, 0, 'C', true); // Increased width
    $pdf->Cell(30, $rowHeight, 'Unit Price', 1, 0, 'C', true);
    $pdf->Cell(60, $rowHeight, 'Description', 1, 0, 'C', true);
    $pdf->Cell(25, $rowHeight, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell(35, $rowHeight, 'Total Cost (LKR)', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 9);
    $current_item_name = '';

    foreach ($data as $row) {
        if ($current_item_name !== $row['item_name']) {
            if ($current_item_name !== '') {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(255, 255, 204);
                $pdf->Cell(195, $rowHeight, 'Total for ' . $current_item_name, 1, 0, 'R', true);
                $pdf->Cell(25, $rowHeight, number_format($item_totals[$current_item_name]['total_quantity'], 0), 1, 0, 'C', true);
                $pdf->Cell(35, $rowHeight, number_format($item_totals[$current_item_name]['total_cost'], 2), 1, 1, 'R', true);
                $pdf->Ln(2);
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetFillColor(255, 255, 255);
            }
            $current_item_name = $row['item_name'];
        }

        $pdf->Cell(40, $rowHeight, $row['item_name'], 1, 0, 'L');
        $pdf->Cell(65, $rowHeight, $row['division'], 1, 0, 'L');
        $pdf->Cell(30, $rowHeight, number_format($row['unit_price'], 2), 1, 0, 'R');
        $pdf->Cell(60, $rowHeight, $row['description'], 1, 0, 'L');
        $pdf->Cell(25, $rowHeight, $row['quantity'], 1, 0, 'C');
        $pdf->Cell(35, $rowHeight, number_format($row['total_cost'], 2), 1, 1, 'R');
    }

    // Final total row
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(255, 255, 204);
    $pdf->Cell(195, $rowHeight, 'Total for ' . $current_item_name, 1, 0, 'R', true);
    $pdf->Cell(25, $rowHeight, number_format($item_totals[$current_item_name]['total_quantity'], 0), 1, 0, 'C', true);
    $pdf->Cell(35, $rowHeight, number_format($item_totals[$current_item_name]['total_cost'], 2), 1, 1, 'R', true);

    // Overall total
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, 'Overall Total Budget: LKR ' . number_format($total_budget, 2), 0, 1, 'R');

    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'I', 9);
    

    $pdf->Output();
}
?>

<!-- HTML form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Yearly Report</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-image: url('background.jpg');
            background-size: cover;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.85);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 100%;
            animation: fadeIn 1s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            font-size: 2.8em;
            margin-bottom: 20px;
            color: #003366;
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
            border: 2px solid #007bff;
            border-radius: 10px;
            background-color: #e3f2fd;
            margin-bottom: 25px;
        }
        button {
            font-size: 1.4em;
            padding: 14px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
        button:active { background-color: #004085; }
    </style>
</head>
<body>
    <form method="POST">
        <h1>Generate Yearly Report</h1>
        <label for="year">Select Year:</label>
        <select name="year" id="year" required>
            <option value="">-- Select Year --</option>
            <?php
            while ($row = mysqli_fetch_assoc($year_result)) {
                echo "<option value='{$row['year']}'>{$row['year']}</option>";
            }
            ?>
        </select>
        <br>
        <button type="submit" name="generate_report">Generate Report</button>
    </form>
</body>
</html>
