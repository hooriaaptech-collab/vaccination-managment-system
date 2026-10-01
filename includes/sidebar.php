<?php
/**
 * Unified Dashboard Sidebar Include
 * Dynamically renders navigation based on current role (Admin, Parent, Hospital)
 * Fully Responsive with Mobile Drawer & Backdrop
 * E-Vaccination Management System
 */

$user_role = current_user_role();
$user_name = current_user_name();
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- Mobile Backdrop Overlay -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="dashboard-sidebar" id="dashboardSidebar">
    <!-- Brand Logo & Mobile Close Button -->
    <div class="sidebar-brand-wrapper">
        <a href="<?php echo base_url('index.php'); ?>" class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-shield-plus"></i>
            </div>
            <span>E-Vaccination</span>
        </a>
        <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close Sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Role Indicator Badge -->
    <div class="sidebar-role-badge">
        <div>
            <div class="sidebar-role-title">Active Portal</div>
            <div class="sidebar-role-name">
                <?php 
                    if ($user_role === 'admin') echo '<i class="bi bi-shield-lock-fill text-danger me-1"></i> Admin Portal';
                    elseif ($user_role === 'hospital') echo '<i class="bi bi-hospital-fill text-teal me-1"></i> Hospital Portal';
                    else echo '<i class="bi bi-person-heart text-success me-1"></i> Parent Portal';
                ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Navigation Menu -->
    <ul class="sidebar-menu">
        <div class="sidebar-section-label">Main Navigation</div>

        <?php if ($user_role === 'admin'): ?>
            <!-- ADMIN NAVIGATION -->
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/requests.php'); ?>" class="sidebar-link <?php echo ($current_page == 'requests.php') ? 'active' : ''; ?>">
                    <i class="bi bi-bell-fill"></i>
                    <span>Parent Requests</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/bookings.php'); ?>" class="sidebar-link <?php echo ($current_page == 'bookings.php') ? 'active' : ''; ?>">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>All Bookings</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/children.php'); ?>" class="sidebar-link <?php echo ($current_page == 'children.php' || $current_page == 'child-details.php') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Child Management</span>
                </a>
            </li>
            
            <div class="sidebar-section-label mt-2">Catalogs & Master Data</div>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/vaccines.php'); ?>" class="sidebar-link <?php echo ($current_page == 'vaccines.php') ? 'active' : ''; ?>">
                    <i class="bi bi-capsule"></i>
                    <span>Vaccine Catalog</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/hospitals.php'); ?>" class="sidebar-link <?php echo ($current_page == 'hospitals.php') ? 'active' : ''; ?>">
                    <i class="bi bi-hospital"></i>
                    <span>Hospital Centers</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/users.php'); ?>" class="sidebar-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>User Accounts</span>
                </a>
            </li>
            
            <div class="sidebar-section-label mt-2">Reports & Profile</div>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/reports.php'); ?>" class="sidebar-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Reports & Analytics</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('admin/profile.php'); ?>" class="sidebar-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>Account Settings</span>
                </a>
            </li>

        <?php elseif ($user_role === 'parent'): ?>
            <!-- PARENT NAVIGATION -->
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('parent/dashboard.php'); ?>" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>My Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('parent/children.php'); ?>" class="sidebar-link <?php echo ($current_page == 'children.php' || $current_page == 'add-child.php' || $current_page == 'edit-child.php' || $current_page == 'child-profile.php') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>My Children</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('parent/booking.php'); ?>" class="sidebar-link <?php echo ($current_page == 'booking.php') ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-plus-fill"></i>
                    <span>Book Appointment</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('parent/requests.php'); ?>" class="sidebar-link <?php echo ($current_page == 'requests.php') ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Track Requests</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('parent/vaccination-history.php'); ?>" class="sidebar-link <?php echo ($current_page == 'vaccination-history.php') ? 'active' : ''; ?>">
                    <i class="bi bi-award-fill"></i>
                    <span>Vaccination History</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('parent/hospitals.php'); ?>" class="sidebar-link <?php echo ($current_page == 'hospitals.php') ? 'active' : ''; ?>">
                    <i class="bi bi-hospital"></i>
                    <span>Search Hospitals</span>
                </a>
            </li>
            
            <div class="sidebar-section-label mt-2">Account</div>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('parent/profile.php'); ?>" class="sidebar-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                    <i class="bi bi-person-gear"></i>
                    <span>My Profile</span>
                </a>
            </li>

        <?php elseif ($user_role === 'hospital'): ?>
            <!-- HOSPITAL NAVIGATION -->
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('hospital/dashboard.php'); ?>" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('hospital/appointments.php'); ?>" class="sidebar-link <?php echo ($current_page == 'appointments.php') ? 'active' : ''; ?>">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('hospital/vaccines.php'); ?>" class="sidebar-link <?php echo ($current_page == 'vaccines.php') ? 'active' : ''; ?>">
                    <i class="bi bi-capsule"></i>
                    <span>Vaccine Stock</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('hospital/vaccination-records.php'); ?>" class="sidebar-link <?php echo ($current_page == 'vaccination-records.php') ? 'active' : ''; ?>">
                    <i class="bi bi-journal-medical"></i>
                    <span>Administered Records</span>
                </a>
            </li>
            
            <div class="sidebar-section-label mt-2">Settings</div>
            <li class="sidebar-menu-item">
                <a href="<?php echo base_url('hospital/profile.php'); ?>" class="sidebar-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                    <i class="bi bi-hospital"></i>
                    <span>Hospital Profile</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="sidebar-menu-item mt-3">
            <a href="<?php echo base_url('index.php'); ?>" class="sidebar-link text-muted" target="_blank">
                <i class="bi bi-globe"></i>
                <span>Public Website <i class="bi bi-arrow-up-right small"></i></span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Bottom / Logout -->
    <div class="sidebar-footer">
        <a href="<?php echo base_url('logout.php'); ?>" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </div>
</aside>
