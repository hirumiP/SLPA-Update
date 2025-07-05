<?php
require './fpdf/fpdf.php';
include 'includes/dbc.php';

// -------------------------------------------------------------
// 1. Get available years for dropdown
$year_result = mysqli_query($connect, "SELECT DISTINCT year FROM item_requests ORDER BY year DESC");

// -------------------------------------------------------------
// 2. Only generate report if form is submitted
if (isset($_POST['generate_report'])) {
    $year1 = $_POST['year'];
    $budget1 = $_POST['budget_id'];
    $year2 = $_POST['year2'] ?? null;
    $budget2 = $_POST['budget_id2'] ?? null;

    $filters = [];

    if ($year1 && $budget1) {
        $filters[] = "(r.year = '" . mysqli_real_escape_string($connect, $year1) . "' AND r.budget_id = '" . mysqli_real_escape_string($connect, $budget1) . "')";
    }
    if ($year2 && $budget2) {
        $filters[] = "(r.year = '" . mysqli_real_escape_string($connect, $year2) . "' AND r.budget_id = '" . mysqli_real_escape_string($connect, $budget2) . "')";
    }

    if (empty($filters)) {
        die("Please select at least one valid Year + Budget combination.");
    }

    $where_clause = "WHERE " . implode(" OR ", $filters);

    // -------------------------------------------------------------
    // 3. Fetch category data
    $cat_q = "SELECT * FROM categories ORDER BY category_code";
    $cat_rs = mysqli_query($connect, $cat_q) or die("Category query failed: " . mysqli_error($connect));

    $item_q = "
        SELECT 
            i.item_code,
            i.name,
            i.category_code,
            SUM(r.quantity) AS total_quantity,
            SUM(r.total_price) AS total_cost
        FROM item_requests r
        INNER JOIN items i ON r.item_code = i.item_code
        $where_clause
        GROUP BY i.item_code, i.name, i.category_code
        ORDER BY i.category_code, i.item_code
    ";
    $item_rs = mysqli_query($connect, $item_q) or die("Item request query failed: " . mysqli_error($connect));

    // Group data under categories
    $categories = [];
    while ($cat = mysqli_fetch_assoc($cat_rs)) {
        $categories[$cat['category_code']] = [
            'category_name' => $cat['description'],
            'items' => []
        ];
    }
    while ($it = mysqli_fetch_assoc($item_rs)) {
        $categories[$it['category_code']]['items'][] = [
            'item_name'   => $it['name'],
            'qty'         => $it['total_quantity'],
            'total_cost'  => $it['total_cost']
        ];
    }

    // -------------------------------------------------------------
    // 4. PDF Generation
    class PDF_Table extends FPDF
    {
        public array $col_w;
        public float $line_h = 4;

        function nbLines(float $w, string $txt): int {
            $cw    = $this->CurrentFont['cw'];
            $wmax  = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s     = str_replace("\r", '', $txt);
            $nb    = strlen($s);
            $sep   = -1; $i = $j = $l = 0; $nl = 1;
            while ($i < $nb) {
                $c = $s[$i];
                if ($c === "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
                if ($c === ' ')  { $sep = $i; }
                $l += $cw[$c] ?? 0;
                if ($l > $wmax) {
                    if ($sep === -1) { if ($i === $j) $i++; }
                    else { $i = $sep + 1; }
                    $sep = -1; $j = $i; $l = 0; $nl++;
                } else { $i++; }
            }
            return $nl;
        }

        function row(array $data, array $align = []) {
            $nb_max = 0;
            foreach ($data as $i => $txt) {
                $nb_max = max($nb_max, $this->nbLines($this->col_w[$i], (string)$txt));
            }
            $h = $this->line_h * $nb_max;
            if ($this->GetY() + $h > $this->PageBreakTrigger) {
                $this->AddPage($this->CurOrientation);
            }
            $x_start = $this->GetX();
            $y_start = $this->GetY();

            foreach ($data as $i => $txt) {
                $w = $this->col_w[$i];
                $a = $align[$i] ?? 'L';
                $x = $this->GetX();
                $y = $this->GetY();
                $this->Rect($x, $y, $w, $h);
                $this->MultiCell($w, $this->line_h, (string)$txt, 0, $a);
                $this->SetXY($x + $w, $y);
            }
            $this->SetXY($x_start, $y_start + $h);
        }
    }

    $pdf = new PDF_Table('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 10);

    $pdf->col_w = [15, 15, 15, 10, 30, 55, 25, 45, 25];
    $offset = (297 - array_sum($pdf->col_w)) / 2;

    // Title
    $headerTitle = "SLPA Budget Management - All Divisions Block Allocation (Requested Items)";
    if ($year1 && $budget1) $headerTitle .= " - Year $year1 (Budget $budget1)";
    if ($year2 && $budget2) $headerTitle .= " vs Year $year2 (Budget $budget2)";
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, $headerTitle, 0, 1, 'C');
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(0, 5, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(6);
    $pdf->SetX($offset);

    // Headers
    $headers = [
        'Budget Responsibility Code',
        'Cost Centre Code / Location',
        'C.E.P / Budget Number',
        'Qty',
        'Category Name',
        'Item Name',
        'Total Estimated Cost (Rs.)',
        'Description',
        'Remark'
    ];
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->SetX($offset);
    $pdf->row($headers, array_fill(0, 9, 'C'));

    $pdf->SetFont('Arial', 'I', 6);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetX($offset);
    $pdf->row(array_map(fn($i) => '[' . $i . ']', range(1, 9)), array_fill(0, 9, 'C'));

    // Rows
    $pdf->SetFont('Arial', '', 8);
    foreach ($categories as $cat) {
        $isFirstItem = true;
        foreach ($cat['items'] as $item) {
            $pdf->SetX($offset);
            $pdf->row([
                '', '', '',                                 // BR, CC, CEP
                $item['qty'],
                $isFirstItem ? $cat['category_name'] : '',
                $item['item_name'],
                number_format($item['total_cost'], 2),
                '', ''
            ], ['C','C','C','C','L','L','R','L','L']);

            $isFirstItem = false;
        }
    }

    // Total
    $grand_total = 0;
    foreach ($categories as $cat) {
        foreach ($cat['items'] as $item) {
            $grand_total += $item['total_cost'];
        }
    }

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetX($offset);
    $pdf->row([
        '', '', '', '', '', 'Total Estimated Cost:',
        number_format($grand_total, 2),
        '', ''
    ], ['C','C','C','C','L','R','R','L','L']);

    // Footer
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'I', 6);
    $pdf->Cell(0, 5, 'End of Report', 0, 1, 'C');

    // Output
    $pdf->Output();
    exit;
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Divisions Block Allocation</title>
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
</style>
</head>
<body>
<form action="" method="post" autocomplete="off">
    <h1>All Divisions Block Allocation</h1>
    
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
