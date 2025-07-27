
<?php
include('admin/includes/dbc.php');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_ID = trim($_POST['employee_ID']);
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $connect->prepare("SELECT * FROM users WHERE employee_ID=? AND username=?");
    $stmt->bind_param("ss", $employee_ID, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        // Generate a reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token and expiry in DB (add columns if needed)
        $update = $connect->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE employee_ID=?");
        $update->bind_param("sss", $token, $expires, $employee_ID);
        $update->execute();

        // Show reset link (for demo; in production, email this link)
        $reset_link = "http://localhost/SLPA-Update/new_password.php?token=$token";
        $success = "Password reset link: <a href='$reset_link'>$reset_link</a> (valid for 1 hour)";
    } else {
        $error = "No user found with that Employee ID and Email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .box { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); width: 350px; }
        .box h2 { text-align: center; }
        .box input, .box button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc; }
        .error { color: #d9534f; text-align: center; }
        .success { color: #28a745; text-align: center; }
    </style>
</head>
<body>
<div class="box">
    <h2>Reset Password</h2>
    <form method="post">
        <input type="text" name="employee_ID" placeholder="Employee ID" required>
        <input type="text" name="email" placeholder="Registered Email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <p><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>