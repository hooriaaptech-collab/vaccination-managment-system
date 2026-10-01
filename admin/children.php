<?php
/**
 * Admin Child Management
 * E-Vaccination Management System
 */
$page_title = 'Child Management';
$page_header = 'Registered Children Registry';
$page_subheader = 'Search, manage, view vaccination progress, and update profiles of registered children';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

// Handle Edit / Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $dob = sanitize($_POST['dob'] ?? '');
        $gender = sanitize($_POST['gender'] ?? 'Male');
        $blood_group = sanitize($_POST['blood_group'] ?? '');
        $birth_weight = sanitize($_POST['birth_weight'] ?? '');
        $medical_notes = sanitize($_POST['medical_notes'] ?? '');

        if ($id > 0 && !empty($name) && !empty($dob)) {
            $stmt = $pdo->prepare("
                UPDATE children 
                SET name = ?, dob = ?, gender = ?, blood_group = ?, birth_weight = ?, medical_notes = ?
                WHERE id = ?
            ");
            if ($stmt->execute([$name, $dob, $gender, $blood_group, $birth_weight, $medical_notes, $id])) {
                set_flash('success', 'Child details updated successfully.');
            } else {
                set_flash('danger', 'Failed to update child profile.');
            }
        }
        header("Location: children.php");
        exit();
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM children WHERE id = ?");
            if ($stmt->execute([$id])) {
                set_flash('success', 'Child record removed.');
            } else {
                set_flash('danger', 'Failed to delete child record.');
            }
        }
        header("Location: children.php");
        exit();
    }
}

// Fetch all children with parent info and completed vaccination counts
$query = "
    SELECT c.*, u.name AS parent_name, u.email AS parent_email, u.phone AS parent_phone,
           (SELECT COUNT(*) FROM vaccination_records vr WHERE vr.child_id = c.id AND vr.status = 'Vaccinated') AS vaccinated_count,
           (SELECT COUNT(*) FROM appointments a WHERE a.child_id = c.id AND a.status = 'Pending') AS pending_count
    FROM children c
    JOIN users u ON c.parent_id = u.id
    ORDER BY c.created_at DESC
";
$stmt = $pdo->query($query);
$children = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- Action Bar -->
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold font-outfit text-dark mb-0">Total Registered Children (<?php echo count($children); ?>)</h5>
                    <span class="text-muted small">Comprehensive national child immunization database</span>
                </div>
                <div style="max-width: 320px; width: 100%;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="tableSearchInput" class="form-control border-start-0" placeholder="Search by child name, parent, or blood group...">
                    </div>
                </div>
            </div>

            <!-- Children Table Card -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Child Name & Age</th>
                                <th>Gender & DOB</th>
                                <th>Parent / Guardian</th>
                                <th>Vitals</th>
                                <th>Doses Given</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($children)): ?>
                                <?php foreach ($children as $child): ?>
                                    <tr>
                                        <td>
                                            <a href="child-details.php?id=<?php echo $child['id']; ?>" class="fw-bold text-teal text-decoration-none">
                                                <?php echo htmlspecialchars($child['name']); ?> <i class="bi bi-arrow-up-right-square small"></i>
                                            </a>
                                            <div class="text-muted small">Age: <strong><?php echo calculate_age($child['dob']); ?></strong></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border small"><?php echo htmlspecialchars($child['gender']); ?></span>
                                            <div class="small text-muted mt-1"><i class="bi bi-calendar-event me-1"></i><?php echo format_date($child['dob']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark small"><?php echo htmlspecialchars($child['parent_name']); ?></div>
                                            <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($child['parent_phone']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-dark">Blood: <span class="badge bg-danger-subtle text-danger px-1.5"><?php echo htmlspecialchars($child['blood_group'] ?: 'N/A'); ?></span></div>
                                            <div class="small text-muted">Weight: <?php echo htmlspecialchars($child['birth_weight'] ?: 'N/A'); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success px-2.5 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i> <?php echo $child['vaccinated_count']; ?> Shots
                                            </span>
                                            <?php if ($child['pending_count'] > 0): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 small">
                                                    <?php echo $child['pending_count']; ?> Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1.5">
                                                <!-- View Details Button -->
                                                <a href="child-details.php?id=<?php echo $child['id']; ?>" class="btn btn-emerald btn-sm py-1 px-2" title="View Full Immunization Profile">
                                                    <i class="bi bi-file-medical"></i> Profile
                                                </a>

                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#editChildModal<?php echo $child['id']; ?>" title="Edit Child Details">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <!-- Delete Form -->
                                                <form action="children.php" method="POST" onsubmit="return confirmDelete('Are you sure you want to delete this child profile? This will delete all vaccination records associated.');" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $child['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete Child Record">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Edit Child Modal -->
                                            <div class="modal fade" id="editChildModal<?php echo $child['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form action="children.php" method="POST">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?php echo $child['id']; ?>">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"><i class="bi bi-pencil-square text-teal me-2"></i> Edit Child Information</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row g-3">
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold small text-dark">Child Full Name <span class="text-danger">*</span></label>
                                                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($child['name']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Date of Birth <span class="text-danger">*</span></label>
                                                                        <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($child['dob']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Gender <span class="text-danger">*</span></label>
                                                                        <select name="gender" class="form-select" required>
                                                                            <option value="Male" <?php echo ($child['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                                                            <option value="Female" <?php echo ($child['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                                                            <option value="Other" <?php echo ($child['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Blood Group</label>
                                                                        <select name="blood_group" class="form-select">
                                                                            <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'] as $bg): ?>
                                                                                <option value="<?php echo $bg; ?>" <?php echo ($child['blood_group'] === $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Birth Weight</label>
                                                                        <input type="text" name="birth_weight" class="form-control" value="<?php echo htmlspecialchars($child['birth_weight']); ?>" placeholder="e.g. 3.4 kg">
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold small text-dark">Medical Notes & Allergies</label>
                                                                        <textarea name="medical_notes" class="form-control" rows="3"><?php echo htmlspecialchars($child['medical_notes']); ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-emerald btn-sm px-3">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        No registered children found in database.
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

