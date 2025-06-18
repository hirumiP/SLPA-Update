<?php
include('admin/includes/dbc.php');

// Example user data
$employee_ID = 'EMP006';
$username = 'john_doe';
$password = password_hash('securepassword', PASSWORD_BCRYPT); // Hash the password
$role = 'user';

// Insert the new user
$sql = "INSERT INTO users (employee_ID, username, pwd, role) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $employee_ID, $username, $password, $role);
if (mysqli_stmt_execute($stmt)) {
    echo "User added successfully!";
} else {
    echo "Error: " . mysqli_error($connect);
}
?>
