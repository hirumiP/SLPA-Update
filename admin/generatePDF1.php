<?php
require('./fpdf/fpdf.php');
include('includes/dbc.php');

if (isset($_POST['generate_report'])) {
    $budget1_type = $_POST['budget1_type'];
    $budget1_year = $_POST['budget1_year'];
    $budget2_type = $_POST['budget2_type'];
    $budget2_year = $_POST['budget2_year'];

    $query_budget1 = "SELECT ir.division, i.name AS item_name, ir.unit_price, ir.quantity, ir.description, ir.remark, (ir.unit_price * ir.quantity) AS total_cost
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        WHERE ir.year = '$budget1_year' AND ir.budget_id = '$budget1_type'
        ORDER BY ir.division";

    $query_budget2 = "SELECT ir.division, i.name AS item_name, ir.unit_price, ir.quantity, ir.description, ir.remark, (ir.unit_price * ir.quantity) AS total_cost
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        WHERE ir.year = '$budget2_year' AND ir.budget_id = '$budget2_type'
        ORDER BY ir.division";

    $result1 = mysqli_query($connect, $query_budget1);
    $result2 = mysqli_query($connect, $query_budget2);

    if (!$result1 || !$result2) {
        die("Query failed: " . mysqli_error($connect));
    }

    $budget1_data = [];
    $budget2_data = [];

    while ($row = mysqli_fetch_assoc($result1)) {
        $budget1_data[] = $row;
    }
    while ($row = mysqli_fetch_assoc($result2)) {
        $budget2_data[] = $row;
    }

    // Now generate PDF
    class PDF extends FPDF {
        function BudgetTable($header, $data, $title) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, $title, 0, 1, 'C');
            $this->SetFont('Arial', 'B', 10);
            foreach ($header as $col) {
                $this->Cell(30, 7, $col, 1);
            }
            $this->Ln();
            $this->SetFont('Arial', '', 10);
            foreach ($data as $row) {
                $this->Cell(30, 6, $row['division'], 1);
                $this->Cell(30, 6, $row['item_name'], 1);
                $this->Cell(30, 6, $row['unit_price'], 1);
                $this->Cell(30, 6, $row['quantity'], 1);
                $this->Cell(30, 6, $row['total_cost'], 1);
                $this->Ln();
            }
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();

    $headers = ['Division', 'Item', 'Unit Price', 'Qty', 'Total'];
    $pdf->BudgetTable($headers, $budget1_data, "Budget 1: $budget1_type ($budget1_year)");
    $pdf->Ln(10);
    $pdf->BudgetTable($headers, $budget2_data, "Budget 2: $budget2_type ($budget2_year)");

    $pdf->Output();
}
?>
