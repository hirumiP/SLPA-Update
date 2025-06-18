<?php
require('./fpdf/fpdf.php');
include('includes/dbc.php');

// Fetch categories from the database
$category_query = "SELECT * FROM categories ORDER BY category_code";
$category_result = mysqli_query($connect, $category_query);
if (!$category_result) {
    die("Category query failed: " . mysqli_error($connect));
}

// Fetch items from the database
$item_query = "SELECT * FROM items ORDER BY category_code, item_code";
$item_result = mysqli_query($connect, $item_query);
if (!$item_result) {
    die("Item query failed: " . mysqli_error($connect));
}

// Data storage for categories and items
$categories = [];
while ($category = mysqli_fetch_assoc($category_result)) {
    $categories[$category['category_code']] = [
        'category_name' => $category['description'],
        'items' => [],  // Initialize an empty array for items under each category
    ];
}

// Group items by their respective categories
while ($item = mysqli_fetch_assoc($item_result)) {
    $categories[$item['category_code']]['items'][] = [
        'item_code' => $item['item_code'],
        'item_name' => $item['name'],
        'unit_price' => $item['unit_price'],
        'description' => $item['description'],
        'remark' => $item['remark'],
    ];
}

// Create a new PDF document
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
//$pdf->Image('logo.png', 10, 10, 28); // Logo at the top left corner

// Title Section
$pdf->SetFont('Arial', 'B', 10); // Title font
$pdf->Cell(190, 8, 'SLPA Budget Management - Category Wise Item Report', 0, 1, 'C');
$pdf->Ln(3); // Line break
$pdf->SetFont('Arial', '', 6); // Date font
$pdf->Cell(190, 6, 'Generated on: ' . date('Y-m-d'), 0, 1, 'C');
$pdf->Ln(5); // Line break
$pdf->Ln(5); // Line break
$pdf->Ln(5); // Line break
// Table Column Widths
$col_widths = [18, 35, 28, 40, 22, 35, 22]; // Adjusted column widths for better layout

// Calculate total table width
$total_table_width = array_sum($col_widths);

// Center the table on the page
$offset = (210 - $total_table_width) / 2; // 210mm is the width of A4 size in mm
$pdf->SetX($offset);

// Table Header
$pdf->SetFont('Arial', 'B', 7); // Header font
$pdf->SetFillColor(200, 220, 255); // Header background color
$row_height = 6; // Row height for consistency

// Header Cells
$pdf->Cell($col_widths[0], $row_height, 'Category Code', 1, 0, 'C', true);
$pdf->Cell($col_widths[1], $row_height, 'Category Name', 1, 0, 'C', true);
$pdf->Cell($col_widths[2], $row_height, 'Item Code', 1, 0, 'C', true);
$pdf->Cell($col_widths[3], $row_height, 'Item Name', 1, 0, 'C', true);
$pdf->Cell($col_widths[4], $row_height, 'Unit Price', 1, 0, 'C', true);
$pdf->Cell($col_widths[5], $row_height, 'Description', 1, 0, 'C', true);
$pdf->Cell($col_widths[6], $row_height, 'Remark', 1, 1, 'C', true);

// Table Data
$pdf->SetFont('Arial', '', 6); // Data font size
foreach ($categories as $category_code => $category_data) {
    // Set X position to ensure consistent alignment for each category
    $pdf->SetX($offset);

    // Category Row - Highlighted with light color
    $pdf->SetFont('Arial', 'B', 6); // Font for category row
    $pdf->SetFillColor(255, 255, 200); // Highlight color
    $pdf->Cell($col_widths[0], $row_height, $category_code, 1, 0, 'C', true);
    $pdf->Cell($col_widths[1], $row_height, $category_data['category_name'], 1, 0, 'L', true);
    $pdf->Cell($col_widths[2], $row_height, '', 1, 0, 'C'); // Empty cell for item details
    $pdf->Cell($col_widths[3], $row_height, '', 1, 0, 'C');
    $pdf->Cell($col_widths[4], $row_height, '', 1, 0, 'C');
    $pdf->Cell($col_widths[5], $row_height, '', 1, 0, 'C');
    $pdf->Cell($col_widths[6], $row_height, '', 1, 1, 'C'); // Empty cell for item details

    // Item Rows under each category
    foreach ($category_data['items'] as $item) {
        $pdf->SetFont('Arial', '', 6); // Font for item rows
        $pdf->SetX($offset); // Ensure X position is set before item rows
        $pdf->Cell($col_widths[0], $row_height, '', 1, 0, 'C');
        $pdf->Cell($col_widths[1], $row_height, '', 1, 0, 'C');
        $pdf->Cell($col_widths[2], $row_height, $item['item_code'], 1, 0, 'C');
        $pdf->Cell($col_widths[3], $row_height, $item['item_name'], 1, 0, 'C');
        $pdf->Cell($col_widths[4], $row_height, number_format($item['unit_price'], 2), 1, 0, 'C');
        $pdf->Cell($col_widths[5], $row_height, $item['description'], 1, 0, 'C');
        $pdf->Cell($col_widths[6], $row_height, $item['remark'], 1, 1, 'C');
    }

    // Line break after each category
    $pdf->Ln(1); // Small line break
}

// Footer Section
$pdf->Ln(5); // Line break before footer
$pdf->SetFont('Arial', 'I', 6); // Footer font
$pdf->Cell(190, 6, 'End of Report', 0, 1, 'C');

// Output the generated PDF
$pdf->Output();
?>
