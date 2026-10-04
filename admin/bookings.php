<?php

$page_title = 'Master Bookings';
$page_header = 'All System Bookings & Appointments';
$page_subheader = 'Comprehensive list of all parent bookings across hospital facilities';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

// Fetch hospitals for filter dropdown
$hospitals_list = $pdo->query("SELECT id, hospital_name FROM hospitals ORDER BY hospital_name ASC")->fetchAll();

// Filters
$status_filter = sanitize($_GET['status'] ?? 'all');
$hospital_filter = intval($_GET['hospital_id'] ?? 0);
$date_filter = sanitize($_GET['date'] ?? '');

$where_clauses = [];
$params = [];

if ($status_filter !== 'all') {
    $where_clauses[] = "a.status = ?";
    $params[] = $status_filter;
}
if ($hospital_filter > 0) {
    $where_clauses[] = "a.hospital_id = ?";
    $params[] = $hospital_filter;
}
if (!empty($date_filter)) {
    $where_clauses[] = "a.appointment_date = ?";
    $params[] = $date_filter;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$query = "
    SELECT a.*, c.name AS child_name, c.dob AS child_dob, 
           u.name AS parent_name, u.phone AS parent_phone,
           h.hospital_name, v.vaccine_name, v.short_code
    FROM appointments a
    JOIN children c ON a.child_id = c.id
    JOIN users u ON a.parent_id = u.id
    JOIN hospitals h ON a.hospital_id = h.id
    JOIN vaccines v ON a.vaccine_id = v.id
    $where_sql
    ORDER BY a.appointment_date DESC, a.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- Filter Controls Card -->
            <div class="card-3d p-3 bg-white mb-4">
                <form action="bookings.php" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-semibold">Filter by Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                            <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1 fw-semibold">Filter by Hospital</label>
                        <select name="hospital_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="0">All Hospitals</option>
                            <?php foreach ($hospitals_list as $h): ?>
                                <option value="<?php echo $h['id']; ?>" <?php echo ($hospital_filter == $h['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($h['hospital_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-semibold">Appointment Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_filter); ?>" onchange="this.form.submit()">
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-1">
                        <a href="bookings.php" class="btn btn-outline-secondary btn-sm w-100" style="margin-top: 22px;">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Bookings Master Table -->
            <div class="table-card">
                <div class="table-card-header">
                    <h6 class="fw-bold font-outfit text-dark mb-0">Total Bookings Found: <?php echo count($bookings); ?></h6>
                    <div style="max-width: 250px;">
                        <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Live search bookings...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Child & Parent</th>
                                <th>Vaccine</th>
                                <th>Hospital</th>
                                <th>Appointment Slot</th>
                                <th>Status</th>
                                <th>Admin Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bookings)): ?>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($b['booking_code']); ?></span>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?php echo format_date($b['created_at']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($b['child_name']); ?></div>
                                            <div class="text-muted small"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($b['parent_name']); ?> (<?php echo htmlspecialchars($b['parent_phone']); ?>)</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($b['short_code']); ?></span>
                                            <div class="text-muted small"><?php echo htmlspecialchars($b['vaccine_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-medium text-dark"><?php echo htmlspecialchars($b['hospital_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-dark"><?php echo format_date($b['appointment_date']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($b['appointment_time']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo status_badge($b['status']); ?>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><?php echo htmlspecialchars($b['admin_remarks'] ?: 'None'); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        No bookings match the selected filter criteria.
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


