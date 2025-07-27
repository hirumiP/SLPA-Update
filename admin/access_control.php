<?php
session_start();
if (!isset($_SESSION['employee_ID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include('includes/dbc.php');
include('includes/header.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_year = $_POST['year'];
    $selected_budget = $_POST['budget'];
    $access_start = $_POST['access_start'];
    $access_end = $_POST['access_end'];

    // Save or update access period for the selected year and budget
    $stmt = $connect->prepare(
        "REPLACE INTO access_control (year, budget, access_start, access_end) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param('ssss', $selected_year, $selected_budget, $access_start, $access_end);
    $stmt->execute();
    $msg = "Access period updated successfully!";
}

// Fetch years and budgets for dropdowns
$years = $connect->query("SELECT DISTINCT year FROM item_requests ORDER BY year DESC");
$budgets = $connect->query("SELECT DISTINCT budget FROM budget ORDER BY budget ASC");
?>

<div class="container mt-5">
    <h2 class="mb-4 text-primary"><i class="fas fa-user-shield me-2"></i>System Access Control Panel</h2>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3 bg-white p-4 rounded shadow-sm">
        <div class="col-md-3">
            <label for="year" class="form-label">Select Year</label>
            <select name="year" id="year" class="form-select" required>
                <option value="">-- Select Year --</option>
                <?php while ($row = $years->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['year']) ?>">
                        <?= htmlspecialchars($row['year']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="budget" class="form-label">Select Budget</label>
            <select name="budget" id="budget" class="form-select" required>
                <option value="">-- Select Budget --</option>
                <?php while ($row = $budgets->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['budget']) ?>">
                        <?= htmlspecialchars($row['budget']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="access_start" class="form-label">Access Start</label>
            <input type="datetime-local" name="access_start" id="access_start" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label for="access_end" class="form-label">Access End</label>
            <input type="datetime-local" name="access_end" id="access_end" class="form-control" required>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>Update Access
            </button>
        </div>
    </form>
</div>
<?php include('includes/scripts.php'); ?>