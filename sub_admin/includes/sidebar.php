<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="dashbord_user.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <div class="sb-sidenav-menu-heading">Interface</div>
                <!--<a class="nav-link" href="eq_plan.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                    EQ Plan
                </a>-->
                <a class="nav-link" href="item_req.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-map"></i></div>
                    Item Requests
                </a>
                 <a class="nav-link" href="print_items.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-map"></i></div>
                    Print Item Requests
                </a>
                <a class="nav-link" href="addCategory.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    Setting
                </a>
                <a class="nav-link" href="aboutus.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    About Us
                </a>
                <a class="nav-link" href="contactus.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                    Contact Us
                </a>
                
            </div>
        </div>
        <div class="sb-sidenav-footer">
    <div class="small">Logged in as:</div>
    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>
</div>
    </nav>
</div>
