<?php
/**
 * Parent Hospital Search & Selection
 * E-Vaccination Management System
 */
$page_title = 'Search Hospitals';
$page_header = 'Partner Hospital Directory';
$page_subheader = 'Discover nearby verified clinics and pediatric centers to schedule your child’s vaccination';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

// Fetch active hospitals
$stmt = $pdo->query("SELECT * FROM hospitals WHERE status = 'active' ORDER BY hospital_name ASC");
$hospitals = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold font-outfit text-dark mb-0">Authorized Immunization Centers (<?php echo count($hospitals); ?>)</h5>
                    <span class="text-muted small">All centers follow cold-chain safety and certified medical protocols</span>
                </div>
                <div style="max-width: 300px; width: 100%;">
                    <input type="text" id="hospitalSearchInput" class="form-control form-control-sm" placeholder="Search by name, city or location...">
                </div>
            </div>

            <div class="row g-4" id="hospitalCardsGrid">
                <?php if (!empty($hospitals)): ?>
                    <?php foreach ($hospitals as $hosp): ?>
                        <?php
                            $default_img = base_url('assets/images/hospital-default.jpg');
                            $hosp_img    = $default_img;
                            if (!empty($hosp['image'])) {
                                $img_rel = 'uploads/hospitals/' . $hosp['image'];
                                if (file_exists(__DIR__ . '/../' . $img_rel)) {
                                    $hosp_img = base_url($img_rel);
                                }
                            }
                        ?>
                        <div class="col-lg-4 col-md-6 hospital-item-card">
                            <div class="card-3d p-4 bg-white h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="hospital-thumb-wrap mb-3" style="position:relative; width:100%; height:160px; border-radius:14px; overflow:hidden; background:#eef2f5;">
                                        <img src="<?php echo $hosp_img; ?>"
                                             alt="<?php echo htmlspecialchars($hosp['hospital_name']); ?>"
                                             loading="lazy"
                                             style="width:100%; height:100%; object-fit:cover;"
                                             onerror="this.onerror=null; this.src='<?php echo $default_img; ?>';">
                                        <span class="badge bg-white text-success border border-success-subtle px-2.5 py-1 rounded-pill small" style="position:absolute; top:10px; right:10px;">
                                            Authorized Hub
                                        </span>
                                        <div class="stat-icon emerald" style="width: 44px; height: 44px; position:absolute; bottom:-16px; left:14px; border:3px solid #fff; border-radius:50%; box-shadow:0 4px 12px rgba(0,0,0,.12);">
                                            <i class="bi bi-hospital"></i>
                                        </div>
                                    </div>

                                    <h5 class="fw-bold font-outfit text-dark mb-2 mt-3"><?php echo htmlspecialchars($hosp['hospital_name']); ?></h5>
                                    
                                    <div class="d-flex flex-column gap-2 text-muted small mb-3">
                                        <div><i class="bi bi-geo-alt-fill text-teal me-1"></i> <?php echo htmlspecialchars($hosp['address']); ?>, <strong><?php echo htmlspecialchars($hosp['city']); ?></strong></div>
                                        <?php if (!empty($hosp['location'])): ?>
                                            <div><i class="bi bi-compass text-teal me-1"></i> <?php echo htmlspecialchars($hosp['location']); ?></div>
                                        <?php endif; ?>
                                        <div><i class="bi bi-clock-fill text-teal me-1"></i> <?php echo htmlspecialchars($hosp['operating_hours'] ?? '08:00 AM - 05:00 PM'); ?></div>
                                    </div>

                                    <div class="p-2.5 bg-light rounded-3 small text-muted mb-3">
                                        <div><i class="bi bi-telephone text-dark me-1"></i> <?php echo htmlspecialchars($hosp['phone']); ?></div>
                                        <div><i class="bi bi-envelope text-dark me-1"></i> <?php echo htmlspecialchars($hosp['email']); ?></div>
                                    </div>
                                </div>

                                <div class="pt-3 border-top">
                                    <a href="booking.php?hospital_id=<?php echo $hosp['id']; ?>" class="btn btn-emerald btn-sm w-100 py-2">
                                        <i class="bi bi-calendar-check me-1"></i> Book Appointment at this Hospital
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 border">
                        <i class="bi bi-hospital text-muted fs-2 d-block mb-2"></i>
                        No hospital facilities registered.
                    </div>
                <?php endif; ?>
            </div>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>



