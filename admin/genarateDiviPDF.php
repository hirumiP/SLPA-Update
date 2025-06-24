<?php
require('./fpdf/fpdf.php');
include('includes/dbc.php');

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
        ir.remark,
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

    class PDF extends FPDF {
        public $col_widths = [20, 20, 20, 15, 45, 30, 20, 45, 40];

        function NbLines($w, $txt) {
            $cw = &$this->CurrentFont['cw'];
            if ($w == 0)
                $w = $this->w - $this->rMargin - $this->x;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace("\r", '', $txt);
            $nb = strlen($s);
            if ($nb > 0 and $s[$nb - 1] == "\n")
                $nb--;
            $sep = -1;
            $i = 0;
            $j = 0;
            $l = 0;
            $nl = 1;
            while ($i < $nb) {
                $c = $s[$i];
                if ($c == "\n") {
                    $i++;
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                    continue;
                }
                if ($c == ' ')
                    $sep = $i;
                $l += $cw[$c];
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j)
                            $i++;
                    } else
                        $i = $sep + 1;
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                } else
                    $i++;
            }
            return $nl;
        }

        function Row($data, $aligns = []) {
            $nb = 0;
            foreach ($data as $i => $txt) {
                $lines = $this->NbLines($this->col_widths[$i], $txt);
                if ($lines > $nb) $nb = $lines;
            }
            $h = 5 * $nb;

            $this->CheckPageBreak($h);

            for ($i = 0; $i < count($data); $i++) {
                $w = $this->col_widths[$i];
                $a = isset($aligns[$i]) ? $aligns[$i] : 'L';

                $x = $this->GetX();
                $y = $this->GetY();

                $this->Rect($x, $y, $w, $h);

                $this->MultiCell($w, 5, $data[$i], 0, $a);

                $this->SetXY($x + $w, $y);
            }
            $this->Ln($h);
        }

        function CheckPageBreak($h) {
            if ($this->GetY() + $h > $this->PageBreakTrigger)
                $this->AddPage($this->CurOrientation);
        }
    }

    $pdf = new PDF('L', 'mm', 'A4');
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management 2025', 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->Cell(0, 10, "Division Report: $selected_division for $selected_year", 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(200, 220, 255);

    $headers = [
        'Budget Responsibility Code',
        'Cost Centre Code / Location',
        'C.E.P / Budget Number',
        'Qty',
        'Item Name',
        'Total Estimated Cost',
        "Allocation Required for $selected_year",
        'Justification Report',
        'Remark'
    ];

    // Print header row
    $pdf->Row($headers, ['C', 'C', 'C', 'C', 'L', 'R', 'C', 'L', 'L']);

    // Print numbering row: [1], [2], ...
    $numbers = [];
    for ($i = 1; $i <= count($headers); $i++) {
        $numbers[] = "[$i]";
    }
    // Center align all numbering cells
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Row($numbers, array_fill(0, count($headers), 'C'));

    // Reset font for data
    $pdf->SetFont('Arial', '', 8);

    foreach ($data as $row) {
        $rowData = [
            '',                     // Budget Responsibility Code (empty)
            '',                     // Cost Centre Code (empty)
            '',                     // C.E.P / Budget Number (empty)
            $row['quantity'],
            $row['item_name'],
            number_format($row['total_cost'], 2),
            '',                     // Allocation Required (empty)
            $row['description'],
            $row['remark']
        ];
        $aligns = ['L', 'L', 'L', 'C', 'L', 'R', 'L', 'L', 'L'];

        $pdf->Row($rowData, $aligns);
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, "Total for $selected_division in $selected_year: LKR " . number_format($total_budget, 2), 0, 1, 'R');

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 8, 'End of Report', 0, 1, 'C');

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
            <?php 
            $division_query = "SELECT DISTINCT division FROM item_requests ORDER BY division";
            $division_result = mysqli_query($connect, $division_query);
            while ($row = mysqli_fetch_assoc($division_result)) {
                echo "<option value='{$row['division']}'>{$row['division']}</option>";
            } 
            ?>
        </select>

        <label for="year">Select Year:</label>
        <select name="year" id="year" required>
            <option value="">-- Select Year --</option>
            <?php 
            $year_query = "SELECT DISTINCT year FROM item_requests ORDER BY year DESC";
            $year_result = mysqli_query($connect, $year_query);
            while ($row = mysqli_fetch_assoc($year_result)) {
                echo "<option value='{$row['year']}'>{$row['year']}</option>";
            } 
            ?>
        </select>

        <button type="submit" name="generate_report">Generate Report</button>
    </form>
</body>
</html>
