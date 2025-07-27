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
    // Convert HTML5 datetime-local to MySQL DATETIME format
    $access_start = str_replace('T', ' ', $_POST['access_start']) . ':00';
    $access_end = str_replace('T', ' ', $_POST['access_end']) . ':00';

    // Save or update access period for the selected year and budget
    $stmt = $connect->prepare(
        "REPLACE INTO access_control (year, budget, access_start, access_end) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param('ssss', $selected_year, $selected_budget, $access_start, $access_end);
    $stmt->execute();
    $msg = "Access period updated for $selected_year / $selected_budget!";

    // If "Apply to All" button was clicked, update all users and sub_admins
    if (isset($_POST['apply_all']) && $_POST['apply_all'] == '1') {
        $update = $connect->prepare(
            "UPDATE users SET access_start=?, access_end=? WHERE role IN ('user', 'sub_admin')"
        );
        $update->bind_param('ss', $access_start, $access_end);
        $update->execute();
        $msg .= " Access period applied to all users and sub admins!";
    }
}

// Fetch years and budgets for dropdowns
$years = $connect->query("SELECT DISTINCT year FROM item_requests ORDER BY year DESC");
$budgets = $connect->query("SELECT DISTINCT budget FROM budget ORDER BY budget ASC");

// Fetch all access control details
$access_details = $connect->query("SELECT year, budget, access_start, access_end FROM access_control ORDER BY year DESC, budget ASC");
?>

<div class="container mt-5">
    <h2 class="mb-4 text-primary"><i class="fas fa-user-shield me-2"></i>System Access Control Panel</h2>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3 bg-white p-4 rounded shadow-sm mb-4">
        <div class="col-md-3">
            <label for="year" class="form-label">Select Year</label>
            <select name="year" id="year" class="form-select" required>
                <option value="">-- Select Year --</option>
                <?php
                $years->data_seek(0);
                while ($row = $years->fetch_assoc()): ?>
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
                <?php
                $budgets->data_seek(0);
                while ($row = $budgets->fetch_assoc()): ?>
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
            <button type="submit" name="apply_all" value="1" class="btn btn-success px-4 ms-2">
                <i class="fas fa-users me-2"></i>Apply to All Users & Sub Admins
            </button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold">
            Current Access Periods
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Year</th>
                        <th>Budget</th>
                        <th>Access Start</th>
                        <th>Access End</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($access_details->num_rows > 0): ?>
                        <?php while ($row = $access_details->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['year']) ?></td>
                                <td><?= htmlspecialchars($row['budget']) ?></td>
                                <td><?= htmlspecialchars($row['access_start']) ?></td>
                                <td><?= htmlspecialchars($row['access_end']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No access periods set.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include('includes/scripts.php'); ?>