<?php
// Admin Sidebar Navigation
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-shield-alt"></i>
        <span>Tagum City Rent Car Jhunrider</span>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/rentals.php" class="<?php echo ($current_page === 'rentals.php' || $current_page === 'rental-details.php') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> Rentals
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/vehicles.php" class="<?php echo $current_page === 'vehicles.php' ? 'active' : ''; ?>">
                <i class="fas fa-car"></i> Vehicles
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/customers.php" class="<?php echo $current_page === 'customers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Customers
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/damage-reports.php" class="<?php echo $current_page === 'damage-reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-triangle"></i> Damage Reports
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/schedules.php" class="<?php echo $current_page === 'schedules.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar"></i> Schedules
            </a>
        </li>
        <li style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <a href="<?php echo BASE_URL; ?>auth/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
        <!-- Add this inside the sidebar-nav list -->
<li>
    <a href="<?php echo BASE_URL; ?>admin/sos-alerts.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'sos-alerts.php' ? 'active' : ''; ?>">
        <i class="fas fa-exclamation-triangle"></i> SOS Alerts
    </a>
</li>
    </ul>
</div>

<style>
.sidebar {
    background: linear-gradient(135deg, #0A2540 0%, #1E2937 100%);
    color: white;
    min-height: 100vh;
    padding: 20px 0;
    position: fixed;
    width: 280px;
    left: 0;
    top: 0;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.sidebar-brand {
    padding: 20px 25px;
    font-weight: 700;
    font-size: 22px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}

.sidebar-brand i {
    font-size: 28px;
}

.sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin: 5px 0;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 25px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    border-left: 4px solid transparent;
}

.sidebar-nav a:hover {
    background: rgba(16, 185, 129, 0.1);
    color: white;
    border-left-color: #10B981;
}

.sidebar-nav a.active {
    background: rgba(16, 185, 129, 0.15);
    color: white;
    border-left-color: #10B981;
    font-weight: 600;
}

.sidebar-nav i {
    width: 24px;
    font-size: 18px;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
        min-height: auto;
    }
    
    .main-content {
        margin-left: 0 !important;
    }
}
</style>