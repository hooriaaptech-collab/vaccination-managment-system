<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch all vaccines from database
try {
    $stmt = $pdo->query("SELECT * FROM vaccines ORDER BY id ASC");
    $vaccines = $stmt->fetchAll();
} catch (Exception $e) {
    $vaccines = [];
}

$page_title = 'Vaccines Catalog - E-Vaccination Management System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Page Header Banner -->
<div class="py-5 bg-white border-bottom">
    <div class="container text-center max-w-700 mx-auto" style="max-width: 720px;">
        <span class="section-tag">Immunization Catalog</span>
        <h1 class="fw-extrabold font-outfit text-dark mb-3">Childhood Vaccine Directory</h1>
        <p class="text-muted">
            Explore standard vaccines recommended under national immunization schedules. Learn about dosage intervals, target diseases, and optimal timing for your child.
        </p>

        <!-- Live Search Filter -->
        <div class="mt-4" style="max-width: 500px; margin: 0 auto;">
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-white border-end-0 text-teal"><i class="bi bi-search"></i></span>
                <input type="text" id="vaccineSearchInput" class="form-control border-start-0 ps-0" placeholder="Search vaccine by name, code or disease...">
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="fw-bold text-dark font-outfit mb-0">All Standard Vaccines (<?php echo count($vaccines); ?>)</h5>
        <span class="text-muted small">Updated in accordance with WHO & EPI Guidelines</span>
    </div>

    
    <div class="row g-4" id="vaccineCardsGrid">
        <?php if (!empty($vaccines)): ?>
            <?php foreach ($vaccines as $index => $vac): ?>
        
                <div class="col-lg-4 col-md-6 vaccine-item-card">
                    <div class="card-3d p-4 h-100 d-flex flex-column justify-content-between bg-white">
                        <div>
                            <!-- Vaccine Picture Banner (gradient + icon, works offline) -->
                            
<!-- Vaccine Image from Admin Panel -->
<div class="mb-3"
     style="height:180px; border-radius:14px; background:#fff; overflow:hidden; display:flex; align-items:center; justify-content:center;">

    <?php if (!empty($vac['image'])): ?>

        <img
            src="<?php echo htmlspecialchars(base_url($vac['image'])); ?>"
            alt="<?php echo htmlspecialchars($vac['vaccine_name']); ?>"
            style="width:100%; height:100%; object-fit:contain;"
        >

    <?php else: ?>

        <div class="text-muted small text-center p-3">
            Vaccine image not uploaded yet
        </div>

    <?php endif; ?>

</div>

                            <!-- Card Header -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-light text-teal border px-3 py-1.5 rounded-pill fw-bold font-outfit">
                                    <?php echo htmlspecialchars($vac['short_code']); ?>
                                </span>
                                <?php echo status_badge($vac['status']); ?>
                            </div>

                            <!-- Title -->
                            <h5 class="fw-bold font-outfit text-dark mb-2"><?php echo htmlspecialchars($vac['vaccine_name']); ?></h5>
                            
                            <!-- Key Badges -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-emerald-subtle text-success small">
                                    <i class="bi bi-calendar-event me-1"></i> <?php echo htmlspecialchars($vac['recommended_age']); ?>
                                </span>
                                <span class="badge bg-secondary-subtle text-dark small">
                                    <i class="bi bi-droplet-half me-1"></i> <?php echo htmlspecialchars($vac['doses_required']); ?> Dose(s)
                                </span>
                                <?php if ($vac['interval_days'] > 0): ?>
                                    <span class="badge bg-info-subtle text-info-emphasis small">
                                        <i class="bi bi-arrow-repeat me-1"></i> <?php echo htmlspecialchars($vac['interval_days']); ?> Days Interval
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Target Disease -->
                            <div class="p-2.5 bg-light rounded-3 mb-3 small">
                                <strong class="text-dark"><i class="bi bi-shield-plus text-teal me-1"></i> Prevents:</strong>
                                <span class="text-secondary"><?php echo htmlspecialchars($vac['target_disease']); ?></span>
                            </div>

                            <!-- Description -->
                            <p class="text-muted small mb-3">
                                <?php echo htmlspecialchars($vac['description']); ?>
                            </p>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="text-muted small">ID: #VAC-0<?php echo $vac['id']; ?></span>
                            <?php if (is_logged_in() && current_user_role() === 'parent'): ?>
                                <a href="<?php echo base_url('parent/booking.php?vaccine_id=' . $vac['id']); ?>" class="btn btn-emerald btn-sm">
                                    <i class="bi bi-calendar-plus me-1"></i> Book Dose
                                </a>
                            <?php else: ?>
                                <a href="<?php echo base_url('register.php'); ?>" class="btn btn-outline-emerald btn-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Get Vaccinated
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-capsule text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-dark fw-bold mt-2">No vaccines found</h5>
                <p class="text-muted">Vaccine inventory is currently being synchronized.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

