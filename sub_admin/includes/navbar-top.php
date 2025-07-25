<?php
session_start();
$username = $_SESSION['username'] ?? 'User';
?>

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary shadow-sm">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3 fw-bold" href="dashbord_user.php" style="letter-spacing:1px;">
        <i class="fas fa-ship me-2"></i>SLPA
    </a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-white" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>
    <!-- Navbar Search-->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control border-0 shadow-none" type="text" placeholder="Search..." aria-label="Search" aria-describedby="btnNavbarSearch" />
            <button class="btn btn-light" id="btnNavbarSearch" type="button"><i class="fas fa-search text-primary"></i></button>
        </div>
    </form>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-lg me-2"></i>
                <span class="d-none d-lg-inline fw-semibold"><?= htmlspecialchars($username) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                <li>
                    <h6 class="dropdown-header text-primary">
                        <i class="fas fa-user me-2"></i><?= htmlspecialchars($username) ?>
                    </h6>
                </li>
                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cogs me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item text-danger" href="/SLPA-Update/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<!-- Font Awesome CDN (if not already included) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .sb-topnav {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 1.08rem;
        background: linear-gradient(90deg, #0d2957 80%, #00509e 100%) !important;
        border-bottom: 2px solid #00509e;
    }
    .navbar-brand {
        font-size: 1.5rem;
        color: #fff !important;
    }
    .navbar-brand:hover {
        color: #ffd700 !important;
    }
    .navbar-nav .nav-link {
        color: #fff !important;
        font-weight: 500;
    }
    .navbar-nav .nav-link:hover, .navbar-nav .nav-link:focus {
        color: #ffd700 !important;
    }
    .dropdown-menu {
        min-width: 220px;
        border-radius: 0.5rem;
    }
    .dropdown-header {
        font-size: 1rem;
        font-weight: 600;
        background: #f4f6f9;
    }
    .dropdown-item i {
        width: 22px;
        text-align: center;
    }
    .btn-link {
        color: #fff !important;
    }
    .btn-link:hover {
        color: #ffd700 !important;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #00509e;
    }
    .btn-light {
        background: #fff;
        color: #0d2957;
        border: none;
    }
    .btn-light:hover {
        background: #e9ecef;
        color: #00509e;
    }
</style>