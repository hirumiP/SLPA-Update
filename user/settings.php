<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .settings-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(13,41,87,0.10);
            padding: 2.5rem 2rem;
        }
        .settings-title {
            letter-spacing: 1px;
            font-weight: 700;
            color: #0d2957;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background: #0d2957;
            border: none;
        }
        .btn-primary:hover {
            background: #00509e;
        }
    </style>
</head>
<body>
    <?php include('includes/navbar-top.php'); ?>
    <div class="settings-container">
        <h2 class="settings-title mb-4"><i class="fas fa-cog me-2"></i>User Settings</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?= $username ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="user@email.com" disabled>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Change Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="New password" disabled>
            </div>
            <div class="mb-3">
                <label for="theme" class="form-label">Theme</label>
                <select class="form-select" id="theme" name="theme" disabled>
                    <option selected>Default</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
        </form>
    </div>
</body>
</html>