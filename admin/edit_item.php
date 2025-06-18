<?php
include('includes/dbc.php');
include('includes/header.php');

// Check if 'id' exists in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request. Item ID not found.");
}

$item_id = $_GET['id']; // Get the item ID from the URL

// Fetch item details
$query = "SELECT item_code, category_code, name, unit_price, qty_in_hand, description, remark, created_at 
          FROM items WHERE item_code = ?";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "s", $item_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    die("Error: Item not found.");
}

mysqli_stmt_bind_result($stmt, $item_code, $category_code, $name, $unit_price, $qty_in_hand, $description, $remark, $created_at);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Item stock - <?php echo htmlspecialchars($name); ?></h2>
    
    <table class="table table-bordered mt-3">
        <tr>
            <th>Item Code</th>
            <td><?php echo htmlspecialchars($item_code); ?></td>
        </tr>
        <tr>
            <th>Category Code</th>
            <td><?php echo htmlspecialchars($category_code); ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?php echo htmlspecialchars($name); ?></td>
        </tr>
        <tr>
            <th>Unit Price</th>
            <td><?php echo htmlspecialchars(number_format($unit_price, 2)); ?></td>
        </tr>
        <tr>
            <th>Quantity in Hand</th>
            <td><?php echo htmlspecialchars($qty_in_hand); ?></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><?php echo htmlspecialchars($description); ?></td>
        </tr>
        <tr>
            <th>Remark</th>
            <td><?php echo htmlspecialchars($remark); ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?php echo htmlspecialchars($created_at); ?></td>
        </tr>
    </table>

    <!-- Update Stock Form -->
    <form action="update_stock.php" method="POST">
        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item_code); ?>">
        
        <div class="mb-3">
            <label for="new_stock" class="form-label">New Quantity in Hand</label>
            <input type="number" class="form-control" id="new_stock" name="new_stock" value="<?php echo htmlspecialchars($qty_in_hand); ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Stock</button>
    </form>
</div>
</body>
</html>
