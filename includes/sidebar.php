<?php
// File: smart-udhar-system/includes/sidebar.php
// Get current page to set active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-header-content">
            <h4><i class="bi bi-wallet2"></i> Smart Udhar</h4>
            <div class="shop-name">
                <?php echo htmlspecialchars($_SESSION['shop_name'] ?? 'Smart Udhar'); ?>
            </div>
        </div>
        <button class="sidebar-toggle-btn" id="sidebarToggle">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                <i class="bi bi-people-fill"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'items.php' ? 'active' : ''; ?>" href="items.php">
                <i class="bi bi-box-seam"></i> Items
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'udhar.php' ? 'active' : ''; ?>" href="udhar.php">
                <i class="bi bi-credit-card"></i> Udhar Entry
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                <i class="bi bi-cash-stack"></i> Payments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'reminders.php' ? 'active' : ''; ?>" href="reminders.php">
                <i class="bi bi-bell-fill"></i> Reminders
            </a>
        </li>
        <li class="nav-item">
            <div class="dropdown-divider"></div>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                <i class="bi bi-person-circle"></i> Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>

    <div class="sidebar-footer text-center mt-4">
        <small class="text-muted">
            Version 1.0<br>
            &copy; <?php echo date('Y'); ?>
        </small>
    </div>
</div>