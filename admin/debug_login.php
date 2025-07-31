<?php
// filepath: c:\xampp\htdocs\SLPA-Update\admin\debug_login.php
include('includes/dbc.php'); // Fixed path - remove 'admin/'

echo "<h2>Login Debug Information</h2>";

// Test database connection
if ($connect) {
    echo "✅ Database connected successfully<br><br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
    exit;
}

// Check if users table exists
$table_check = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ Users table exists<br><br>";
} else {
    echo "❌ Users table does not exist<br><br>";
    exit;
}

// Show all users
echo "<h3>All Users in Database:</h3>";
$sql = "SELECT employee_ID, username, pwd, role, status, division FROM users";
$result = mysqli_query($connect, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Employee ID</th><th>Username</th><th>Role</th><th>Status</th><th>Division</th><th>Password Hash</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['employee_ID'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['division'] . "</td>";
        echo "<td>" . substr($row['pwd'], 0, 30) . "...</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "❌ No users found in database<br><br>";
}

// Test password verification
echo "<h3>Password Verification Test:</h3>";
$test_password = "admin123";
$test_hash = '$2y$10$N9qo8uLOickgx2ZMRZoMye7VZbIRLU3MMpnKGgKjKyKX8fLznW.W2';

echo "Testing password: <strong>" . $test_password . "</strong><br>";
echo "Against hash: " . $test_hash . "<br>";
echo "Result: " . (password_verify($test_password, $test_hash) ? "✅ MATCH" : "❌ NO MATCH") . "<br><br>";

// Generate a fresh password hash
$fresh_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "Freshly generated hash: " . $fresh_hash . "<br>";
echo "Fresh hash verification: " . (password_verify($test_password, $fresh_hash) ? "✅ MATCH" : "❌ NO MATCH") . "<br><br>";

// Test specific user lookup
echo "<h3>Test User Lookup:</h3>";
$test_employee_id = "001";
$lookup_sql = "SELECT employee_ID, username, pwd, role, status FROM users WHERE employee_ID = ?";
$stmt = mysqli_prepare($connect, $lookup_sql);
mysqli_stmt_bind_param($stmt, "s", $test_employee_id);
mysqli_stmt_execute($stmt);
$lookup_result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($lookup_result)) {
    echo "✅ Found user: " . $user['username'] . "<br>";
    echo "Employee ID: " . $user['employee_ID'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "Status: " . $user['status'] . "<br>";
    echo "Password verification: " . (password_verify($test_password, $user['pwd']) ? "✅ MATCH" : "❌ NO MATCH") . "<br>";
} else {
    echo "❌ User with Employee ID '$test_employee_id' not found<br>";
}
?>