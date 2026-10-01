<?php
/**
 * Parent Track Appointment Requests
 * E-Vaccination Management System
 */
$page_title = 'Track Requests';
$page_header = 'Appointment Requests Status';
$page_subheader = 'Monitor approval progress and instructions for your vaccination appointments';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();

// Handle Cancel Action by parent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $appointment_id = intval($_POST['appointment_id'] ?? 0);

    if ($action === 'cancel' && $appointment_id > 0) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND parent_id = ? AND status = 'Pending'");
        if ($stmt->execute([$appointment_id, $parent_id])) {
            set_flash('info', 'Appointment request cancelled.');
        }
        header("Location: requests.php");
        exit();
    }
}

// Fetch all appointments of this parent
$query = "
    SELECT a.*, c.name AS child_name, c.dob AS child_dob, 
           h.hospital_name, h.phone AS hospital_phone, h.address AS hospital_address, h.city AS hospital_city,
           v.vaccine_name, v.short_code
    FROM appointments a
    JOIN children c ON a.child_id = c.id
    JOIN hospitals h ON a.hospital_id = h.id
    JOIN vaccines v ON a.vaccine_id = v.id
    WHERE a.parent_id = ?
    ORDER BY a.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$parent_id]);
$requests = $stmt->fetchAll();

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
                    <h5 class="fw-bold font-outfit text-dark mb-0">My Appointment Requests (<?php echo count($requests); ?>)</h5>
                    <span class="text-muted small">Live status updates from health administration and partner centers</span>
                </div>
                <a href="booking.php" class="btn btn-emerald btn-sm px-3">
                    <i class="bi bi-calendar-plus-fill me-1"></i> New Request
                </a>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Child Name</th>
                                <th>Vaccine</th>
                                <th>Hospital</th>
                                <th>Requested Schedule</th>
                                <th>Status</th>
                                <th>Administrator Feedback / Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($requests)): ?>
                                <?php foreach ($requests as $req): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($req['booking_code']); ?></span>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?php echo format_date($req['created_at']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($req['child_name']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($req['short_code']); ?></span>
                                            <div class="text-muted small"><?php echo htmlspecialchars($req['vaccine_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-medium text-dark"><?php echo htmlspecialchars($req['hospital_name']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($req['hospital_address'] . ', ' . $req['hospital_city']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-dark"><?php echo format_date($req['appointment_date']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($req['appointment_time']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo status_badge($req['status']); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($req['admin_remarks'])): ?>
                                                <div class="small text-dark p-2 bg-light rounded-2 border">
                                                    <i class="bi bi-chat-quote-fill text-teal me-1"></i> <?php echo htmlspecialchars($req['admin_remarks']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">Pending admin review...</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($req['status'] === 'Pending'): ?>
                                                <form action="requests.php" method="POST" onsubmit="return confirmDelete('Are you sure you want to cancel this appointment request?');">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $req['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Cancel Request">
                                                        <i class="bi bi-x-circle"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php elseif ($req['status'] === 'Approved'): ?>
                                                <span class="badge bg-success-subtle text-success p-2 small">
                                                    <i class="bi bi-check-all me-1"></i> Visit on Date
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        You have not submitted any vaccination appointment requests yet.
                                        <div class="mt-2">
                                            <a href="booking.php" class="btn btn-emerald btn-sm">Schedule an Appointment</a>
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
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

