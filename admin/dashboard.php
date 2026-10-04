<?php

$page_title = 'Admin Dashboard';
$page_header = 'System Administration Overview';
$page_subheader = 'Monitor child registrations, appointment requests, hospital facilities, and vaccination coverage';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Enforce admin role
require_role('admin');

// Fetch statistics
$total_children = $pdo->query("SELECT COUNT(*) FROM children")->fetchColumn();
$total_parents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'parent'")->fetchColumn();
$total_hospitals = $pdo->query("SELECT COUNT(*) FROM hospitals WHERE status = 'active'")->fetchColumn();
$total_vaccines = $pdo->query("SELECT COUNT(*) FROM vaccines WHERE status = 'Available'")->fetchColumn();
$pending_requests = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$total_vaccinated = $pdo->query("SELECT COUNT(*) FROM vaccination_records WHERE status = 'Vaccinated'")->fetchColumn();

// Fetch recent pending appointment requests
$req_stmt = $pdo->query("
    SELECT a.*, c.name AS child_name, c.dob AS child_dob, u.name AS parent_name, u.phone AS parent_phone, 
           h.hospital_name, v.vaccine_name, v.short_code
    FROM appointments a
    JOIN children c ON a.child_id = c.id
    JOIN users u ON a.parent_id = u.id
    JOIN hospitals h ON a.hospital_id = h.id
    JOIN vaccines v ON a.vaccine_id = v.id
    WHERE a.status = 'Pending'
    ORDER BY a.created_at DESC
    LIMIT 5
");
$pending_list = $req_stmt->fetchAll();

// Fetch recent vaccination records
$rec_stmt = $pdo->query("
    SELECT vr.*, c.name AS child_name, h.hospital_name, v.vaccine_name, v.short_code
    FROM vaccination_records vr
    JOIN children c ON vr.child_id = c.id
    JOIN hospitals h ON vr.hospital_id = h.id
    JOIN vaccines v ON vr.vaccine_id = v.id
    ORDER BY vr.created_at DESC
    LIMIT 5
");
$recent_records = $rec_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="dashboard-main">
        <!-- Topbar -->
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- KPI Summary Cards Row (Subtle 3D Depth) -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Total Children</div>
                                <h3 class="dash-card-value"><?php echo $total_children; ?></h3>
                                <span class="text-muted small">Registered in system</span>
                            </div>
                            <div class="dash-card-icon emerald">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Pending Requests</div>
                                <h3 class="dash-card-value text-warning"><?php echo $pending_requests; ?></h3>
                                <span class="text-muted small">Awaiting review</span>
                            </div>
                            <div class="dash-card-icon coral">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Total Hospitals</div>
                                <h3 class="dash-card-value"><?php echo $total_hospitals; ?></h3>
                                <span class="text-muted small">Active medical hubs</span>
                            </div>
                            <div class="dash-card-icon teal">
                                <i class="bi bi-hospital"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="dash-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="dash-card-title">Doses Administered</div>
                                <h3 class="dash-card-value text-success"><?php echo $total_vaccinated; ?></h3>
                                <span class="text-muted small">Verified shots</span>
                            </div>
                            <div class="dash-card-icon purple">
                                <i class="bi bi-award-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Metrics Bar -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon emerald" style="width: 42px; height: 42px;">
                                <i class="bi bi-person-heart"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Registered Parents</div>
                                <h5 class="fw-bold text-dark mb-0"><?php echo $total_parents; ?> Accounts</h5>
                            </div>
                        </div>
                        <a href="users.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon teal" style="width: 42px; height: 42px;">
                                <i class="bi bi-capsule"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Vaccines in Catalog</div>
                                <h5 class="fw-bold text-dark mb-0"><?php echo $total_vaccines; ?> Available</h5>
                            </div>
                        </div>
                        <a href="vaccines.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon coral" style="width: 42px; height: 42px;">
                                <i class="bi bi-calendar2-check"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Total Bookings Lifetime</div>
                                <h5 class="fw-bold text-dark mb-0"><?php echo $total_bookings; ?> Bookings</h5>
                            </div>
                        </div>
                        <a href="bookings.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Recent Requests & Recent Activity Rows -->
            <div class="row g-4 mb-4">
                <!-- Pending Requests Table -->
                <div class="col-lg-7">
                    <div class="table-card h-100">
                        <div class="table-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-bell-fill text-warning"></i>
                                <h6 class="fw-bold font-outfit text-dark mb-0">Pending Appointment Requests</h6>
                            </div>
                            <a href="requests.php" class="btn btn-emerald btn-sm">Manage All (<?php echo $pending_requests; ?>)</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover custom-table">
                                <thead>
                                    <tr>
                                        <th>Child & Parent</th>
                                        <th>Vaccine</th>
                                        <th>Hospital</th>
                                        <th>Target Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pending_list)): ?>
                                        <?php foreach ($pending_list as $req): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($req['child_name']); ?></div>
                                                    <div class="text-muted small"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($req['parent_name']); ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($req['short_code']); ?></span>
                                                </td>
                                                <td>
                                                    <div class="small text-dark fw-medium"><?php echo htmlspecialchars($req['hospital_name']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold text-dark"><?php echo format_date($req['appointment_date']); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($req['appointment_time']); ?></div>
                                                </td>
                                                <td>
                                                    <a href="requests.php?action=view&id=<?php echo $req['id']; ?>" class="btn btn-outline-emerald btn-sm py-1 px-2">
                                                        Review
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-check2-all text-success fs-4 d-block mb-1"></i>
                                                No pending appointment requests. All caught up!
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Administered Records -->
                <div class="col-lg-5">
                    <div class="table-card h-100">
                        <div class="table-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <h6 class="fw-bold font-outfit text-dark mb-0">Latest Vaccinations</h6>
                            </div>
                            <a href="reports.php" class="btn btn-outline-secondary btn-sm">Full Report</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover custom-table">
                                <thead>
                                    <tr>
                                        <th>Child & Dose</th>
                                        <th>Hospital</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_records)): ?>
                                        <?php foreach ($recent_records as $rec): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($rec['child_name']); ?></div>
                                                    <span class="badge bg-emerald-subtle text-success small"><?php echo htmlspecialchars($rec['short_code']); ?> (Dose #<?php echo $rec['dose_number']; ?>)</span>
                                                </td>
                                                <td>
                                                    <div class="small text-dark"><?php echo htmlspecialchars($rec['hospital_name']); ?></div>
                                                    <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($rec['certificate_code']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="small text-dark fw-semibold"><?php echo format_date($rec['vaccination_date']); ?></div>
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

            <!-- Quick Action Shortcuts -->
            <div class="card-3d p-4 bg-white">
                <h6 class="fw-bold font-outfit text-dark mb-3"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Administrative Actions</h6>
                <div class="d-flex flex-wrap gap-2">
                    <a href="requests.php" class="btn btn-emerald btn-sm">
                        <i class="bi bi-bell me-1"></i> Process Pending Requests (<?php echo $pending_requests; ?>)
                    </a>
                    <a href="vaccines.php?action=add" class="btn btn-outline-emerald btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add New Vaccine
                    </a>
                    <a href="hospitals.php?action=add" class="btn btn-outline-emerald btn-sm">
                        <i class="bi bi-building-add me-1"></i> Register New Hospital
                    </a>
                    <a href="children.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-search me-1"></i> Lookup Child Records
                    </a>
                    <a href="reports.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer me-1"></i> Export Vaccination Reports
                    </a>
                </div>
            </div>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>



