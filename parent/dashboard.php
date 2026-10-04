<?php
$page_title = 'Parent Dashboard';
$page_header = 'Parent Care Dashboard';
$page_subheader = 'Track immunization milestones, book hospital appointments, and manage child health records';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();

// Fetch parent's registered children
$c_stmt = $pdo->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM vaccination_records vr WHERE vr.child_id = c.id AND vr.status = 'Vaccinated') AS doses_count
    FROM children c
    WHERE c.parent_id = ?
    ORDER BY c.dob DESC
");
$c_stmt->execute([$parent_id]);
$children = $c_stmt->fetchAll();

$children_count = count($children);

// Fetch upcoming / pending appointments
$app_stmt = $pdo->prepare("
    SELECT a.*, c.name AS child_name, h.hospital_name, h.phone AS hospital_phone, h.address AS hospital_address,
           v.vaccine_name, v.short_code
    FROM appointments a
    JOIN children c ON a.child_id = c.id
    JOIN hospitals h ON a.hospital_id = h.id
    JOIN vaccines v ON a.vaccine_id = v.id
    WHERE a.parent_id = ? AND a.status IN ('Pending', 'Approved')
    ORDER BY a.appointment_date ASC
");
$app_stmt->execute([$parent_id]);
$upcoming_appointments = $app_stmt->fetchAll();

// Fetch total vaccinated count for this parent
$vac_stmt = $pdo->prepare("
    SELECT COUNT(vr.id)
    FROM vaccination_records vr
    JOIN children c ON vr.child_id = c.id
    WHERE c.parent_id = ? AND vr.status = 'Vaccinated'
");
$vac_stmt->execute([$parent_id]);
$total_shots_received = $vac_stmt->fetchColumn() ?: 0;

// Fetch recent vaccination records
$rec_stmt = $pdo->prepare("
    SELECT vr.*, c.name AS child_name, h.hospital_name, v.vaccine_name, v.short_code
    FROM vaccination_records vr
    JOIN children c ON vr.child_id = c.id
    JOIN hospitals h ON vr.hospital_id = h.id
    JOIN vaccines v ON vr.vaccine_id = v.id
    WHERE c.parent_id = ?
    ORDER BY vr.vaccination_date DESC
    LIMIT 4
");
$rec_stmt->execute([$parent_id]);
$recent_records = $rec_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- Welcome Banner Card -->
            <div class="card-3d p-4 mb-4 text-white" style="background: linear-gradient(135deg, var(--primary-emerald) 0%, var(--teal-main) 100%);">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <span class="badge bg-white text-teal fw-bold mb-2 px-3 py-1.5 rounded-pill">
                            <i class="bi bi-heart-fill text-danger me-1"></i> Family Health Portal
                        </span>
                        <h3 class="fw-extrabold font-outfit text-white mb-1">
                            Welcome, <?php echo htmlspecialchars(current_user_name()); ?>!
                        </h3>
                        <p class="text-light opacity-90 small mb-0">
                            You have <strong><?php echo $children_count; ?></strong> child(ren) registered under your care. All vaccination schedules are synced with standard guidelines.
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="add-child.php" class="btn btn-coral btn-sm px-3">
                            <i class="bi bi-person-plus-fill me-1"></i> Add New Child
                        </a>
                        <a href="booking.php" class="btn btn-light btn-sm text-dark px-3">
                            <i class="bi bi-calendar-plus-fill text-teal me-1"></i> Book Vaccine Slot
                        </a>
                    </div>
                </div>
            </div>

            <!-- Parent KPI Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Registered Children</div>
                                <h3 class="dash-card-value"><?php echo $children_count; ?></h3>
                                <a href="children.php" class="small text-teal fw-semibold text-decoration-none">Manage Children <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="dash-card-icon emerald">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Active Appointments</div>
                                <h3 class="dash-card-value text-warning"><?php echo count($upcoming_appointments); ?></h3>
                                <a href="requests.php" class="small text-teal fw-semibold text-decoration-none">Track Requests <i class="bi bi-arrow-right"></i></a>
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
                                <div class="dash-card-title">Completed Doses</div>
                                <h3 class="dash-card-value text-success"><?php echo $total_shots_received; ?></h3>
                                <a href="vaccination-history.php" class="small text-teal fw-semibold text-decoration-none">View Certificates <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="dash-card-icon purple">
                                <i class="bi bi-award-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Children Summary Cards & Upcoming Appointments Row -->
            <div class="row g-4 mb-4">
                <!-- Registered Children Cards -->
                <div class="col-lg-6">
                    <div class="table-card h-100">
                        <div class="table-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-hearts text-teal"></i>
                                <h6 class="fw-bold font-outfit text-dark mb-0">My Children Profiles</h6>
                            </div>
                            <a href="add-child.php" class="btn btn-outline-emerald btn-sm"><i class="bi bi-plus"></i> Add Child</a>
                        </div>
                        <div class="p-3">
                            <?php if (!empty($children)): ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($children as $c): ?>
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="stat-icon emerald" style="width: 44px; height: 44px; font-size: 1.3rem;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <a href="child-profile.php?id=<?php echo $c['id']; ?>" class="fw-bold text-dark text-decoration-none fs-6">
                                                        <?php echo htmlspecialchars($c['name']); ?>
                                                    </a>
                                                    <div class="text-muted small">
                                                        <?php echo htmlspecialchars($c['gender']); ?> &bull; Age: <strong><?php echo calculate_age($c['dob']); ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success-subtle text-success px-2.5 py-1 mb-1 d-inline-block">
                                                    <?php echo $c['doses_count']; ?> Shots Completed
                                                </span>
                                                <div>
                                                    <a href="booking.php?child_id=<?php echo $c['id']; ?>" class="btn btn-emerald btn-sm py-0.5 px-2" style="font-size: 0.78rem;">
                                                        <i class="bi bi-plus-circle me-1"></i> Book Shot
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-emoji-smile fs-3 d-block mb-2"></i>
                                    You haven't added any children yet.
                                    <div class="mt-2">
                                        <a href="add-child.php" class="btn btn-emerald btn-sm">Add Your Child Now</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Scheduled Appointments -->
                <div class="col-lg-6">
                    <div class="table-card h-100">
                        <div class="table-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event-fill text-warning"></i>
                                <h6 class="fw-bold font-outfit text-dark mb-0">Upcoming & Active Bookings</h6>
                            </div>
                            <a href="booking.php" class="btn btn-emerald btn-sm">Book New</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover custom-table">
                                <thead>
                                    <tr>
                                        <th>Child & Vaccine</th>
                                        <th>Hospital</th>
                                        <th>Date & Slot</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($upcoming_appointments)): ?>
                                        <?php foreach ($upcoming_appointments as $app): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['child_name']); ?></div>
                                                    <span class="badge bg-light text-teal border small"><?php echo htmlspecialchars($app['short_code']); ?></span>
                                                </td>
                                                <td>
                                                    <div class="small fw-medium text-dark"><?php echo htmlspecialchars($app['hospital_name']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="small fw-bold text-dark"><?php echo format_date($app['appointment_date']); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($app['appointment_time']); ?></div>
                                                </td>
                                                <td>
                                                    <?php echo status_badge($app['status']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No upcoming appointments scheduled.
                                                <div class="mt-2">
                                                    <a href="booking.php" class="btn btn-outline-emerald btn-sm">Book Appointment</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Vaccination History & Certificate Access -->
            <div class="table-card">
                <div class="table-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <h6 class="fw-bold font-outfit text-dark mb-0">Recent Vaccination Records</h6>
                    </div>
                    <a href="vaccination-history.php" class="btn btn-outline-secondary btn-sm">View Official Certificates</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>Vaccine Administered</th>
                                <th>Hospital Center</th>
                                <th>Date Administered</th>
                                <th>Certificate Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_records)): ?>
                                <?php foreach ($recent_records as $rec): ?>
                                    <tr>
                                        <td><div class="fw-bold text-dark"><?php echo htmlspecialchars($rec['child_name']); ?></div></td>
                                        <td>
                                            <span class="badge bg-emerald-subtle text-success"><?php echo htmlspecialchars($rec['short_code']); ?> (Dose #<?php echo $rec['dose_number']; ?>)</span>
                                            <div class="text-muted small"><?php echo htmlspecialchars($rec['vaccine_name']); ?></div>
                                        </td>
                                        <td><div class="small text-dark"><?php echo htmlspecialchars($rec['hospital_name']); ?></div></td>
                                        <td><div class="small fw-bold text-dark"><?php echo format_date($rec['vaccination_date']); ?></div></td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace small">
                                                <?php echo htmlspecialchars($rec['certificate_code']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No administered vaccination shots recorded yet.
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



