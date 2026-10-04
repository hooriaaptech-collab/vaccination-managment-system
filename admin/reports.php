<?php

$page_title = 'Reports & Analytics';
$page_header = 'Immunization Reports & Analytics';
$page_subheader = 'Generate date-wise, child-wise, vaccine-wise, and hospital-wise vaccination audit reports';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$report_type = sanitize($_GET['type'] ?? 'date_wise');
$start_date = sanitize($_GET['start_date'] ?? date('Y-01-01'));
$end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));
$selected_vaccine = intval($_GET['vaccine_id'] ?? 0);
$selected_hospital = intval($_GET['hospital_id'] ?? 0);

// Fetch vaccines & hospitals for dropdowns
$all_vaccines = $pdo->query("SELECT id, vaccine_name, short_code FROM vaccines ORDER BY vaccine_name ASC")->fetchAll();
$all_hospitals = $pdo->query("SELECT id, hospital_name FROM hospitals ORDER BY hospital_name ASC")->fetchAll();

$report_data = [];

if ($report_type === 'date_wise') {
    $stmt = $pdo->prepare("
        SELECT vr.*, c.name AS child_name, c.dob AS child_dob, c.gender, u.name AS parent_name, u.phone AS parent_phone,
               v.vaccine_name, v.short_code, h.hospital_name, h.city
        FROM vaccination_records vr
        JOIN children c ON vr.child_id = c.id
        JOIN users u ON c.parent_id = u.id
        JOIN vaccines v ON vr.vaccine_id = v.id
        JOIN hospitals h ON vr.hospital_id = h.id
        WHERE vr.vaccination_date BETWEEN ? AND ?
        ORDER BY vr.vaccination_date DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $report_data = $stmt->fetchAll();
} elseif ($report_type === 'child_wise') {
    $stmt = $pdo->query("
        SELECT c.id, c.name AS child_name, c.dob, c.gender, c.blood_group,
               u.name AS parent_name, u.phone AS parent_phone,
               COUNT(vr.id) AS total_doses,
               MAX(vr.vaccination_date) AS last_vaccine_date
        FROM children c
        JOIN users u ON c.parent_id = u.id
        LEFT JOIN vaccination_records vr ON vr.child_id = c.id AND vr.status = 'Vaccinated'
        GROUP BY c.id
        ORDER BY total_doses DESC, c.name ASC
    ");
    $report_data = $stmt->fetchAll();
} elseif ($report_type === 'vaccine_wise') {
    $stmt = $pdo->query("
        SELECT v.id, v.vaccine_name, v.short_code, v.target_disease, v.recommended_age, v.status,
               COUNT(vr.id) AS doses_given,
               (SELECT COUNT(DISTINCT child_id) FROM vaccination_records WHERE vaccine_id = v.id AND status = 'Vaccinated') AS unique_children
        FROM vaccines v
        LEFT JOIN vaccination_records vr ON vr.vaccine_id = v.id AND vr.status = 'Vaccinated'
        GROUP BY v.id
        ORDER BY doses_given DESC
    ");
    $report_data = $stmt->fetchAll();
} elseif ($report_type === 'hospital_wise') {
    $stmt = $pdo->query("
        SELECT h.id, h.hospital_name, h.city, h.phone, h.status,
               COUNT(vr.id) AS vaccinations_administered,
               (SELECT COUNT(*) FROM appointments a WHERE a.hospital_id = h.id) AS total_bookings_received
        FROM hospitals h
        LEFT JOIN vaccination_records vr ON vr.hospital_id = h.id AND vr.status = 'Vaccinated'
        GROUP BY h.id
        ORDER BY vaccinations_administered DESC
    ");
    $report_data = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- Navigation Tabs & Print Bar -->
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 no-print">
                <ul class="nav nav-pills bg-white p-1 rounded-3 border shadow-sm" id="reportTabs">
                    <li class="nav-item">
                        <a href="reports.php?type=date_wise" class="nav-link rounded-3 fw-bold small <?php echo ($report_type === 'date_wise') ? 'active bg-emerald text-white' : 'text-dark'; ?>">
                            <i class="bi bi-calendar3 me-1"></i> Date-Wise Audit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.php?type=child_wise" class="nav-link rounded-3 fw-bold small <?php echo ($report_type === 'child_wise') ? 'active bg-emerald text-white' : 'text-dark'; ?>">
                            <i class="bi bi-people-fill me-1"></i> Child-Wise Report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.php?type=vaccine_wise" class="nav-link rounded-3 fw-bold small <?php echo ($report_type === 'vaccine_wise') ? 'active bg-emerald text-white' : 'text-dark'; ?>">
                            <i class="bi bi-capsule me-1"></i> Vaccine Coverage
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.php?type=hospital_wise" class="nav-link rounded-3 fw-bold small <?php echo ($report_type === 'hospital_wise') ? 'active bg-emerald text-white' : 'text-dark'; ?>">
                            <i class="bi bi-hospital me-1"></i> Hospital Hubs
                        </a>
                    </li>
                </ul>

                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i> Print / Export Report
                </button>
            </div>

            <!-- Date Range Filter Form (When Date-Wise selected) -->
            <?php if ($report_type === 'date_wise'): ?>
                <div class="card-3d p-3 bg-white mb-4 no-print">
                    <form action="reports.php" method="GET" class="row g-2 align-items-center">
                        <input type="hidden" name="type" value="date_wise">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1 fw-semibold">From Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1 fw-semibold">To Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-emerald btn-sm w-100" style="margin-top: 22px;">
                                <i class="bi bi-funnel-fill me-1"></i> Filter Date Range
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Printable Report Container -->
            <div class="table-card print-area">
                <div class="table-card-header bg-light">
                    <div>
                        <h5 class="fw-bold font-outfit text-dark mb-0">
                            <?php 
                                if ($report_type === 'date_wise') echo 'Date-Wise Immunization Log (' . format_date($start_date) . ' to ' . format_date($end_date) . ')';
                                elseif ($report_type === 'child_wise') echo 'Child Immunization Coverage Summary';
                                elseif ($report_type === 'vaccine_wise') echo 'Vaccine Uptake & Utilization Statistics';
                                elseif ($report_type === 'hospital_wise') echo 'Healthcare Facility Performance Report';
                            ?>
                        </h5>
                        <span class="text-muted small">Generated on: <?php echo date('d M Y, h:i A'); ?> | National E-Vaccination Registry</span>
                    </div>
                    <span class="badge bg-emerald text-white px-3 py-1.5 rounded-pill">
                        <?php echo count($report_data); ?> Records Found
                    </span>
                </div>

                <div class="table-responsive">
                    <?php if ($report_type === 'date_wise'): ?>
                        <table class="table table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>Administered Date</th>
                                    <th>Child Name & Age</th>
                                    <th>Parent Contact</th>
                                    <th>Vaccine & Dose</th>
                                    <th>Administered At</th>
                                    <th>Certificate Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report_data)): ?>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><strong class="text-dark"><?php echo format_date($row['vaccination_date']); ?></strong></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['child_name']); ?></div>
                                                <span class="small text-muted"><?php echo calculate_age($row['child_dob']); ?></span>
                                            </td>
                                            <td>
                                                <div class="small text-dark"><?php echo htmlspecialchars($row['parent_name']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($row['parent_phone']); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-emerald-subtle text-success"><?php echo htmlspecialchars($row['short_code']); ?> (Dose #<?php echo $row['dose_number']; ?>)</span>
                                                <div class="text-muted small"><?php echo htmlspecialchars($row['vaccine_name']); ?></div>
                                            </td>
                                            <td>
                                                <div class="small fw-medium text-dark"><?php echo htmlspecialchars($row['hospital_name']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($row['city']); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace small"><?php echo htmlspecialchars($row['certificate_code']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No vaccinations recorded in this date range.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    <?php elseif ($report_type === 'child_wise'): ?>
                        <table class="table table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>Child Name</th>
                                    <th>Date of Birth & Age</th>
                                    <th>Parent Info</th>
                                    <th>Blood Group</th>
                                    <th>Total Doses Completed</th>
                                    <th>Last Vaccination Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report_data)): ?>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><div class="fw-bold text-dark"><?php echo htmlspecialchars($row['child_name']); ?></div></td>
                                            <td>
                                                <div class="small text-dark"><?php echo format_date($row['dob']); ?></div>
                                                <span class="text-muted small"><?php echo calculate_age($row['dob']); ?></span>
                                            </td>
                                            <td>
                                                <div class="small text-dark"><?php echo htmlspecialchars($row['parent_name']); ?></div>
                                                <span class="text-muted small"><?php echo htmlspecialchars($row['parent_phone']); ?></span>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['blood_group'] ?: 'Unknown'); ?></span></td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success fw-bold px-3 py-1.5 fs-6">
                                                    <?php echo $row['total_doses']; ?> Shots
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small fw-medium text-dark"><?php echo format_date($row['last_vaccine_date']); ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No children records available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    <?php elseif ($report_type === 'vaccine_wise'): ?>
                        <table class="table table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>Vaccine Name & Code</th>
                                    <th>Target Disease</th>
                                    <th>Recommended Age</th>
                                    <th>Total Doses Administered</th>
                                    <th>Children Reached</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($row['short_code']); ?></span>
                                            <div class="fw-bold text-dark mt-1"><?php echo htmlspecialchars($row['vaccine_name']); ?></div>
                                        </td>
                                        <td><span class="small text-muted"><?php echo htmlspecialchars($row['target_disease']); ?></span></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['recommended_age']); ?></span></td>
                                        <td><strong class="text-teal fs-6"><?php echo $row['doses_given']; ?> Shots</strong></td>
                                        <td><span class="small fw-semibold text-dark"><?php echo $row['unique_children']; ?> Children</span></td>
                                        <td><?php echo status_badge($row['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php elseif ($report_type === 'hospital_wise'): ?>
                        <table class="table table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>Hospital Name</th>
                                    <th>City</th>
                                    <th>Contact Phone</th>
                                    <th>Total Bookings Received</th>
                                    <th>Vaccinations Administered</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><div class="fw-bold text-dark"><?php echo htmlspecialchars($row['hospital_name']); ?></div></td>
                                        <td><span class="small text-muted"><?php echo htmlspecialchars($row['city']); ?></span></td>
                                        <td><span class="small text-dark"><?php echo htmlspecialchars($row['phone']); ?></span></td>
                                        <td><strong class="text-dark"><?php echo $row['total_bookings_received']; ?> Bookings</strong></td>
                                        <td><span class="badge bg-success-subtle text-success fs-6 fw-bold"><?php echo $row['vaccinations_administered']; ?> Doses</span></td>
                                        <td><?php echo status_badge($row['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>



