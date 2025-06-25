<?php
require('./fpdf/fpdf.php');
include('includes/dbc.php');

// Fetch categories
$category_query = "SELECT * FROM categories ORDER BY category_code";
$category_result = mysqli_query($connect, $category_query);
if (!$category_result) {
    die("Category query failed: " . mysqli_error($connect));
}

// Fetch items
$item_query = "SELECT * FROM items ORDER BY category_code, item_code";
$item_result = mysqli_query($connect, $item_query);
if (!$item_result) {
    die("Item query failed: " . mysqli_error($connect));
}

// Organize data
$categories = [];
while ($category = mysqli_fetch_assoc($category_result)) {
    $categories[$category['category_code']] = [
        'category_name' => $category['description'],
        'items' => [],
    ];
}
while ($item = mysqli_fetch_assoc($item_result)) {
    $categories[$item['category_code']]['items'][] = [
        'item_name' => $item['name'],
        'unit_price' => $item['unit_price'],
        'description' => $item['description'],
        'remark' => $item['remark'],
        'budget_responsibility_code' => $item['budget_responsibility_code'] ?? '',
        'cost_centre_code' => $item['cost_centre_code'] ?? '',
        'budget_number' => $item['budget_number'] ?? '',
    ];
}

// Create PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Title
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 8, 'SLPA Budget Management - Category Wise Item Report', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('Arial', '', 6);
$pdf->Cell(0, 6, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
$pdf->Ln(10);

// New column widths with 3 new columns
$col_widths = [
    30, // Budget Responsibility Code
    35, // Cost Centre Code / Location
    35, // C.E.P / Budget Number
    35, // Category Name
    35, // Item Name
    20, // Unit Price
    45, // Description
    25  // Remark
];
$total_table_width = array_sum($col_widths);
$offset = (297 - $total_table_width) / 2; // Landscape width = 297mm

// Table Header
$pdf->SetX($offset);
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetFillColor(200, 220, 255);
$row_height = 6;

$headers = [
    'Budget Responsibility Code',
    'Cost Centre Code / Location',
    'C.E.P / Budget Number',
    'Category Name',
    'Item Name',
    'Unit Price',
    'Description',
    'Remark'
];

foreach ($headers as $i => $header) {
    $pdf->Cell($col_widths[$i], $row_height, $header, 1, 0, 'C', true);
}
$pdf->Ln();

// Add Column Numbering Row
$pdf->SetX($offset);
$pdf->SetFont('Arial', 'I', 6);
$pdf->SetFillColor(240, 240, 240);
foreach ($col_widths as $i => $width) {
    $pdf->Cell($width, $row_height, '[' . ($i + 1) . ']', 1, 0, 'C', true);
}
$pdf->Ln();

// Data Rows
$pdf->SetFont('Arial', '', 6);
foreach ($categories as $category_data) {
    foreach ($category_data['items'] as $item) {
        $pdf->SetX($offset);
        $pdf->SetFillColor(255, 255, 200);
        $pdf->SetFont('Arial', '', 6);
        
        // New Columns
        $pdf->Cell($col_widths[0], $row_height, $item['budget_responsibility_code'], 1, 0, 'C');
        $pdf->Cell($col_widths[1], $row_height, $item['cost_centre_code'], 1, 0, 'C');
        $pdf->Cell($col_widths[2], $row_height, $item['budget_number'], 1, 0, 'C');
        
        // Category Name
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell($col_widths[3], $row_height, $category_data['category_name'], 1, 0, 'L', true);
        
        // Item Info
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell($col_widths[4], $row_height, $item['item_name'], 1, 0, 'C');
        $pdf->Cell($col_widths[5], $row_height, number_format($item['unit_price'], 2), 1, 0, 'C');
        $pdf->Cell($col_widths[6], $row_height, $item['description'], 1, 0, 'C');
        $pdf->Cell($col_widths[7], $row_height, $item['remark'], 1, 1, 'C');
    }
    $pdf->Ln(1);
}

// Footer
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 6);
$pdf->Cell(0, 6, 'End of Report', 0, 1, 'C');

// Output PDF
$pdf->Output();
?>
