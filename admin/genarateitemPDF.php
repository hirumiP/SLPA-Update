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

    // SQL query to fetch the required data for the selected year, ordered by category_code, item name
    $sql = "
        SELECT 
            i.name AS item_name, 
            i.category_code,
            c.description AS category_description,
            ir.division, 
            ir.unit_price, 
            ir.quantity, 
            ir.reason, 
            (ir.quantity * ir.unit_price) AS total_cost
        FROM 
            item_requests ir
        LEFT JOIN 
            items i ON ir.item_code = i.item_code
        LEFT JOIN
            categories c ON i.category_code = c.category_code
        WHERE 
            ir.year = '$selected_year'
        ORDER BY 
            i.category_code, i.name, ir.division
    ";

    $result = mysqli_query($connect, $sql);
    if (!$result) {
        die("Query failed: " . mysqli_error($connect));
    }

    // Group data by category_description -> items -> rows
    $data = [];
    $category_totals = [];
    $total_budget = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $category_code = $row['category_code'];
        $category_desc = $row['category_description'] ?? 'Uncategorized';
        $item_name = $row['item_name'];

        // Initialize category group if not set
        if (!isset($data[$category_code])) {
            $data[$category_code] = [
                'description' => $category_desc,
                'items' => [],
            ];
            $category_totals[$category_code] = [
                'total_quantity' => 0,
                'total_cost' => 0
            ];
        }

        // Initialize item group if not set
        if (!isset($data[$category_code]['items'][$item_name])) {
            $data[$category_code]['items'][$item_name] = [
                'rows' => [],
                'total_quantity' => 0,
                'total_cost' => 0,
            ];
        }

        // Add row
        $data[$category_code]['items'][$item_name]['rows'][] = $row;

        // Accumulate totals
        $qty = $row['quantity'];
        $cost = $row['total_cost'];

        $data[$category_code]['items'][$item_name]['total_quantity'] += $qty;
        $data[$category_code]['items'][$item_name]['total_cost'] += $cost;

        $category_totals[$category_code]['total_quantity'] += $qty;
        $category_totals[$category_code]['total_cost'] += $cost;

        $total_budget += $cost;
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

    $rowHeight = 6;

    foreach ($data as $category_code => $category_data) {
        // Category header row
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(200, 230, 255);
        $pdf->Cell(235, $rowHeight, "Category: " . $category_data['description'], 1, 1, 'L', true);

        // Table Header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(70, $rowHeight, 'Item Name', 1, 0, 'C', true);
        $pdf->Cell(75, $rowHeight, 'Division', 1, 0, 'C', true);
        $pdf->Cell(30, $rowHeight, 'Unit Price', 1, 0, 'C', true);
        $pdf->Cell(25, $rowHeight, 'Quantity', 1, 0, 'C', true);
        $pdf->Cell(35, $rowHeight, 'Total Cost (LKR)', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);

        foreach ($category_data['items'] as $item_name => $item_data) {
            foreach ($item_data['rows'] as $row) {
                $pdf->Cell(70, $rowHeight, $row['item_name'], 1, 0, 'L');
                $pdf->Cell(75, $rowHeight, $row['division'], 1, 0, 'L');
                $pdf->Cell(30, $rowHeight, number_format($row['unit_price'], 2), 1, 0, 'R');
                $pdf->Cell(25, $rowHeight, $row['quantity'], 1, 0, 'C');
                $pdf->Cell(35, $rowHeight, number_format($row['total_cost'], 2), 1, 1, 'R');
            }

            
        }

        // Category total row
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 230, 255);
        $pdf->Cell(175, $rowHeight, 'Total for ' . $category_data['description'], 1, 0, 'R', true);
        $pdf->Cell(25, $rowHeight, number_format($category_totals[$category_code]['total_quantity'], 0), 1, 0, 'C', true);
        $pdf->Cell(35, $rowHeight, number_format($category_totals[$category_code]['total_cost'], 2), 1, 1, 'R', true);
        $pdf->Ln(8);
    }

    // Overall total budget
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Overall Total Budget: LKR ' . number_format($total_budget, 2), 0, 1, 'R');

    $pdf->Output();
}
?>

<!-- HTML Form Section -->
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
