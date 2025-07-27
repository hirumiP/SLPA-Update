<?php
<?php
include('admin/includes/dbc.php');
$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Find user by token and check expiry
        $stmt = $connect->prepare("SELECT * FROM users WHERE reset_token=? AND reset_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $update = $connect->prepare("UPDATE users SET pwd=?, reset_token=NULL, reset_expires=NULL WHERE employee_ID=?");
            $update->bind_param("ss", $hash, $user['employee_ID']);
            $update->execute();
            $success = "Password reset successful! <a href='login.php'>Login here</a>";
        } else {
            $error = "Invalid or expired reset link.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Set New Password</title>
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
    <h2>Set New Password</h2>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    <?php if ($success) { echo "<div class='success'>$success</div>"; } else: ?>
    <form method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>