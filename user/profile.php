<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);
// You can fetch more user info from the database here if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(13,41,87,0.10);
            padding: 2.5rem 2rem;
        }
        .profile-title {
            letter-spacing: 1px;
            font-weight: 700;
            color: #0d2957;
        }
        .profile-icon {
            font-size: 3rem;
            color: #0d2957;
        }
        .profile-label {
            font-weight: 500;
            color: #00509e;
        }
    </style>
</head>
<body>
    <?php include('includes/navbar-top.php'); ?>
    <div class="profile-container">
        <div class="text-center mb-4">
            <i class="fas fa-user-circle profile-icon"></i>
            <h2 class="profile-title mt-2">User Profile</h2>
        </div>
        <div class="mb-3">
            <label class="profile-label">Username</label>
            <div class="form-control bg-light"><?= $username ?></div>
        </div>
        <div class="mb-3">
            <label class="profile-label">Role</label>
            <div class="form-control bg-light">User</div>
        </div>
        <!-- Add more profile fields here if needed -->
        <div class="mt-4 text-center">
            <a href="settings.php" class="btn btn-primary"><i class="fas fa-cogs me-2"></i>Settings</a>
        </div>
    </div>
</body>
</html>