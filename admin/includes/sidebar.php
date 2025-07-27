<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark bg-primary" id="sidenavAccordion" style="min-height:100vh;">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading text-uppercase text-light" style="letter-spacing:1px;">Core</div>
                <a class="nav-link d-flex align-items-center" href="dashbord.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-tachometer-alt"></i></div>
                    <span>Dashboard</span>
                </a>
                <div class="sb-sidenav-menu-heading text-uppercase text-light" style="letter-spacing:1px;">Management</div>
                <a class="nav-link d-flex align-items-center" href="add-user.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-user-plus"></i></div>
                    <span>Add User</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="add-division.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-map"></i></div>
                    <span>Add Division</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="add-item.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-box"></i></div>
                    <span>Add Item</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="addCategory.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-folder-plus"></i></div>
                    <span>Add Category</span>
                </a>
                <div class="sb-sidenav-menu-heading text-uppercase text-light" style="letter-spacing:1px;">Reports</div>
                <a class="nav-link d-flex align-items-center" href="reports.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-chart-bar"></i></div>
                    <span>Reports</span>
                </a>
                <div class="sb-sidenav-menu-heading text-uppercase text-light" style="letter-spacing:1px;">Inventory</div>
                <a class="nav-link d-flex align-items-center" href="item_recieved.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-arrow-down"></i></div>
                    <span>Item Received</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="item_issue.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-arrow-up"></i></div>
                    <span>Item Issues</span>
                </a>
                <a class="nav-link d-flex align-items-center" href="condemn_item.php">
                    <div class="sb-nav-link-icon me-2"><i class="fas fa-ban"></i></div>
                    <span>Condemn Item</span>
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer bg-dark text-light">
            <div class="small">Logged in as:</div>
            <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
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
        color: #0d2957 !important; /* Make font dark blue on hover */
        font-weight: 600;
    }
    .sb-sidenav .nav-link:hover .sb-nav-link-icon,
    .sb-sidenav .nav-link.active .sb-nav-link-icon {
        color: #0d2957 !important; /* Make icon dark blue on hover */
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
