<?php
/**
 * Public Hospital Directory & Search
 * E-Vaccination Management System
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch all active hospitals from database
try {
    $stmt = $pdo->query("SELECT * FROM hospitals WHERE status = 'active' ORDER BY hospital_name ASC");
    $hospitals = $stmt->fetchAll();
} catch (Exception $e) {
    $hospitals = [];
}

$page_title = 'Hospital Directory - E-Vaccination Management System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
    .hospital-thumb-wrap {
        position: relative;
        width: 100%;
        height: 170px;
        border-radius: 14px;
        overflow: hidden;
        background: #eef2f5;
        margin-bottom: 1rem;
    }
    .hospital-thumb-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .4s ease;
    }
    .card-3d:hover .hospital-thumb-wrap img {
        transform: scale(1.06);
    }
    .hospital-thumb-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
        backdrop-filter: blur(4px);
    }
    .hospital-thumb-icon {
        position: absolute;
        bottom: -18px;
        left: 14px;
        z-index: 2;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
</style>

<!-- Page Header Banner -->
<div class="py-5 bg-white border-bottom">
    <div class="container text-center max-w-700 mx-auto" style="max-width: 720px;">
        <span class="section-tag">Authorized Centers</span>
        <h1 class="fw-extrabold font-outfit text-dark mb-3">Partner Hospital & Clinic Network</h1>
        <p class="text-muted">
            Locate verified healthcare facilities offering professional child immunization services, cold-chain compliant vaccines, and trained pediatric staff.
        </p>

        <!-- Live Search Filter -->
        <div class="mt-4" style="max-width: 500px; margin: 0 auto;">
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-white border-end-0 text-teal"><i class="bi bi-search"></i></span>
                <input type="text" id="hospitalSearchInput" class="form-control border-start-0 ps-0" placeholder="Search by hospital name, city or location...">
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="fw-bold text-dark font-outfit mb-0">Registered Healthcare Facilities (<?php echo count($hospitals); ?>)</h5>
        <span class="text-muted small">All centers are verified by Health Authorities</span>
    </div>

    <div class="row g-4" id="hospitalCardsGrid">
        <?php if (!empty($hospitals)): ?>
            <?php foreach ($hospitals as $hosp): ?>
                <?php
                    // Image resolve: DB column -> uploaded file -> default placeholder
                    $default_img = base_url('assets/images/hospital-default.jpg');
                    $hosp_img    = $default_img;

                    if (!empty($hosp['image'])) {
                        $img_rel  = 'uploads/hospitals/' . $hosp['image'];
                        $img_abs  = __DIR__ . '/' . $img_rel;
                        if (file_exists($img_abs)) {
                            $hosp_img = base_url($img_rel);
                        }
                    }
                ?>
                <div class="col-lg-4 col-md-6 hospital-item-card">
                    <div class="card-3d p-4 h-100 d-flex flex-column justify-content-between bg-white">
                        <div>
                            <!-- Hospital Image Banner -->
                            <div class="hospital-thumb-wrap">
                                <img src="<?php echo $hosp_img; ?>"
                                     alt="<?php echo htmlspecialchars($hosp['hospital_name']); ?>"
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='<?php echo $default_img; ?>';">

                                <span class="badge bg-white text-success border border-success-subtle rounded-pill px-2.5 py-1 hospital-thumb-badge">
                                    <i class="bi bi-patch-check-fill me-1"></i> Authorized Center
                                </span>

                                <div class="stat-icon emerald hospital-thumb-icon" style="width: 46px; height: 46px;">
                                    <i class="bi bi-hospital"></i>
                                </div>
                            </div>

                            <!-- Hospital Title -->
                            <h5 class="fw-bold font-outfit text-dark mb-2 mt-4"><?php echo htmlspecialchars($hosp['hospital_name']); ?></h5>
                            
                            <!-- Address & Location -->
                            <div class="d-flex flex-column gap-2 mb-3 text-muted small">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-geo-alt-fill text-teal mt-0.5"></i>
                                    <span><?php echo htmlspecialchars($hosp['address']); ?>, <strong><?php echo htmlspecialchars($hosp['city']); ?></strong></span>
                                </div>
                                <?php if (!empty($hosp['location'])): ?>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-compass text-teal mt-0.5"></i>
                                        <span><?php echo htmlspecialchars($hosp['location']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-clock-fill text-teal mt-0.5"></i>
                                    <span><?php echo htmlspecialchars($hosp['operating_hours'] ?? '08:00 AM - 05:00 PM'); ?></span>
                                </div>
                            </div>

                            <!-- Contact info -->
                            <div class="p-3 bg-light rounded-3 small text-muted mb-3">
                                <div class="mb-1"><i class="bi bi-telephone-fill text-dark me-2"></i><strong>Phone:</strong> <?php echo htmlspecialchars($hosp['phone']); ?></div>
                                <div><i class="bi bi-envelope-fill text-dark me-2"></i><strong>Email:</strong> <?php echo htmlspecialchars($hosp['email']); ?></div>
                            </div>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> Cold-Chain Ready</span>
                            <?php if (is_logged_in() && current_user_role() === 'parent'): ?>
                                <a href="<?php echo base_url('parent/booking.php?hospital_id=' . $hosp['id']); ?>" class="btn btn-emerald btn-sm">
                                    <i class="bi bi-calendar-check me-1"></i> Book Slot
                                </a>
                            <?php else: ?>
                                <a href="<?php echo base_url('login.php'); ?>" class="btn btn-outline-emerald btn-sm">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login to Book
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-hospital text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-dark fw-bold mt-2">No healthcare centers registered yet</h5>
                <p class="text-muted">Registered centers will appear here as soon as approved.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Live search filter for hospital cards
(function () {
    var input = document.getElementById('hospitalSearchInput');
    if (!input) return;

    var cards = document.querySelectorAll('#hospitalCardsGrid .hospital-item-card');

    input.addEventListener('keyup', function () {
        var q = this.value.toLowerCase().trim();
        cards.forEach(function (card) {
            var text = card.textContent.toLowerCase();
            card.style.display = (q === '' || text.indexOf(q) !== -1) ? '' : 'none';
        });
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

