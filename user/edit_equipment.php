<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include('E:\xamp\htdocs\SLPA-Update\user\includes\dbc.php');

// Initialize variables
$id = 0;
$divisions = [];
$categories = [];
$items = [];
$division = $year = $category_code = $item_code = $available_quantity = $to_purchased = $to_condemned = $remark = "";

// Fetch dropdown options
$division_query = "SELECT DISTINCT division FROM equipment_plan";
$divisions_result = $connect->query($division_query);
if ($divisions_result) {
    while ($row = $divisions_result->fetch_assoc()) {
        $divisions[] = $row['division'];
    }
}

$category_query = "SELECT category_code, description FROM categories";
$categories_result = $connect->query($category_query);
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$item_query = "SELECT item_code, name FROM items";
$items_result = $connect->query($item_query);
if ($items_result) {
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }
}

// Check if 'id' is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input
    $query = "SELECT * FROM equipment_plan WHERE id = $id";
    $result = $connect->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $division = $row['division'];
        $year = $row['year'];
        $category_code = $row['category_code'];
        $item_code = $row['item_code'];
        $available_quantity = $row['available_quantity'];
        $to_purchased = $row['to_purchased'];
        $to_condemned = $row['to_condemned'];
        $remark = $row['remark'];
    } else {
        echo "<div class='alert alert-danger'>Record not found!</div>";
        exit();
    }
}

// Handle form submission for updating the record
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $division = $_POST['division'];
    $year = $_POST['year'];
    $category_code = $_POST['category'];
    $item_code = $_POST['item'];
    $available_quantity = $_POST['available_quantity'];
    $to_purchased = $_POST['to_purchased'];
    $to_condemned = $_POST['to_condemned'];
    $remark = $_POST['remark'];

    $update_query = "UPDATE equipment_plan 
                     SET division = '$division', year = '$year', category_code = '$category_code', 
                         item_code = '$item_code', available_quantity = '$available_quantity', 
                         to_purchased = '$to_purchased', to_condemned = '$to_condemned', remark = '$remark'
                     WHERE id = $id";

    if ($connect->query($update_query) === TRUE) {
        header("Location: equipment_plan.php?status=updated");
    } else {
        echo "<div class='alert alert-danger'>Error updating record: " . $connect->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid px-4">
    <div class="container">
        <br>
        <h2 class="text-center mb-4">Edit Equipment</h2>
        <br>
        <form method="POST" class="row g-3 shadow-lg p-5 border border-2 border-primary rounded-3 bg-light">
            <!-- Division Dropdown -->
            <div class="col-md-4">
                <label for="division" class="form-label">Division</label>
                <select id="division" name="division" class="form-select" required>
                    <option selected disabled>Select Division</option>
                    <?php foreach ($divisions as $div): ?>
                        <option value="<?= $div; ?>" <?= ($div === $division) ? 'selected' : ''; ?>>
                            <?= $div; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Year Input -->
            <div class="col-md-4">
                <label for="year" class="form-label">Year</label>
                <input type="number" class="form-control" id="year" name="year" value="<?= $year; ?>" required>
            </div>

            <!-- Category Dropdown -->
            <div class="col-md-4">
                <label for="category" class="form-label">Category</label>
                <select id="category" name="category" class="form-select" required>
                    <option selected disabled>Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_code']; ?>" <?= ($category['category_code'] === $category_code) ? 'selected' : ''; ?>>
                            <?= $category['category_code']; ?> - <?= $category['description']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Item Dropdown -->
            <div class="col-md-4">
                <label for="item" class="form-label">Item</label>
                <select id="item" name="item" class="form-select" required>
                    <option selected disabled>Select Item</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_code']; ?>" <?= ($item['item_code'] === $item_code) ? 'selected' : ''; ?>>
                            <?= $item['item_code']; ?> - <?= $item['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Available Quantity -->
            <div class="col-md-4">
                <label for="available_quantity" class="form-label">Available Quantity</label>
                <input type="number" class="form-control" id="available_quantity" name="available_quantity" value="<?= $available_quantity; ?>" required>
            </div>

            <!-- To Purchase -->
            <div class="col-md-4">
                <label for="to_purchased" class="form-label">To Purchased</label>
                <input type="number" class="form-control" id="to_purchased" name="to_purchased" value="<?= $to_purchased; ?>" required>
            </div>

            <!-- To Condemn -->
            <div class="col-md-4">
                <label for="to_condemned" class="form-label">To Condemned</label>
                <input type="number" class="form-control" id="to_condemned" name="to_condemned" value="<?= $to_condemned; ?>" required>
            </div>

            <!-- Remarks -->
            <div class="col-md-6">
                <label for="remark" class="form-label">Remark</label>
                <textarea class="form-control" id="remark" name="remark"><?= $remark; ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary px-5 py-2">Update</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
