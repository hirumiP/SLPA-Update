<?php
require('./fpdf/fpdf.php');
include('includes/dbc.php');

if (isset($_POST['generate_report'])) {
    $selected_division = $_POST['division'];
    $selected_year = $_POST['year'];
    $selected_budget_id = $_POST['budget_id'];
    $summary_report = isset($_POST['summary_report']) && $_POST['summary_report'] == '1';


    $sql = "
    SELECT 
        ir.division, 
        i.name AS item_name, 
        ir.unit_price, 
        ir.quantity, 
        ir.description, 
        ir.remark,
        ir.budget_id,
        i.category_code,
        ir.reason,
        (ir.quantity * ir.unit_price) AS total_cost
    FROM 
        item_requests ir
    LEFT JOIN 
        items i ON ir.item_code = i.item_code
    WHERE 
        ir.division = '$selected_division' 
        AND ir.year = '$selected_year'
        AND ir.budget_id = '$selected_budget_id'
    ORDER BY 
        i.category_code, i.name
    ";

    $result = mysqli_query($connect, $sql);
    if (!$result) {
        die("Query failed: " . mysqli_error($connect));
    }

    $category_names = [];
    $cat_query = "SELECT category_code, description FROM categories";
    $cat_result = mysqli_query($connect, $cat_query);
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $category_names[$row['category_code']] = $row['description'];
    }

    $data = [];
    $total_budget = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $cat = $row['category_code'] ?? 'Uncategorized';
        $row['category_name'] = $category_names[$cat] ?? 'Uncategorized';
        $data[$cat]['name'] = $row['category_name'];
        $data[$cat]['items'][] = $row;
        $total_budget += $row['total_cost'];
    }

    class PDF extends FPDF {
        public $col_widths = [15, 20, 15, 55, 30, 65, 30]; // Updated column widths

        function NbLines($w, $txt) {
            $cw = &$this->CurrentFont['cw'];
            if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace("\r", '', $txt);
            $nb = strlen($s);
            if ($nb > 0 and $s[$nb - 1] == "\n") $nb--;
            $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
            while ($i < $nb) {
                $c = $s[$i];
                if ($c == "\n") {
                    $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue;
                }
                if ($c == ' ') $sep = $i;
                $l += $cw[$c];
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j) $i++;
                    } else $i = $sep + 1;
                    $sep = -1; $j = $i; $l = 0; $nl++;
                } else $i++;
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

    if ($summary_report) {
    // Prepare summary data by category
    $summary_data = [];

    foreach ($data as $cat_code => $cat_data) {
        $new_qty = 0;
        $replace_qty = 0;
        $total_qty = 0;
        $total_amount = 0;

        foreach ($cat_data['items'] as $item) {
            $qty = $item['quantity'];
            $total_qty += $qty;
            $total_amount += $item['total_cost'];

            if ($item['reason'] === 'New') {
                $new_qty += $qty;
            } elseif ($item['reason'] === 'Replace') {
                $replace_qty += $qty;
            }
        }

        $summary_data[] = [
            'category' => $cat_data['name'],
            'new_qty' => $new_qty,
            'replace_qty' => $replace_qty,
            'total_qty' => $total_qty,
            'total_amount' => $total_amount,
        ];
    }
}
if ($summary_report) {
    $pdf = new PDF('P', 'mm', 'A4');  // Portrait mode for summary
    $pdf->SetLeftMargin(15);
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management - Summary Report', 0, 1, 'C');
    $pdf->Ln(3);

    $budget_label = $selected_budget_id == 1 ? "First Round" : ($selected_budget_id == 2 ? "Revised" : "Unknown");
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Division: $selected_division | Year: $selected_year | Budget: $budget_label", 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 220, 255);

    // Header row
    $pdf->SetTextColor(0);
    $pdf->Cell(70, 8, 'Category', 1, 0, 'L', true);
    $pdf->Cell(25, 8, 'New ', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Replace ', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Total Qty', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Total Amount (LKR)', 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 10);

    $grand_new_qty = 0;
    $grand_replace_qty = 0;
    $grand_total_qty = 0;
    $grand_total_amount = 0;

    foreach ($summary_data as $row) {
        $pdf->Cell(70, 8, $row['category'], 1, 0, 'L');
        $pdf->Cell(25, 8, $row['new_qty'], 1, 0, 'C');
        $pdf->Cell(30, 8, $row['replace_qty'], 1, 0, 'C');
        $pdf->Cell(25, 8, $row['total_qty'], 1, 0, 'C');
        $pdf->Cell(40, 8, number_format($row['total_amount'], 2), 1, 1, 'R');

        $grand_new_qty += $row['new_qty'];
        $grand_replace_qty += $row['replace_qty'];
        $grand_total_qty += $row['total_qty'];
        $grand_total_amount += $row['total_amount'];
    }

    // Grand total row
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(70, 8, 'Grand Total', 1, 0, 'L', true);
    $pdf->Cell(25, 8, $grand_new_qty, 1, 0, 'C', true);
    $pdf->Cell(30, 8, $grand_replace_qty, 1, 0, 'C', true);
    $pdf->Cell(25, 8, $grand_total_qty, 1, 0, 'C', true);
    $pdf->Cell(40, 8, number_format($grand_total_amount, 2), 1, 1, 'R', true);

    $pdf->Output('Summary_Report.pdf', 'I');
    exit;
} else {
    $pdf = new PDF('L', 'mm', 'A4');
    $pdf->SetLeftMargin(30); // or any value in mm
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SLPA Budget Management', 0, 1, 'C');
    $pdf->Ln(3);

    $budget_label = $selected_budget_id == 1 ? "First Round" : ($selected_budget_id == 2 ? "Revised" : "Unknown");
    $pdf->Cell(0, 10, "Division Report: $selected_division for $selected_year ($budget_label)", 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(200, 220, 255);

    $headers = ['New', 'Replace', 'Qty', 'Item Name', 'Total Estimated Cost', 'Justification Report', 'Remark'];
    $pdf->Row($headers, ['C', 'C', 'C', 'L', 'R', 'L', 'L']);

    $numbers = [];
    for ($i = 1; $i <= count($headers); $i++) {
        $numbers[] = "[$i]";
    }
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Row($numbers, array_fill(0, count($headers), 'C'));

    $pdf->SetFont('Arial', '', 8);

    foreach ($data as $cat_data) {
        $cat_total_qty = 0;
        $cat_total_cost = 0;

        foreach ($cat_data['items'] as $row) {
            $cat_total_qty += $row['quantity'];
            $cat_total_cost += $row['total_cost'];

            $new_qty = $row['reason'] === 'New' ? $row['quantity'] : '';
            $replace_qty = $row['reason'] === 'Replace' ? $row['quantity'] : '';

            $rowData = [
                $new_qty,
                $replace_qty,
                $row['quantity'],
                $row['item_name'],
                number_format($row['total_cost'], 2),
                $row['description'],
                $row['remark']
            ];
            $aligns = ['C', 'C', 'C', 'L', 'R', 'L', 'L'];
            $pdf->Row($rowData, $aligns);
        }

        // Category total row
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(230, 230, 250);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Row([
            '', '', $cat_total_qty,
            $cat_data['name'] . " Total",
            number_format($cat_total_cost, 2),
            '', ''
        ], ['C', 'C', 'C', 'L', 'R', 'L', 'L']);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8);
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, "Grand Total : LKR " . number_format($total_budget, 2), 0, 1, 'R');

    $pdf->Output('Division_Report.pdf', 'I');
    exit;
}


    
}
?>


<!-- HTML FORM SECTION (unchanged) -->
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

       <label for="budget">Select Budget:</label>
        <select name="budget_id" id="budget" required>
            <option value="">-- Select Budget --</option>
            <?php 
            $budget_query = "SELECT DISTINCT budget_id FROM item_requests ORDER BY budget_id";
            $budget_result = mysqli_query($connect, $budget_query);
            while ($row = mysqli_fetch_assoc($budget_result)) {
                $label = $row['budget_id'] == 1 ? "First Round" : ($row['budget_id'] == 2 ? "Revised" : "Unknown");
                echo "<option value='{$row['budget_id']}'>{$label}</option>";
            } 
            ?>
        </select>
        <label>
  <input type="checkbox" name="summary_report" value="1"> Summary Report
</label>


        <button type="submit" name="generate_report">Generate Report</button>
    </form>
</body>
</html>
