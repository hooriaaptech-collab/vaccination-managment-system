<?php
/**
 * Admin Child Details & Immunization Record Profile
 * E-Vaccination Management System
 */
$page_title = 'Child Profile';
$page_header = 'Child Immunization Profile';
$page_subheader = 'Comprehensive health records, parental details, and vaccination timeline';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$child_id = intval($_GET['id'] ?? 0);
if ($child_id <= 0) {
    set_flash('danger', 'Invalid child ID specified.');
    header("Location: children.php");
    exit();
}

// Fetch child and parent data
$stmt = $pdo->prepare("
    SELECT c.*, u.name AS parent_name, u.email AS parent_email, u.phone AS parent_phone
    FROM children c
    JOIN users u ON c.parent_id = u.id
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$child_id]);
$child = $stmt->fetch();

if (!$child) {
    set_flash('danger', 'Child profile not found.');
    header("Location: children.php");
    exit();
}

// Fetch administered vaccination records
$v_stmt = $pdo->prepare("
    SELECT vr.*, v.vaccine_name, v.short_code, v.target_disease, h.hospital_name, h.city AS hospital_city
    FROM vaccination_records vr
    JOIN vaccines v ON vr.vaccine_id = v.id
    JOIN hospitals h ON vr.hospital_id = h.id
    WHERE vr.child_id = ?
    ORDER BY vr.vaccination_date ASC
");
$v_stmt->execute([$child_id]);
$records = $v_stmt->fetchAll();

// Fetch appointments
$a_stmt = $pdo->prepare("
    SELECT a.*, v.vaccine_name, v.short_code, h.hospital_name
    FROM appointments a
    JOIN vaccines v ON a.vaccine_id = v.id
    JOIN hospitals h ON a.hospital_id = h.id
    WHERE a.child_id = ?
    ORDER BY a.appointment_date DESC
");
$a_stmt->execute([$child_id]);
$appointments = $a_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <div class="mb-3">
                <a href="children.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Children Registry
                </a>
            </div>

            <!-- Profile Top Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Child Vitals Card -->
                <div class="col-lg-7">
                    <div class="card-3d p-4 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon emerald" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                    <i class="bi bi-person-bounding-box"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold font-outfit text-dark mb-0"><?php echo htmlspecialchars($child['name']); ?></h4>
                                    <span class="text-muted small">ID: #CH-<?php echo str_pad($child['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill">
                                <?php echo htmlspecialchars($child['gender']); ?>
                            </span>
                        </div>

                        <div class="row g-3 py-2 border-top border-bottom small my-2">
                            <div class="col-sm-4">
                                <span class="text-muted d-block">Date of Birth:</span>
                                <strong class="text-dark"><?php echo format_date($child['dob']); ?></strong>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block">Current Age:</span>
                                <strong class="text-teal"><?php echo calculate_age($child['dob']); ?></strong>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block">Blood Group:</span>
                                <span class="badge bg-danger-subtle text-danger px-2"><?php echo htmlspecialchars($child['blood_group'] ?: 'N/A'); ?></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block">Birth Weight:</span>
                                <strong class="text-dark"><?php echo htmlspecialchars($child['birth_weight'] ?: 'N/A'); ?></strong>
                            </div>
                            <div class="col-sm-8">
                                <span class="text-muted d-block">Registered On:</span>
                                <span class="text-dark"><?php echo format_date($child['created_at']); ?></span>
                            </div>
                        </div>

                        <?php if (!empty($child['medical_notes'])): ?>
                            <div class="p-2.5 bg-light rounded-3 mt-3 small">
                                <strong class="text-dark"><i class="bi bi-file-medical text-teal me-1"></i> Medical Notes & Allergies:</strong>
                                <p class="text-muted mb-0 mt-1"><?php echo nl2br(htmlspecialchars($child['medical_notes'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Parent Information Card -->
                <div class="col-lg-5">
                    <div class="card-3d p-4 bg-white h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="stat-icon teal" style="width: 42px; height: 42px;">
                                <i class="bi bi-person-heart"></i>
                            </div>
                            <h5 class="fw-bold font-outfit text-dark mb-0">Parent / Guardian Info</h5>
                        </div>

                        <ul class="list-unstyled d-flex flex-column gap-2.5 small mb-0">
                            <li>
                                <span class="text-muted d-block">Full Name:</span>
                                <strong class="text-dark fs-6"><?php echo htmlspecialchars($child['parent_name']); ?></strong>
                            </li>
                            <li>
                                <span class="text-muted d-block">Phone Number:</span>
                                <span class="text-dark"><i class="bi bi-telephone-fill text-teal me-1"></i> <?php echo htmlspecialchars($child['parent_phone']); ?></span>
                            </li>
                            <li>
                                <span class="text-muted d-block">Email Address:</span>
                                <span class="text-dark"><i class="bi bi-envelope-fill text-teal me-1"></i> <?php echo htmlspecialchars($child['parent_email']); ?></span>
                            </li>
                            <?php if (!empty($child['address'])): ?>
                                <li>
                                    <span class="text-muted d-block">Residential Address:</span>
                                    <span class="text-dark"><i class="bi bi-geo-alt-fill text-teal me-1"></i> <?php echo htmlspecialchars($child['address']); ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Administered Vaccination Records -->
            <div class="table-card mb-4">
                <div class="table-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-award-fill text-success"></i>
                        <h6 class="fw-bold font-outfit text-dark mb-0">Administered Vaccination History (<?php echo count($records); ?> Doses)</h6>
                    </div>
                    <span class="badge bg-success-subtle text-success px-2.5 py-1">Official Immunization Log</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Vaccine & Dose</th>
                                <th>Target Disease</th>
                                <th>Hospital Center</th>
                                <th>Date Administered</th>
                                <th>Batch / Dr. Name</th>
                                <th>Verification Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($records)): ?>
                                <?php foreach ($records as $rec): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($rec['vaccine_name']); ?></div>
                                            <span class="badge bg-emerald-subtle text-success small"><?php echo htmlspecialchars($rec['short_code']); ?> (Dose #<?php echo $rec['dose_number']; ?>)</span>
                                        </td>
                                        <td><span class="small text-muted"><?php echo htmlspecialchars($rec['target_disease']); ?></span></td>
                                        <td><div class="small fw-medium text-dark"><?php echo htmlspecialchars($rec['hospital_name']); ?></div></td>
                                        <td><div class="small fw-bold text-dark"><?php echo format_date($rec['vaccination_date']); ?></div></td>
                                        <td>
                                            <div class="small text-dark"><?php echo htmlspecialchars($rec['batch_number'] ?: 'N/A'); ?></div>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($rec['administered_by'] ?: 'Pediatric Staff'); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-teal border px-2 py-1 font-monospace small">
                                                <?php echo htmlspecialchars($rec['certificate_code']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No administered vaccination shots recorded yet for this child.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Appointments Log -->
            <div class="table-card">
                <div class="table-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-week-fill text-teal"></i>
                        <h6 class="fw-bold font-outfit text-dark mb-0">Appointment Requests History</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Vaccine</th>
                                <th>Hospital</th>
                                <th>Date & Slot</th>
                                <th>Status</th>
                                <th>Admin Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($appointments)): ?>
                                <?php foreach ($appointments as $app): ?>
                                    <tr>
                                        <td><strong class="text-dark"><?php echo htmlspecialchars($app['booking_code']); ?></strong></td>
                                        <td><span class="fw-medium text-teal"><?php echo htmlspecialchars($app['vaccine_name']); ?></span></td>
                                        <td><span class="small text-dark"><?php echo htmlspecialchars($app['hospital_name']); ?></span></td>
                                        <td>
                                            <div class="small fw-semibold text-dark"><?php echo format_date($app['appointment_date']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($app['appointment_time']); ?></div>
                                        </td>
                                        <td><?php echo status_badge($app['status']); ?></td>
                                        <td><span class="small text-muted"><?php echo htmlspecialchars($app['admin_remarks'] ?: 'None'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No appointment requests recorded.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>



