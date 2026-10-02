<?php
/**
 * Admin Parent Appointment Requests Management
 * E-Vaccination Management System
 */
$page_title = 'Parent Requests';
$page_header = 'Parent Appointment Requests';
$page_subheader = 'Review, approve, or reject vaccination appointment requests submitted by parents';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

// Process Status Actions (Approve / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $admin_remarks = sanitize($_POST['admin_remarks'] ?? '');

    if ($appointment_id > 0) {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'Approved', admin_remarks = ? WHERE id = ?");
            if ($stmt->execute([$admin_remarks ?: 'Approved by health administrator.', $appointment_id])) {
                set_flash('success', 'Appointment #' . $appointment_id . ' has been APPROVED successfully.');
            } else {
                set_flash('danger', 'Failed to approve appointment.');
            }
        } elseif ($action === 'reject') {
            if (empty($admin_remarks)) {
                set_flash('danger', 'Please provide a reason for rejecting the appointment request.');
            } else {
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'Rejected', admin_remarks = ? WHERE id = ?");
                if ($stmt->execute([$admin_remarks, $appointment_id])) {
                    set_flash('warning', 'Appointment #' . $appointment_id . ' has been REJECTED.');
                } else {
                    set_flash('danger', 'Failed to reject appointment.');
                }
            }
        }
        header("Location: requests.php");
        exit();
    }
}

// Filter by Status
$status_filter = sanitize($_GET['status'] ?? 'all');
$where_clause = "";
$params = [];

if ($status_filter !== 'all') {
    $where_clause = "WHERE a.status = ?";
    $params[] = $status_filter;
}

$query = "
    SELECT a.*, c.name AS child_name, c.dob AS child_dob, c.gender AS child_gender, 
           u.name AS parent_name, u.email AS parent_email, u.phone AS parent_phone,
           h.hospital_name, h.address AS hospital_address,
           v.vaccine_name, v.short_code
    FROM appointments a
    JOIN children c ON a.child_id = c.id
    JOIN users u ON a.parent_id = u.id
    JOIN hospitals h ON a.hospital_id = h.id
    JOIN vaccines v ON a.vaccine_id = v.id
    $where_clause
    ORDER BY CASE WHEN a.status = 'Pending' THEN 1 ELSE 2 END, a.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- Filter and Actions Header -->
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <!-- Status Filter Tabs -->
                <div class="btn-group shadow-sm bg-white p-1 rounded-3 border">
                    <a href="requests.php?status=all" class="btn btn-sm <?php echo ($status_filter === 'all') ? 'btn-emerald' : 'btn-light'; ?>">
                        All Requests (<?php echo count($requests); ?>)
                    </a>
                    <a href="requests.php?status=Pending" class="btn btn-sm <?php echo ($status_filter === 'Pending') ? 'btn-warning text-dark' : 'btn-light'; ?>">
                        Pending
                    </a>
                    <a href="requests.php?status=Approved" class="btn btn-sm <?php echo ($status_filter === 'Approved') ? 'btn-success' : 'btn-light'; ?>">
                        Approved
                    </a>
                    <a href="requests.php?status=Rejected" class="btn btn-sm <?php echo ($status_filter === 'Rejected') ? 'btn-danger' : 'btn-light'; ?>">
                        Rejected
                    </a>
                </div>

                <!-- Table Search Box -->
                <div style="max-width: 300px; width: 100%;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="tableSearchInput" class="form-control border-start-0" placeholder="Filter by parent, child, or hospital...">
                    </div>
                </div>
            </div>

            <!-- Requests Table Card -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Child Details</th>
                                <th>Parent Info</th>
                                <th>Vaccine Required</th>
                                <th>Hospital</th>
                                <th>Target Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($requests)): ?>
                                <?php foreach ($requests as $req): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold font-outfit text-dark"><?php echo htmlspecialchars($req['booking_code']); ?></span>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?php echo format_date($req['created_at']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($req['child_name']); ?></div>
                                            <span class="badge bg-light text-muted border small"><?php echo htmlspecialchars($req['child_gender']); ?>, <?php echo calculate_age($req['child_dob']); ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark"><?php echo htmlspecialchars($req['parent_name']); ?></div>
                                            <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($req['parent_phone']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-teal"><?php echo htmlspecialchars($req['short_code']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($req['vaccine_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark small"><?php echo htmlspecialchars($req['hospital_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark small"><?php echo format_date($req['appointment_date']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($req['appointment_time']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo status_badge($req['status']); ?>
                                            <?php if (!empty($req['admin_remarks'])): ?>
                                                <div class="text-muted small mt-1" style="font-size: 0.72rem;" title="<?php echo htmlspecialchars($req['admin_remarks']); ?>">
                                                    <i class="bi bi-chat-left-quote text-teal"></i> <?php echo htmlspecialchars(substr($req['admin_remarks'], 0, 30)) . '...'; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($req['status'] === 'Pending'): ?>
                                                <div class="d-flex gap-1.5">
                                                    <!-- Approve Button -->
                                                    <button type="button" class="btn btn-success btn-sm py-1 px-2.5" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $req['id']; ?>" title="Approve Request">
                                                        <i class="bi bi-check-lg"></i> Approve
                                                    </button>
                                                    <!-- Reject Button -->
                                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2.5" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $req['id']; ?>" title="Reject Request">
                                                        <i class="bi bi-x-lg"></i> Reject
                                                    </button>
                                                </div>

                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal<?php echo $req['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form action="requests.php" method="POST">
                                                                <input type="hidden" name="action" value="approve">
                                                                <input type="hidden" name="appointment_id" value="<?php echo $req['id']; ?>">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title text-success"><i class="bi bi-check-circle-fill me-2"></i> Approve Appointment Request</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="small text-muted mb-3">
                                                                        You are approving the vaccination appointment for <strong><?php echo htmlspecialchars($req['child_name']); ?></strong> (<?php echo htmlspecialchars($req['vaccine_name']); ?>) at <strong><?php echo htmlspecialchars($req['hospital_name']); ?></strong> on <strong><?php echo format_date($req['appointment_date']); ?></strong>.
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold small text-dark">Approval Remarks / Instructions for Parent (Optional)</label>
                                                                        <textarea name="admin_remarks" class="form-control" rows="2" placeholder="e.g. Approved. Please bring baby vaccination card and arrive 15 minutes early.">Approved. Please arrive on scheduled time.</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-success btn-sm px-3"><i class="bi bi-check-lg me-1"></i> Confirm Approval</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal<?php echo $req['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form action="requests.php" method="POST">
                                                                <input type="hidden" name="action" value="reject">
                                                                <input type="hidden" name="appointment_id" value="<?php echo $req['id']; ?>">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title text-danger"><i class="bi bi-x-circle-fill me-2"></i> Reject Appointment Request</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="small text-muted mb-3">
                                                                        Please provide a clear reason for rejecting the request for <strong><?php echo htmlspecialchars($req['child_name']); ?></strong>. The parent will be able to see this reason.
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold small text-dark">Rejection Reason <span class="text-danger">*</span></label>
                                                                        <textarea name="admin_remarks" class="form-control" rows="3" placeholder="e.g. Hospital clinic is closed on this date, or child has not reached minimum recommended age for this vaccine." required></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger btn-sm px-3"><i class="bi bi-x-lg me-1"></i> Confirm Rejection</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php else: ?>
                                                <span class="text-muted small"><i class="bi bi-lock me-1"></i> Processed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No appointment requests found matching the selected filter.
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


