<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="dashbord.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <div class="sb-sidenav-menu-heading">Interface</div>
                <a class="nav-link" href="add-user.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                    Add User
                </a>
                <a class="nav-link" href="add-division.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-map"></i></div>
                    Add Division
                </a>
                <a class="nav-link" href="add-item.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                    Add Item
                </a>
                <a class="nav-link" href="addCategory.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    Add Category
                </a>
                <a class="nav-link" href="item_recieved.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    Item Recieved
                </a>
                <a class="nav-link" href="item_issue.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    Item Issues
                </a>
                <a class="nav-link" href="condemn_item.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    Condemn Item
                </a>
                <a class="nav-link" href="reports.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    Reports
                </a>
                
                
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>
        </div>
    </nav>
</div>
