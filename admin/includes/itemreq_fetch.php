<?php
include('includes/dbc.php');

// Get Filters from Request
$selectedDivision = $_GET['division'] ?? null;
$selectedBudget = $_GET['budget'] ?? null;

// Base Query
$query = "
    SELECT items.item_code, items.item_name, divisions.division_name, budget.budget
    FROM items
    INNER JOIN divisions ON items.division_name = divisions.division_name
    INNER JOIN budget ON items.budget_id = budget.id
    WHERE 1=1
";

// Apply Filters
if (!empty($selectedDivision)) {
    $query .= " AND items.division_name = '" . mysqli_real_escape_string($connect, $selectedDivision) . "'";
}
if (!empty($selectedBudget)) {
    $query .= " AND items.budget_id = " . intval($selectedBudget);
}

// Execute Query
$result = mysqli_query($connect, $query);
if (!$result) {
    echo '<p class="text-danger">Error executing query: ' . mysqli_error($connect) . '</p>';
    exit;
}
$filteredData = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Generate Table
if (count($filteredData) > 0): ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Division</th>
                <th>Budget</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filteredData as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item_code']) ?></td>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= htmlspecialchars($row['division_name']) ?></td>
                    <td><?= htmlspecialchars($row['budget']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No records found for the selected filters.</p>
<?php endif; ?>
