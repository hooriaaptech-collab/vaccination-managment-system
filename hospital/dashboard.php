<?php
/**
 * Hospital Portal Dashboard
 * E-Vaccination Management System
 */
$page_title = 'Hospital Dashboard';
$page_header = 'Hospital Clinical Dashboard';
$page_subheader = 'Manage daily vaccination appointments, stock availability, and immunization records';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('hospital');

$user_id = current_user_id();

// Fetch hospital profile
$h_stmt = $pdo->prepare("SELECT * FROM hospitals WHERE user_id = ? LIMIT 1");
$h_stmt->execute([$user_id]);
$hospital = $h_stmt->fetch();

if (!$hospital) {
    die("Hospital profile not linked. Please contact admin.");
}

$hospital_id = $hospital['id'];

// Statistics
$total_bookings = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ?");
$total_bookings->execute([$hospital_id]);
$total_bookings_count = $total_bookings->fetchColumn() ?: 0;

$approved_apps = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND status = 'Approved'");
$approved_apps->execute([$hospital_id]);
$approved_count = $approved_apps->fetchColumn() ?: 0;

$completed_vacs = $pdo->prepare("SELECT COUNT(*) FROM vaccination_records WHERE hospital_id = ? AND status = 'Vaccinated'");
$completed_vacs->execute([$hospital_id]);
$completed_count = $completed_vacs->fetchColumn() ?: 0;

// Fetch upcoming scheduled appointments for today and future
$app_stmt = $pdo->prepare("
    SELECT a.*, c.name AS child_name, c.dob AS child_dob, c.gender AS child_gender, c.blood_group,
           u.name AS parent_name, u.phone AS parent_phone,
           v.vaccine_name, v.short_code
    FROM appointments a
    JOIN children c ON a.child_id = c.id
    JOIN users u ON a.parent_id = u.id
    JOIN vaccines v ON a.vaccine_id = v.id
    WHERE a.hospital_id = ? AND a.status = 'Approved'
    ORDER BY a.appointment_date ASC
    LIMIT 6
");
$app_stmt->execute([$hospital_id]);
$upcoming_list = $app_stmt->fetchAll();

// Fetch recent administered records
$rec_stmt = $pdo->prepare("
    SELECT vr.*, c.name AS child_name, v.vaccine_name, v.short_code
    FROM vaccination_records vr
    JOIN children c ON vr.child_id = c.id
    JOIN vaccines v ON vr.vaccine_id = v.id
    WHERE vr.hospital_id = ?
    ORDER BY vr.vaccination_date DESC
    LIMIT 5
");
$rec_stmt->execute([$hospital_id]);
$recent_administered = $rec_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- Hospital Facility Banner -->
            <div class="card-3d p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon emerald" style="width: 54px; height: 54px; font-size: 1.6rem;">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small mb-1">
                                <i class="bi bi-patch-check-fill me-1"></i> Authorized Immunization Center
                            </span>
                            <h3 class="fw-bold font-outfit text-dark mb-0"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h3>
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($hospital['address'] . ', ' . $hospital['city']); ?> &bull; 
                                <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($hospital['operating_hours'] ?? '08:00 AM - 05:00 PM'); ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="vaccines.php" class="btn btn-outline-emerald btn-sm px-3">
                            <i class="bi bi-capsule me-1"></i> Vaccine Stock Toggle
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hospital KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Scheduled Approved</div>
                                <h3 class="dash-card-value text-warning"><?php echo $approved_count; ?></h3>
                                <a href="appointments.php" class="small text-teal fw-semibold text-decoration-none">Manage Appointments <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="dash-card-icon coral">
                                <i class="bi bi-calendar2-check-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Administered Doses</div>
                                <h3 class="dash-card-value text-success"><?php echo $completed_count; ?></h3>
                                <a href="vaccination-records.php" class="small text-teal fw-semibold text-decoration-none">View Administered Log <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="dash-card-icon emerald">
                                <i class="bi bi-award-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Total Bookings Received</div>
                                <h3 class="dash-card-value"><?php echo $total_bookings_count; ?></h3>
                                <span class="text-muted small">All-time appointments</span>
                            </div>
                            <div class="dash-card-icon teal">
                                <i class="bi bi-journal-medical"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scheduled Patients & Recent Activity -->
            <div class="row g-4 mb-4">
                <!-- Upcoming Appointments for today/this week -->
                <div class="col-lg-7">
                    <div class="table-card h-100">
                        <div class="table-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-check text-warning"></i>
                                <h6 class="fw-bold font-outfit text-dark mb-0">Upcoming Approved Patients (<?php echo count($upcoming_list); ?>)</h6>
                            </div>
                            <a href="appointments.php" class="btn btn-emerald btn-sm">All Appointments</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover custom-table">
                                <thead>
                                    <tr>
                                        <th>Child & Parent</th>
                                        <th>Vaccine</th>
                                        <th>Date & Slot</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($upcoming_list)): ?>
                                        <?php foreach ($upcoming_list as $app): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['child_name']); ?></div>
                                                    <div class="text-muted small">Parent: <?php echo htmlspecialchars($app['parent_name']); ?> (<?php echo htmlspecialchars($app['parent_phone']); ?>)</div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($app['short_code']); ?></span>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($app['vaccine_name']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="small fw-bold text-dark"><?php echo format_date($app['appointment_date']); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($app['appointment_time']); ?></div>
                                                </td>
                                                <td>
                                                    <a href="appointments.php" class="btn btn-success btn-sm py-1 px-2.5">
                                                        <i class="bi bi-check2-circle me-1"></i> Update Status
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No upcoming approved appointments pending for this hospital.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Vaccinations Administered -->
                <div class="col-lg-5">
                    <div class="table-card h-100">
                        <div class="table-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <h6 class="fw-bold font-outfit text-dark mb-0">Recent Administered Shots</h6>
                            </div>
                            <a href="vaccination-records.php" class="btn btn-outline-secondary btn-sm">Full Log</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover custom-table">
                                <thead>
                                    <tr>
                                        <th>Child & Dose</th>
                                        <th>Date</th>
                                        <th>Certificate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_administered)): ?>
                                        <?php foreach ($recent_administered as $rec): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($rec['child_name']); ?></div>
                                                    <span class="badge bg-emerald-subtle text-success small"><?php echo htmlspecialchars($rec['short_code']); ?> (Dose #<?php echo $rec['dose_number']; ?>)</span>
                                                </td>
                                                <td><div class="small fw-bold text-dark"><?php echo format_date($rec['vaccination_date']); ?></div></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border font-monospace small"><?php echo htmlspecialchars($rec['certificate_code']); ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                No vaccination records recorded yet.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

