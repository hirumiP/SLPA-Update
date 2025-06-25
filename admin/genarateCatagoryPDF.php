<?php
require './fpdf/fpdf.php';
include 'includes/dbc.php';

/* -----------------------------------------------------------------
   1. FETCH DATA (Group item_requests by item_code)
   ----------------------------------------------------------------- */
$cat_q = "SELECT * FROM categories ORDER BY category_code";
$cat_rs = mysqli_query($connect, $cat_q) or die("Category query failed: " . mysqli_error($connect));

$item_q = "SELECT 
              i.item_code,
              i.name,
              i.unit_price,
              i.category_code,
              SUM(r.quantity) AS total_quantity
           FROM item_requests r
           INNER JOIN items i ON r.item_code = i.item_code
           GROUP BY i.item_code, i.name, i.unit_price, i.category_code
           ORDER BY i.category_code, i.item_code";

$item_rs = mysqli_query($connect, $item_q) or die("Item request query failed: " . mysqli_error($connect));

// Group items under categories
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
        'total_cost'  => $it['unit_price'] * $it['total_quantity']
    ];
}

/* -----------------------------------------------------------------
   2. FPDF subclass with wrapped rows
   ----------------------------------------------------------------- */
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

/* -----------------------------------------------------------------
   3. PDF SETUP
   ----------------------------------------------------------------- */
$pdf = new PDF_Table('L', 'mm', 'A4'); // Landscape
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 10);

// Column widths: BR, CC, CEP, Qty, Category, Item, Total Cost, Desc, Remark
$pdf->col_w = [15, 15, 15, 10, 30, 35, 25, 55, 25];
$offset = (297 - array_sum($pdf->col_w)) / 2;

/* -----------------------------------------------------------------
   4. HEADER
   ----------------------------------------------------------------- */
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'SLPA Budget Management - All Divisions Block Allocation (Requested Items)', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(0, 5, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
$pdf->Ln(6);
$pdf->SetX($offset);

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

// Index row
$pdf->SetFont('Arial', 'I', 6);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetX($offset);
$pdf->row(array_map(fn($i) => '[' . $i . ']', range(1, 9)), array_fill(0, 9, 'C'));

/* -----------------------------------------------------------------
   5. DATA ROWS
   ----------------------------------------------------------------- */
$pdf->SetFont('Arial', '', 8);
foreach ($categories as $cat) {
    foreach ($cat['items'] as $item) {
        $pdf->SetX($offset);
        $pdf->row([
            '', '', '',                                 // BR Code, CC, CEP
            $item['qty'],
            $cat['category_name'],
            $item['item_name'],
            number_format($item['total_cost'], 2),
            '', // Description
            ''  // Remark
        ], ['C','C','C','C','L','L','R','L','L']);
    }
    $pdf->Ln(1); // space between categories
}

/* -----------------------------------------------------------------
   6. FOOTER
   ----------------------------------------------------------------- */
$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 6);
$pdf->Cell(0, 5, 'End of Report', 0, 1, 'C');

/* -----------------------------------------------------------------
   7. OUTPUT
   ----------------------------------------------------------------- */
$pdf->Output();
?>
