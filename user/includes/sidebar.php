<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark bg-primary" id="sidenavAccordion" style="min-height:100vh;">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading text-uppercase text-light" style="letter-spacing:1px;">Core</div>
                <a class="nav-link d-flex align-items-center" href="dashbord_user.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-tachometer-alt"></i></div>
                    <span>Dashboard</span>
                </a>
                <div class="sb-sidenav-menu-heading text-uppercase text-light" style="letter-spacing:1px;">Interface</div>
                <a class="nav-link d-flex align-items-center" href="eq_plan.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-user-plus"></i></div>
                    <span>Item Requests</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="item-request.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-map"></i></div>
                    <span>Add Item Request</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="addCategory.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-folder-plus"></i></div>
                    <span>Setting</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="aboutus.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-info-circle"></i></div>
                    <span>About Us</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="contactus.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-envelope"></i></div>
                    <span>Contact Us</span>
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer bg-dark text-light d-flex align-items-center">
            <div class="sb-nav-link-icon me-2"><i class="fas fa-user-circle"></i></div>
            <div>
                <div class="small">Logged in as:</div>
                <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
            </div>
        </div>
    </nav>
</div>

<!-- Font Awesome CDN (if not already included) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .sb-sidenav {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 1.05rem;
        background: linear-gradient(135deg, #0d2957 80%, #00509e 100%);
    }
    .sb-sidenav .nav-link {
        color: #e0e6f1;
        transition: background 0.2s, color 0.2s;
        border-radius: 0.5rem;
        margin-bottom: 2px;
        padding: 0.75rem 1.2rem;
    }
    .sb-sidenav .nav-link:hover, .sb-sidenav .nav-link.active {
        background: #fff;
        color: #0d2957 !important;
        font-weight: 600;
    }
    .sb-sidenav .nav-link:hover .sb-nav-link-icon,
    .sb-sidenav .nav-link.active .sb-nav-link-icon {
        color: #0d2957 !important;
    }
    .sb-sidenav-menu-heading {
        font-size: 0.95rem;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        color: #b0c4de !important;
        letter-spacing: 1px;
    }
    .sb-nav-link-icon {
        font-size: 1.2rem;
        color: #ffd700;
        transition: color 0.2s;
    }
    .sb-sidenav-footer {
        padding: 1rem 1.2rem;
        font-size: 0.95rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>
