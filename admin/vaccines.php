<?php
/**
 * Admin Vaccine Management (Full CRUD)
 * E-Vaccination Management System
 */
$page_title = 'Vaccine Management';
$page_header = 'Master Vaccine Catalog & Inventory';
$page_subheader = 'Add, edit, delete, and configure availability of standard childhood vaccines';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

// Handle Add / Edit / Delete POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'add') {
        $vaccine_name = sanitize($_POST['vaccine_name'] ?? '');
        $short_code = sanitize($_POST['short_code'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $target_disease = sanitize($_POST['target_disease'] ?? '');
        $recommended_age = sanitize($_POST['recommended_age'] ?? '');
        $doses_required = intval($_POST['doses_required'] ?? 1);
        $interval_days = intval($_POST['interval_days'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'Available');

        if (empty($vaccine_name) || empty($short_code) || empty($target_disease) || empty($recommended_age)) {
            set_flash('danger', 'Please fill in all required vaccine fields.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO vaccines (vaccine_name, short_code, description, target_disease, recommended_age, doses_required, interval_days, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$vaccine_name, $short_code, $description, $target_disease, $recommended_age, $doses_required, $interval_days, $status])) {
                $vac_id = $pdo->lastInsertId();
                // Associate with all existing hospitals
                $h_stmt = $pdo->query("SELECT id FROM hospitals");
                $all_h = $h_stmt->fetchAll();
                $hv_ins = $pdo->prepare("INSERT IGNORE INTO hospital_vaccines (hospital_id, vaccine_id, status) VALUES (?, ?, 'Available')");
                foreach ($all_h as $h) {
                    $hv_ins->execute([$h['id'], $vac_id]);
                }
                set_flash('success', 'New vaccine "' . htmlspecialchars($vaccine_name) . '" added to catalog.');
            } else {
                set_flash('danger', 'Failed to add vaccine.');
            }
        }
        header("Location: vaccines.php");
        exit();
    } elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $vaccine_name = sanitize($_POST['vaccine_name'] ?? '');
        $short_code = sanitize($_POST['short_code'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $target_disease = sanitize($_POST['target_disease'] ?? '');
        $recommended_age = sanitize($_POST['recommended_age'] ?? '');
        $doses_required = intval($_POST['doses_required'] ?? 1);
        $interval_days = intval($_POST['interval_days'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'Available');

        if ($id > 0 && !empty($vaccine_name)) {
            $stmt = $pdo->prepare("
                UPDATE vaccines 
                SET vaccine_name = ?, short_code = ?, description = ?, target_disease = ?, recommended_age = ?, doses_required = ?, interval_days = ?, status = ?
                WHERE id = ?
            ");
            if ($stmt->execute([$vaccine_name, $short_code, $description, $target_disease, $recommended_age, $doses_required, $interval_days, $status, $id])) {
                set_flash('success', 'Vaccine details updated successfully.');
            } else {
                set_flash('danger', 'Failed to update vaccine.');
            }
        }
        header("Location: vaccines.php");
        exit();
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM vaccines WHERE id = ?");
            if ($stmt->execute([$id])) {
                set_flash('success', 'Vaccine removed from catalog.');
            } else {
                set_flash('danger', 'Failed to delete vaccine.');
            }
        }
        header("Location: vaccines.php");
        exit();
    } elseif ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $curr = sanitize($_POST['current_status'] ?? 'Available');
        $new_status = ($curr === 'Available') ? 'Unavailable' : 'Available';

        $stmt = $pdo->prepare("UPDATE vaccines SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        set_flash('info', 'Vaccine status changed to ' . $new_status . '.');
        header("Location: vaccines.php");
        exit();
    }
}

// Fetch all vaccines
$stmt = $pdo->query("SELECT * FROM vaccines ORDER BY id ASC");
$vaccines = $stmt->fetchAll();

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
                    <h5 class="fw-bold font-outfit text-dark mb-0">Standard Vaccines Catalog (<?php echo count($vaccines); ?>)</h5>
                    <span class="text-muted small">Manage dosage guidelines, target diseases, and availability</span>
                </div>
                <div class="d-flex gap-2">
                    <div style="max-width: 250px;">
                        <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Filter vaccines...">
                    </div>
                    <button type="button" class="btn btn-emerald btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addVaccineModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add New Vaccine
                    </button>
                </div>
            </div>

            <!-- Vaccines Table Card -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Code & Name</th>
                                <th>Target Disease</th>
                                <th>Recommended Age</th>
                                <th>Doses & Interval</th>
                                <th>Availability</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vaccines)): ?>
                                <?php foreach ($vaccines as $vac): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-teal border fw-bold mb-1"><?php echo htmlspecialchars($vac['short_code']); ?></span>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($vac['vaccine_name']); ?></div>
                                            <div class="text-muted small" style="max-width: 280px;"><?php echo htmlspecialchars(substr($vac['description'], 0, 75)) . '...'; ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark small"><?php echo htmlspecialchars($vac['target_disease']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-emerald-subtle text-success small">
                                                <i class="bi bi-calendar3 me-1"></i> <?php echo htmlspecialchars($vac['recommended_age']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-dark"><?php echo $vac['doses_required']; ?> Shot(s)</div>
                                            <div class="text-muted small"><?php echo ($vac['interval_days'] > 0) ? $vac['interval_days'] . ' days gap' : 'Single/Birth'; ?></div>
                                        </td>
                                        <td>
                                            <form action="vaccines.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $vac['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $vac['status']; ?>">
                                                <button type="submit" class="btn p-0 border-0" title="Click to toggle status">
                                                    <?php echo status_badge($vac['status']); ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1.5">
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#editVaccineModal<?php echo $vac['id']; ?>" title="Edit Vaccine">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <!-- Delete Form -->
                                                <form action="vaccines.php" method="POST" onsubmit="return confirmDelete('Are you sure you want to delete this vaccine from catalog?');" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $vac['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete Vaccine">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Edit Vaccine Modal -->
                                            <div class="modal fade" id="editVaccineModal<?php echo $vac['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <form action="vaccines.php" method="POST">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?php echo $vac['id']; ?>">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"><i class="bi bi-pencil-square text-teal me-2"></i> Edit Vaccine Details</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row g-3">
                                                                    <div class="col-md-8">
                                                                        <label class="form-label fw-semibold small text-dark">Vaccine Full Name <span class="text-danger">*</span></label>
                                                                        <input type="text" name="vaccine_name" class="form-control" value="<?php echo htmlspecialchars($vac['vaccine_name']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label fw-semibold small text-dark">Short Code <span class="text-danger">*</span></label>
                                                                        <input type="text" name="short_code" class="form-control" value="<?php echo htmlspecialchars($vac['short_code']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Target Disease / Illness <span class="text-danger">*</span></label>
                                                                        <input type="text" name="target_disease" class="form-control" value="<?php echo htmlspecialchars($vac['target_disease']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Recommended Child Age <span class="text-danger">*</span></label>
                                                                        <input type="text" name="recommended_age" class="form-control" value="<?php echo htmlspecialchars($vac['recommended_age']); ?>" placeholder="e.g. 6 Weeks, 9 Months" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label fw-semibold small text-dark">Total Doses Required</label>
                                                                        <input type="number" name="doses_required" class="form-control" value="<?php echo $vac['doses_required']; ?>" min="1" max="10">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label fw-semibold small text-dark">Interval Between Doses (Days)</label>
                                                                        <input type="number" name="interval_days" class="form-control" value="<?php echo $vac['interval_days']; ?>" min="0">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label fw-semibold small text-dark">Availability Status</label>
                                                                        <select name="status" class="form-select">
                                                                            <option value="Available" <?php echo ($vac['status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                                                                            <option value="Unavailable" <?php echo ($vac['status'] === 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold small text-dark">Description & Medical Notes</label>
                                                                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($vac['description']); ?></textarea>
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
                                        No vaccines found in catalog. Click "Add New Vaccine" to create one.
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

<!-- Add Vaccine Modal -->
<div class="modal fade" id="addVaccineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="vaccines.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-teal me-2"></i> Add New Vaccine to Master Catalog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-dark">Vaccine Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="vaccine_name" class="form-control" placeholder="e.g. Inactivated Polio Vaccine (IPV)" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Short Code <span class="text-danger">*</span></label>
                            <input type="text" name="short_code" class="form-control" placeholder="e.g. IPV-1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Target Disease / Illness <span class="text-danger">*</span></label>
                            <input type="text" name="target_disease" class="form-control" placeholder="e.g. Poliomyelitis" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Recommended Child Age <span class="text-danger">*</span></label>
                            <input type="text" name="recommended_age" class="form-control" placeholder="e.g. At Birth, 6 Weeks, 9 Months" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Total Doses Required</label>
                            <input type="number" name="doses_required" class="form-control" value="1" min="1" max="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Interval Between Doses (Days)</label>
                            <input type="number" name="interval_days" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Availability Status</label>
                            <select name="status" class="form-select">
                                <option value="Available" selected>Available</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Description & Medical Details</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Clinical explanation, contraindications, and immunization benefits..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-3"><i class="bi bi-plus-lg me-1"></i> Add Vaccine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

