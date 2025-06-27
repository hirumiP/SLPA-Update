<?php
require('./fpdf/fpdf.php');
include('includes/dbc.php');

// Function to generate the PDF
function generatePDF($budget1_data, $budget2_data, $budget1_total, $budget2_total) {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Budget Comparison Report', 0, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Budget 1 Data', 0, 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 8, 'Division', 1);
    $pdf->Cell(60, 8, 'Item Name', 1);
    $pdf->Cell(25, 8, 'Unit Price', 1);
    $pdf->Cell(20, 8, 'Qty', 1);
    $pdf->Cell(40, 8, 'Description', 1);
    $pdf->Cell(35, 8, 'Remark', 1);
    $pdf->Cell(30, 8, 'Total Cost', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    foreach ($budget1_data as $row) {
        $pdf->Cell(40, 8, $row['division'], 1);
        $pdf->Cell(60, 8, $row['item_name'], 1);
        $pdf->Cell(25, 8, $row['unit_price'], 1);
        $pdf->Cell(20, 8, $row['quantity'], 1);
        $pdf->Cell(40, 8, $row['description'], 1);
        $pdf->Cell(35, 8, $row['remark'], 1);
        $pdf->Cell(30, 8, number_format($row['total_cost'], 2), 1);
        $pdf->Ln();
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Budget 2 Data', 0, 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 8, 'Division', 1);
    $pdf->Cell(60, 8, 'Item Name', 1);
    $pdf->Cell(25, 8, 'Unit Price', 1);
    $pdf->Cell(20, 8, 'Qty', 1);
    $pdf->Cell(40, 8, 'Description', 1);
    $pdf->Cell(35, 8, 'Remark', 1);
    $pdf->Cell(30, 8, 'Total Cost', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    foreach ($budget2_data as $row) {
        $pdf->Cell(40, 8, $row['division'], 1);
        $pdf->Cell(60, 8, $row['item_name'], 1);
        $pdf->Cell(25, 8, $row['unit_price'], 1);
        $pdf->Cell(20, 8, $row['quantity'], 1);
        $pdf->Cell(40, 8, $row['description'], 1);
        $pdf->Cell(35, 8, $row['remark'], 1);
        $pdf->Cell(30, 8, number_format($row['total_cost'], 2), 1);
        $pdf->Ln();
    }

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Total Budget 1: Rs. " . number_format($budget1_total, 2), 0, 1);
    $pdf->Cell(0, 10, "Total Budget 2: Rs. " . number_format($budget2_total, 2), 0, 1);

    $pdf->Output();
    exit();
}

// Handle form submission and generate PDF
if (isset($_POST['generate_report'])) {
    $budget1_type = $_POST['budget1_type'];
    $budget1_year = $_POST['budget1_year'];
    $budget2_type = $_POST['budget2_type'];
    $budget2_year = $_POST['budget2_year'];

    $query_budget1 = "
        SELECT 
            ir.division,
            i.name AS item_name,
            ir.unit_price,
            ir.quantity,
            ir.description,
            ir.remark,
            (ir.unit_price * ir.quantity) AS total_cost
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        WHERE ir.year = '$budget1_year' 
            AND ir.budget_id = '$budget1_type'
        ORDER BY ir.division
    ";

    $query_budget2 = "
        SELECT 
            ir.division,
            i.name AS item_name,
            ir.unit_price,
            ir.quantity,
            ir.description,
            ir.remark,
            (ir.unit_price * ir.quantity) AS total_cost
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        WHERE ir.year = '$budget2_year' 
            AND ir.budget_id = '$budget2_type'
        ORDER BY ir.division
    ";

    $result1 = mysqli_query($connect, $query_budget1);
    $result2 = mysqli_query($connect, $query_budget2);

    if (!$result1 || !$result2) {
        die("Query failed: " . mysqli_error($connect));
    }

    $budget1_data = [];
    $budget2_data = [];
    $budget1_total = 0;
    $budget2_total = 0;

    while ($row = mysqli_fetch_assoc($result1)) {
        $budget1_data[] = $row;
        $budget1_total += $row['total_cost'];
    }

    while ($row = mysqli_fetch_assoc($result2)) {
        $budget2_data[] = $row;
        $budget2_total += $row['total_cost'];
    }

    generatePDF($budget1_data, $budget2_data, $budget1_total, $budget2_total);
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
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            text-align: left;
        }
    </style>
</head>
<body>
    <form method="POST" action="" target="_blank">
        <h1>Generate Budget Comparison Report</h1>

        <div class="form-grid">
            <div class="form-group">
                <label>Budget 1 Type:</label>
                <select name="budget1_type" required>
                    <option value="">-- Select Type --</option>
                    <option value="First Round">First Round</option>
                    <option value="Revised">Revised</option>
                </select>
            </div>

            <div class="form-group">
                <label>Budget 2 Type:</label>
                <select name="budget2_type" required>
                    <option value="">-- Select Type --</option>
                    <option value="First Round">First Round</option>
                    <option value="Revised">Revised</option>
                </select>
            </div>

            <div class="form-group">
                <label>Budget 1 Year:</label>
                <select name="budget1_year" required>
                    <option value="">-- Select Year --</option>
                    <?php 
                    $year_query = "SELECT DISTINCT year FROM item_requests ORDER BY year DESC";
                    $year_result = mysqli_query($connect, $year_query);
                    while ($row = mysqli_fetch_assoc($year_result)) {
                        echo "<option value='{$row['year']}'>{$row['year']}</option>";
                    } 
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Budget 2 Year:</label>
                <select name="budget2_year" required>
                    <option value="">-- Select Year --</option>
                    <?php 
                    $year_query = "SELECT DISTINCT year FROM item_requests ORDER BY year DESC";
                    $year_result = mysqli_query($connect, $year_query);
                    while ($row = mysqli_fetch_assoc($year_result)) {
                        echo "<option value='{$row['year']}'>{$row['year']}</option>";
                    } 
                    ?>
                </select>
            </div>
        </div>

        <button type="submit" name="generate_report">Generate Comparison Report</button>
    </form>
</body>
</html>
