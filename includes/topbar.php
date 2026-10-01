<?php
/**
 * Unified Dashboard Topbar Include
 * Responsive with Adaptive Header & Touch Dropdown
 * E-Vaccination Management System
 */

$user_role = current_user_role();
$user_name = current_user_name();
$user_email = current_user_email();
$profile_link = base_url($user_role . '/profile.php');
?>
<header class="dashboard-topbar">
    <div class="d-flex align-items-center gap-2 gap-sm-3 text-truncate">
        <!-- Mobile Sidebar Toggle -->
        <button class="topbar-toggle" type="button" aria-label="Toggle Navigation Menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="text-truncate">
            <h5 class="mb-0 fw-bold font-outfit text-dark text-truncate" style="font-size: clamp(0.95rem, 3.5vw, 1.15rem);"><?php echo htmlspecialchars($page_header ?? 'Dashboard Overview'); ?></h5>
            <span class="text-muted small d-none d-md-inline text-truncate"><?php echo htmlspecialchars($page_subheader ?? 'Welcome to E-Vaccination System Portal'); ?></span>
        </div>
    </div>

    <!-- Right Side: User Profile & Actions -->
    <div class="d-flex align-items-center gap-2 gap-sm-3 flex-shrink-0">
        <!-- Date / Status Indicator (Hidden on mobile) -->
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill d-none d-lg-inline-flex align-items-center gap-2">
            <i class="bi bi-calendar3 text-teal"></i> <?php echo date('D, d M Y'); ?>
        </span>

        <!-- User Dropdown Menu -->
        <div class="dropdown">
            <button class="btn p-0 d-flex align-items-center gap-2 border-0 bg-transparent dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="text-start d-none d-md-block">
                    <div class="fw-semibold text-dark small mb-0 lh-1"><?php echo htmlspecialchars($user_name); ?></div>
                    <span class="badge bg-secondary-subtle text-secondary small text-capitalize" style="font-size: 0.7rem;">
                        <?php echo htmlspecialchars($user_role); ?>
                    </span>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2" style="min-width: 200px;">
                <li class="px-3 py-2 border-bottom">
                    <p class="mb-0 fw-bold small text-dark"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="mb-0 text-muted small text-truncate"><?php echo htmlspecialchars($user_email); ?></p>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?php echo $profile_link; ?>">
                        <i class="bi bi-person me-2 text-teal"></i> My Account
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?php echo base_url('index.php'); ?>" target="_blank">
                        <i class="bi bi-globe me-2 text-primary"></i> Public Site
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" href="<?php echo base_url('logout.php'); ?>">
                        <i class="bi bi-box-arrow-right me-2"></i> Log Out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
