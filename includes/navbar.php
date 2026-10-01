<?php
/**
 * Public Navbar Template
 * E-Vaccination Management System
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="<?php echo base_url('index.php'); ?>">
            <div class="brand-icon">
                <i class="bi bi-shield-plus"></i>
            </div>
            <span>E-Vaccination</span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>" href="<?php echo base_url('index.php'); ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="<?php echo base_url('about.php'); ?>">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'vaccines.php') ? 'active' : ''; ?>" href="<?php echo base_url('vaccines.php'); ?>">Vaccines</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'hospitals.php') ? 'active' : ''; ?>" href="<?php echo base_url('hospitals.php'); ?>">Hospitals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'vaccination-schedule.php') ? 'active' : ''; ?>" href="<?php echo base_url('vaccination-schedule.php'); ?>">Schedule</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'how-it-works.php') ? 'active' : ''; ?>" href="<?php echo base_url('how-it-works.php'); ?>">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="<?php echo base_url('contact.php'); ?>">Contact</a>
                </li>
            </ul>

            <!-- Auth Buttons / User State -->
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in()): ?>
                    <?php 
                        $role = current_user_role();
                        $dash_link = base_url($role . '/dashboard.php');
                    ?>
                    <a href="<?php echo $dash_link; ?>" class="btn btn-emerald btn-sm">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                    <a href="<?php echo base_url('logout.php'); ?>" class="btn btn-outline-secondary btn-sm" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo base_url('login.php'); ?>" class="btn btn-outline-emerald btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                    <a href="<?php echo base_url('register.php'); ?>" class="btn btn-coral btn-sm">
                        <i class="bi bi-person-plus-fill me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

