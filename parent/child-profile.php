<?php
/**
 * Parent Child Profile & Personalized Immunization Roadmap
 * E-Vaccination Management System
 */
$page_title = 'Child Roadmap';
$page_header = 'Child Immunization Roadmap';
$page_subheader = 'Personalized vaccination timeline calculated from birth date with completion tracking';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();
$child_id = intval($_GET['id'] ?? 0);

if ($child_id <= 0) {
    set_flash('danger', 'Invalid child ID.');
    header("Location: children.php");
    exit();
}

// Fetch child
$stmt = $pdo->prepare("SELECT * FROM children WHERE id = ? AND parent_id = ?");
$stmt->execute([$child_id, $parent_id]);
$child = $stmt->fetch();

if (!$child) {
    set_flash('danger', 'Child not found or access denied.');
    header("Location: children.php");
    exit();
}

// Fetch completed vaccination records for this child
$v_stmt = $pdo->prepare("
    SELECT vr.*, v.vaccine_name, v.short_code, h.hospital_name
    FROM vaccination_records vr
    JOIN vaccines v ON vr.vaccine_id = v.id
    JOIN hospitals h ON vr.hospital_id = h.id
    WHERE vr.child_id = ? AND vr.status = 'Vaccinated'
");
$v_stmt->execute([$child_id]);
$completed_records = $v_stmt->fetchAll();

// Index completed vaccine codes
$completed_vaccine_ids = [];
foreach ($completed_records as $cr) {
    $completed_vaccine_ids[$cr['vaccine_id']] = $cr;
}

// Fetch all standard vaccines
$all_vaccines = $pdo->query("SELECT * FROM vaccines ORDER BY id ASC")->fetchAll();

// Calculate routine milestones from DOB
$dob_time = strtotime($child['dob']);

$milestones = [
    ['label' => 'At Birth', 'days' => 0, 'codes' => ['BCG', 'HepB-0', 'OPV-0'], 'desc' => 'Tuberculosis, Hep B & Initial Polio protection.'],
    ['label' => '6 Weeks', 'days' => 42, 'codes' => ['PENTA-1', 'ROTA-1', 'PCV-1'], 'desc' => 'DTP, Hep B, Hib, Diarrhea & Pneumonia initial dose.'],
    ['label' => '10 Weeks', 'days' => 70, 'codes' => ['PENTA-1', 'ROTA-1', 'PCV-1'], 'desc' => 'Second series booster for combo vaccines.'],
    ['label' => '14 Weeks', 'days' => 98, 'codes' => ['PENTA-1', 'IPV-1'], 'desc' => 'Third primary series & injectable polio.'],
    ['label' => '9 Months', 'days' => 270, 'codes' => ['MR-1', 'TCV'], 'desc' => 'First Measles-Rubella & Typhoid conjugate dose.'],
    ['label' => '15 Months', 'days' => 450, 'codes' => ['MR-1'], 'desc' => 'Measles & Rubella second booster dose.'],
    ['label' => '16-24 Months', 'days' => 540, 'codes' => ['DTP-B1'], 'desc' => 'First childhood preschool booster.'],
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <div class="mb-3 d-flex align-items-center justify-content-between">
                <a href="children.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to My Children
                </a>
                <div class="d-flex gap-2">
                    <a href="edit-child.php?id=<?php echo $child['id']; ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit Info
                    </a>
                    <a href="booking.php?child_id=<?php echo $child['id']; ?>" class="btn btn-emerald btn-sm">
                        <i class="bi bi-calendar-plus me-1"></i> Book Vaccination
                    </a>
                </div>
            </div>

            <!-- Child Summary Header Card -->
            <div class="card-3d p-4 bg-white mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon emerald" style="width: 54px; height: 54px; font-size: 1.6rem;">
                                <i class="bi bi-person-heart"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold font-outfit text-dark mb-0"><?php echo htmlspecialchars($child['name']); ?></h3>
                                <div class="text-muted small">
                                    <?php echo htmlspecialchars($child['gender']); ?> &bull; Born: <strong><?php echo format_date($child['dob']); ?></strong> (Age: <strong><?php echo calculate_age($child['dob']); ?></strong>)
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6 rounded-pill">
                            <i class="bi bi-award-fill me-1"></i> <?php echo count($completed_records); ?> Doses Completed
                        </span>
                    </div>
                </div>
            </div>

            <!-- Personalized Immunization Milestone Roadmap -->
            <div class="table-card mb-4">
                <div class="table-card-header bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-range text-teal"></i>
                        <h5 class="fw-bold font-outfit text-dark mb-0">Personalized Immunization Roadmap</h5>
                    </div>
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill small">Calculated from Date of Birth</span>
                </div>

                <div class="p-4">
                    <div class="timeline-container">
                        <?php foreach ($all_vaccines as $vac): ?>
                            <?php 
                                $is_done = isset($completed_vaccine_ids[$vac['id']]);
                                $record_info = $is_done ? $completed_vaccine_ids[$vac['id']] : null;
                            ?>
                            <div class="p-3 mb-3 rounded-3 border <?php echo $is_done ? 'bg-light border-success-subtle' : 'bg-white'; ?>" style="border-left: 5px solid <?php echo $is_done ? '#10B981' : '#CBD5E1'; ?> !important;">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($vac['short_code']); ?></span>
                                            <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($vac['vaccine_name']); ?></h6>
                                        </div>
                                        <div class="text-muted small mb-1">
                                            <strong>Target Disease:</strong> <?php echo htmlspecialchars($vac['target_disease']); ?> &bull; 
                                            <strong>Recommended Timing:</strong> <?php echo htmlspecialchars($vac['recommended_age']); ?>
                                        </div>
                                        <?php if ($is_done): ?>
                                            <div class="small text-success fw-semibold">
                                                <i class="bi bi-check-circle-fill me-1"></i> Administered on <?php echo format_date($record_info['vaccination_date']); ?> at <?php echo htmlspecialchars($record_info['hospital_name']); ?> (Cert: <?php echo htmlspecialchars($record_info['certificate_code']); ?>)
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($vac['description']); ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-md-end flex-shrink-0">
                                        <?php if ($is_done): ?>
                                            <span class="badge bg-success text-white px-3 py-1.5 rounded-pill">
                                                <i class="bi bi-check-lg me-1"></i> Completed
                                            </span>
                                        <?php else: ?>
                                            <a href="booking.php?child_id=<?php echo $child['id']; ?>&vaccine_id=<?php echo $vac['id']; ?>" class="btn btn-emerald btn-sm px-3">
                                                <i class="bi bi-calendar-plus me-1"></i> Book Dose
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Official Certificates Section -->
            <?php if (!empty($completed_records)): ?>
                <div class="table-card">
                    <div class="table-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-check text-success"></i>
                            <h6 class="fw-bold font-outfit text-dark mb-0">Official Verified Certificates</h6>
                        </div>
                        <a href="vaccination-history.php" class="btn btn-outline-secondary btn-sm">Print Certificates</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>Vaccine</th>
                                    <th>Administered Date</th>
                                    <th>Administering Hospital</th>
                                    <th>Certificate Code</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completed_records as $rec): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($rec['vaccine_name']); ?></strong></td>
                                        <td><?php echo format_date($rec['vaccination_date']); ?></td>
                                        <td><?php echo htmlspecialchars($rec['hospital_name']); ?></td>
                                        <td><span class="badge bg-light text-dark border font-monospace"><?php echo htmlspecialchars($rec['certificate_code']); ?></span></td>
                                        <td>
                                            <a href="vaccination-history.php?cert_id=<?php echo $rec['id']; ?>" class="btn btn-outline-emerald btn-sm py-1 px-2">
                                                <i class="bi bi-printer me-1"></i> Certificate
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    </div>
</div>


