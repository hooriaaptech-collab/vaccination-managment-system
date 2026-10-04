<?php
//Main Home Page

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch dynamic counts for statistics
try {
    $total_vaccines = $pdo->query("SELECT COUNT(*) FROM vaccines WHERE status = 'Available'")->fetchColumn() ?: 10;
    $total_hospitals = $pdo->query("SELECT COUNT(*) FROM hospitals WHERE status = 'active'")->fetchColumn() ?: 3;
    $total_children = $pdo->query("SELECT COUNT(*) FROM children")->fetchColumn() ?: 4;
    $total_records = $pdo->query("SELECT COUNT(*) FROM vaccination_records WHERE status = 'Vaccinated'")->fetchColumn() ?: 4;
    
    // Fetch featured vaccines
    $v_stmt = $pdo->query("SELECT * FROM vaccines WHERE status = 'Available' LIMIT 6");
    $featured_vaccines = $v_stmt->fetchAll();

    // Fetch featured hospitals
    $h_stmt = $pdo->query("SELECT * FROM hospitals WHERE status = 'active' LIMIT 3");
    $featured_hospitals = $h_stmt->fetchAll();
} catch (Exception $e) {
    $total_vaccines = 10;
    $total_hospitals = 3;
    $total_children = 4;
    $total_records = 4;
    $featured_vaccines = [];
    $featured_hospitals = [];
}

$page_title = 'Home - Child Immunization & Hospital Booking';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>


    <!-- HERO SECTION-->
    
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" aria-hidden="true" style="width: 10px; height: 10px;"></span>
                    <span>National E-Immunization Health Portal</span>
                </div>
                <h1 class="hero-title">
                    Protect Your Child's Future with <span class="highlight">Smart E-Vaccination</span>
                </h1>
                <p class="hero-sub">
                    Eliminate missed doses and lost paper cards. Seamlessly register your newborn, discover certified pediatric hospitals, book immunization appointments, and access verified digital vaccination certificates 24/7.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php if (is_logged_in() && current_user_role() === 'parent'): ?>
                        <a href="<?php echo base_url('parent/booking.php'); ?>" class="btn btn-emerald btn-lg px-4 shadow-sm">
                            <i class="bi bi-calendar-plus-fill me-1"></i> Book Vaccination Slot
                        </a>
                        <a href="<?php echo base_url('parent/dashboard.php'); ?>" class="btn btn-outline-emerald btn-lg px-4">
                            <i class="bi bi-grid-fill me-1"></i> Parent Dashboard
                        </a>
                    <?php elseif (!is_logged_in()): ?>
                        <a href="<?php echo base_url('register.php'); ?>" class="btn btn-emerald btn-lg px-4 shadow-sm">
                            <i class="bi bi-person-plus-fill me-1"></i> Register as Parent
                        </a>
                        <a href="<?php echo base_url('vaccination-schedule.php'); ?>" class="btn btn-outline-emerald btn-lg px-4">
                            <i class="bi bi-calendar-check me-1"></i> Routine Schedule
                        </a>
                    <?php else: ?>
                        <a href="<?php echo base_url(current_user_role() . '/dashboard.php'); ?>" class="btn btn-emerald btn-lg px-4 shadow-sm">
                            <i class="bi bi-speedometer2 me-1"></i> Access Portal Dashboard
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Trust Badges -->
                <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4 text-muted small pt-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success fs-6"></i> WHO & EPI Standardized
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-hospital-fill text-teal fs-6"></i> Verified Cold-Chain Clinics
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-primary fs-6"></i> Encrypted Health Records
                    </div>
                </div>
            </div>

            <!-- Hero Visual 3D Component -->
            <div class="col-lg-5">
                <div class="hero-visual-wrapper">
                    <!-- Floating Pill Top -->
                    <div class="floating-pill pill-top">
                        <div class="stat-icon emerald" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark font-outfit">Verified Protection</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Polio & BCG Complete</div>
                        </div>
                    </div>

                    <!-- Center 3D Card -->
                    <div class="hero-center-card">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="brand-icon">
                                    <i class="bi bi-heart-pulse-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold font-outfit text-dark">Baby Health Passport</h6>
                                    <span class="text-teal small fw-semibold" style="font-size: 0.78rem;">Verified E-Vaccine Pass</span>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small fw-bold">
                                <i class="bi bi-dot"></i> Active
                            </span>
                        </div>

                        <!-- Mini Schedule Item List -->
                        <div class="d-flex flex-column gap-2.5 mb-4">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-3 border shadow-sm">
                                <div class="d-flex align-items-center gap-2.5">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-dark small">BCG + HepB-0 + OPV-0</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Administered at Birth (Within 24h)</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 0.7rem;">Verified</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-3 border shadow-sm">
                                <div class="d-flex align-items-center gap-2.5">
                                    <i class="bi bi-clock-history text-warning fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-dark small">Pentavalent-1 + Rota-1</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Scheduled Target: 6 Weeks</div>
                                    </div>
                                </div>
                                <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 rounded-pill" style="font-size: 0.7rem;">Upcoming</span>
                            </div>
                        </div>

                        <div class="p-3 bg-emerald-subtle rounded-3 border border-emerald-subtle text-center" style="background: linear-gradient(135deg, #ECFDF5 0%, #E0F2FE 100%);">
                            <div class="small fw-bold text-emerald">
                                <i class="bi bi-bell-fill text-teal me-1"></i> Auto-Reminder: Scheduled Slot Confirmed
                            </div>
                        </div>
                    </div>

                    <!-- Floating Pill Bottom -->
                    <div class="floating-pill pill-bottom">
                        <div class="stat-icon coral" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark font-outfit">Hospital Confirmed</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Cold-Chain Ready</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 
     LIVE STATS COUNTERS (Glass Surface Bar)
     -->
<section class="py-4 bg-white border-top border-bottom">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-teal font-outfit mb-0"><?php echo $total_vaccines; ?>+</h2>
                    <p class="text-muted small mb-0 fw-semibold">Core Vaccines Tracked</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-teal font-outfit mb-0"><?php echo $total_hospitals; ?>+</h2>
                    <p class="text-muted small mb-0 fw-semibold">Certified Hospital Hubs</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-teal font-outfit mb-0"><?php echo $total_children; ?>+</h2>
                    <p class="text-muted small mb-0 fw-semibold">Registered Children</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-teal font-outfit mb-0"><?php echo $total_records; ?>+</h2>
                    <p class="text-muted small mb-0 fw-semibold">Doses Administered</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================================================================
     WHY E-VACCINATION MATTERS (PROBLEM VS SOLUTION)
     ==================================================================== -->
<section class="py-5 my-3">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5" style="max-width: 680px;">
            <span class="section-tag">System Advantage</span>
            <h2 class="section-title font-outfit">Transforming Manual Immunization</h2>
            <p class="text-muted">
                Manual paper cards get lost, vaccination campaigns are hard to track, and children inadvertently miss critical doses. Our E-Vaccination platform brings precision, safety, and transparency.
            </p>
        </div>

        <div class="row g-4">
            <!-- Problem Card -->
            <div class="col-md-6">
                <div class="card-3d p-4 p-md-5 h-100" style="border-left: 6px solid #EF4444 !important;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon" style="background: #FEE2E2; color: #DC2626;">
                            <i class="bi bi-x-octagon-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-danger font-outfit">The Manual Paper Process</h4>
                    </div>
                    <ul class="list-unstyled d-flex flex-column gap-3 text-muted small mb-0 mt-3">
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-dash-circle-fill text-danger mt-1"></i>
                            <span>Physical yellow cards are easily misplaced, soaked, or destroyed over years.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-dash-circle-fill text-danger mt-1"></i>
                            <span>Parents forget scheduled booster dates due to lack of automated timeline tracking.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-dash-circle-fill text-danger mt-1"></i>
                            <span>Overcrowded clinics and long wait times with uncertain cold-chain vaccine availability.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-dash-circle-fill text-danger mt-1"></i>
                            <span>Zero centralized health authority audit trail across regional medical centers.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Solution Card -->
            <div class="col-md-6">
                <div class="card-3d p-4 p-md-5 h-100" style="border-left: 6px solid var(--accent-mint) !important;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon emerald">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-emerald font-outfit">The E-Vaccination Solution</h4>
                    </div>
                    <ul class="list-unstyled d-flex flex-column gap-3 text-muted small mb-0 mt-3">
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <span>Permanent encrypted digital health records accessible 24/7 anywhere worldwide.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <span>Automated schedule timeline matching WHO & EPI recommended immunization guidelines.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <span>Hospital appointment booking with live vaccine stock confirmation before arrival.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <span>Official printable vaccination certificates with verification QR/serial codes.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================================================================
     HOW THE SYSTEM WORKS (3 SIMPLE STEPS)
     ==================================================================== -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5" style="max-width: 680px;">
            <span class="section-tag">Smooth Healthcare Flow</span>
            <h2 class="section-title font-outfit">How E-Vaccination Works</h2>
            <p class="text-muted">Three simple steps to ensure your child receives every routine vaccine on time.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-3d p-4 text-center h-100">
                    <div class="stat-icon emerald mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div class="badge bg-light text-teal border mb-2 px-3 py-1.5 rounded-pill fw-bold font-outfit">Step 01</div>
                    <h5 class="fw-bold text-dark font-outfit">Register Child Profile</h5>
                    <p class="text-muted small mb-0">
                        Create a free parent account, add your children's birth dates, and instantly receive their personalized immunization roadmap.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-3d p-4 text-center h-100">
                    <div class="stat-icon teal mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="badge bg-light text-teal border mb-2 px-3 py-1.5 rounded-pill fw-bold font-outfit">Step 02</div>
                    <h5 class="fw-bold text-dark font-outfit">Book Certified Hospital</h5>
                    <p class="text-muted small mb-0">
                        Select a nearby authorized pediatric center, choose the required vaccine dose, and submit your preferred appointment slot.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-3d p-4 text-center h-100">
                    <div class="stat-icon coral mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div class="badge bg-light text-teal border mb-2 px-3 py-1.5 rounded-pill fw-bold font-outfit">Step 03</div>
                    <h5 class="fw-bold text-dark font-outfit">Vaccinate & Get Certificate</h5>
                    <p class="text-muted small mb-0">
                        Visit the clinic on your date. Once administered, the hospital marks it verified and your official digital certificate is ready.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================================================================
     FEATURED VACCINES CATALOG
     ==================================================================== -->
<section class="py-5 my-3">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-4">
            <div>
                <span class="section-tag">Immunization Catalog</span>
                <h2 class="section-title font-outfit mb-0">Essential Childhood Vaccines</h2>
            </div>
            <a href="<?php echo base_url('vaccines.php'); ?>" class="btn btn-outline-emerald mt-3 mt-md-0">
                View Full Vaccine Directory <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_vaccines)): ?>
                <?php foreach ($featured_vaccines as $vac): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card-3d p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge bg-light text-teal border px-3 py-1.5 rounded-pill fw-bold font-outfit">
                                        <?php echo htmlspecialchars($vac['short_code']); ?>
                                    </span>
                                    <span class="badge bg-emerald-subtle text-success px-2.5 py-1 rounded-pill small fw-semibold">
                                        <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($vac['recommended_age']); ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold font-outfit text-dark mb-2"><?php echo htmlspecialchars($vac['vaccine_name']); ?></h5>
                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars(substr($vac['description'], 0, 110)) . '...'; ?>
                                </p>
                            </div>
                            <div class="pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                                <span><strong>Target:</strong> <?php echo htmlspecialchars($vac['target_disease']); ?></span>
                                <span class="badge bg-success-subtle text-success"><?php echo htmlspecialchars($vac['status']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ====================================================================
     PARTNER HOSPITALS & CTA
     ==================================================================== -->
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-tag">Certified Healthcare Network</span>
                <h2 class="section-title font-outfit">Authorized Immunization Hubs</h2>
                <p class="text-muted mb-4">
                    Our partner pediatric hospitals and community clinics maintain cold-chain storage integrity and qualified pediatric medical staff for safe vaccine administration.
                </p>

                <div class="d-flex flex-column gap-3">
                    <?php if (!empty($featured_hospitals)): ?>
                        <?php foreach ($featured_hospitals as $hosp): ?>
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon emerald" style="width: 46px; height: 46px;">
                                        <i class="bi bi-hospital"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold font-outfit text-dark mb-0"><?php echo htmlspecialchars($hosp['hospital_name']); ?></h6>
                                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($hosp['address'] . ', ' . $hosp['city']); ?></div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold">Active Hub</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <a href="<?php echo base_url('hospitals.php'); ?>" class="btn btn-emerald">
                        <i class="bi bi-search me-1"></i> Explore All Partner Centers
                    </a>
                </div>
            </div>

            <!-- Parent Call to Action Card -->
            <div class="col-lg-6">
                <div class="card-3d p-4 p-md-5 text-white" style="background: linear-gradient(135deg, var(--dark-obsidian) 0%, var(--primary-emerald) 60%, var(--primary-teal) 100%);">
                    <div class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill mb-3 font-outfit">
                        <i class="bi bi-heart-fill me-1"></i> Protect Your Little One
                    </div>
                    <h3 class="fw-extrabold font-outfit text-white mb-3">
                        Start Digital Child Health Tracking Today
                    </h3>
                    <p class="text-light opacity-90 small mb-4">
                        Join hundreds of parents who never miss a routine vaccine. Register your child in less than 2 minutes and get instant immunization reminders.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo base_url('register.php'); ?>" class="btn btn-coral btn-lg px-4">
                            <i class="bi bi-person-plus-fill me-1"></i> Create Free Account
                        </a>
                        <a href="<?php echo base_url('login.php'); ?>" class="btn btn-light text-dark btn-lg px-4">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Parent Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
