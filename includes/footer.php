<?php
/**
 * Global Footer Include
 * E-Vaccination Management System
 */
?>
<footer class="main-footer">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & Mission Column -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon">
                        <i class="bi bi-shield-plus"></i>
                    </div>
                    <span class="fs-4 fw-bold text-white font-outfit">E-Vaccination</span>
                </div>
                <p class="text-secondary small pe-lg-4">
                    A centralized digital immunization management platform designed to ensure no child misses life-saving vaccines. Connecting parents, pediatric healthcare centers, and health administrators seamlessly.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <span class="badge bg-emerald-subtle text-emerald border border-emerald-subtle p-2 px-3 rounded-pill text-white" style="background: rgba(20, 184, 166, 0.2);">
                        <i class="bi bi-heart-pulse-fill text-teal me-1"></i> WHO Standardized
                    </span>
                    <span class="badge bg-emerald-subtle text-emerald border border-emerald-subtle p-2 px-3 rounded-pill text-white" style="background: rgba(20, 184, 166, 0.2);">
                        <i class="bi bi-shield-check text-teal me-1"></i> Verified Centers
                    </span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5>Quick Links</h5>
                <a href="<?php echo base_url('index.php'); ?>" class="footer-link">Home</a>
                <a href="<?php echo base_url('about.php'); ?>" class="footer-link">About Us</a>
                <a href="<?php echo base_url('vaccines.php'); ?>" class="footer-link">Vaccines Catalog</a>
                <a href="<?php echo base_url('hospitals.php'); ?>" class="footer-link">Hospital Directory</a>
                <a href="<?php echo base_url('vaccination-schedule.php'); ?>" class="footer-link">Immunization Schedule</a>
                <a href="<?php echo base_url('how-it-works.php'); ?>" class="footer-link">How It Works</a>
            </div>

            <!-- Portals & Accounts -->
            <div class="col-lg-3 col-md-6">
                <h5>Access Portals</h5>
                <a href="<?php echo base_url('login.php'); ?>" class="footer-link">Parent Login</a>
                <a href="<?php echo base_url('login.php'); ?>" class="footer-link">Hospital Staff Login</a>
                <a href="<?php echo base_url('login.php'); ?>" class="footer-link">System Admin Login</a>
                <a href="<?php echo base_url('register.php'); ?>" class="footer-link">Parent Registration</a>
                <a href="<?php echo base_url('register.php'); ?>" class="footer-link">Hospital Registration</a>
            </div>

            <!-- Emergency & Support Contact -->
            <div class="col-lg-3 col-md-6">
                <h5>Help & Support</h5>
                <p class="small text-secondary mb-2">
                    <i class="bi bi-geo-alt-fill text-warning me-2"></i> National Immunization Directorate, Health Avenue
                </p>
                <p class="small text-secondary mb-2">
                    <i class="bi bi-telephone-fill text-warning me-2"></i> Helpline: +1 (800) 555-EVAC
                </p>
                <p class="small text-secondary mb-3">
                    <i class="bi bi-envelope-fill text-warning me-2"></i> support@evaccine-system.org
                </p>
                <a href="<?php echo base_url('contact.php'); ?>" class="btn btn-outline-emerald btn-sm w-100">
                    <i class="bi bi-chat-dots me-1"></i> Send Support Request
                </a>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="mb-0 text-secondary small">
                &copy; <?php echo date('Y'); ?> E-Vaccination Management System. All rights reserved. (College Academic Project)
            </p>
            <div class="d-flex gap-3 small text-secondary">
                <span>Safe Immunization</span>
                <span>•</span>
                <span>Child Health Priority</span>
                <span>•</span>
                <span>EPI Guidelines</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Main JS -->
<script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
<?php if (isset($is_dashboard) && $is_dashboard): ?>
<script src="<?php echo base_url('assets/js/dashboard.js'); ?>"></script>
<?php endif; ?>

</body>
</html>

