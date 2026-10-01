<?php
/**
 * Parent Children List Management
 * E-Vaccination Management System
 */
$page_title = 'My Children';
$page_header = 'My Children Profiles';
$page_subheader = 'Manage and track immunization records for each registered child';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();

// Handle Delete Child
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $child_id = intval($_POST['child_id'] ?? 0);

    if ($action === 'delete' && $child_id > 0) {
        // Verify ownership
        $chk = $pdo->prepare("SELECT id FROM children WHERE id = ? AND parent_id = ?");
        $chk->execute([$child_id, $parent_id]);
        if ($chk->fetch()) {
            $del = $pdo->prepare("DELETE FROM children WHERE id = ?");
            $del->execute([$child_id]);
            set_flash('success', 'Child profile removed successfully.');
        } else {
            set_flash('danger', 'Unauthorized operation.');
        }
        header("Location: children.php");
        exit();
    }
}

// Fetch children
$stmt = $pdo->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM vaccination_records vr WHERE vr.child_id = c.id AND vr.status = 'Vaccinated') AS vaccinated_count,
           (SELECT COUNT(*) FROM appointments a WHERE a.child_id = c.id AND a.status = 'Pending') AS pending_count
    FROM children c
    WHERE c.parent_id = ?
    ORDER BY c.dob DESC
");
$stmt->execute([$parent_id]);
$children = $stmt->fetchAll();

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
                    <h5 class="fw-bold font-outfit text-dark mb-0">Registered Children (<?php echo count($children); ?>)</h5>
                    <span class="text-muted small">View age, medical notes, and customized vaccination schedules</span>
                </div>
                <a href="add-child.php" class="btn btn-emerald btn-sm px-3">
                    <i class="bi bi-person-plus-fill me-1"></i> Add New Child
                </a>
            </div>

            <div class="row g-4">
                <?php if (!empty($children)): ?>
                    <?php foreach ($children as $c): ?>
                        <div class="col-lg-6">
                            <div class="card-3d p-4 bg-white h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="stat-icon emerald" style="width: 48px; height: 48px; font-size: 1.4rem;">
                                                <i class="bi bi-person-heart"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold font-outfit text-dark mb-0"><?php echo htmlspecialchars($c['name']); ?></h5>
                                                <span class="text-muted small"><?php echo htmlspecialchars($c['gender']); ?> &bull; Age: <strong><?php echo calculate_age($c['dob']); ?></strong></span>
                                            </div>
                                        </div>
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill">
                                            Blood: <?php echo htmlspecialchars($c['blood_group'] ?: 'N/A'); ?>
                                        </span>
                                    </div>

                                    <div class="row g-2 p-3 bg-light rounded-3 small text-muted mb-3">
                                        <div class="col-6"><strong>Date of Birth:</strong> <?php echo format_date($c['dob']); ?></div>
                                        <div class="col-6"><strong>Birth Weight:</strong> <?php echo htmlspecialchars($c['birth_weight'] ?: 'N/A'); ?></div>
                                        <?php if (!empty($c['medical_notes'])): ?>
                                            <div class="col-12 mt-1"><strong>Notes:</strong> <?php echo htmlspecialchars($c['medical_notes']); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill">
                                            <i class="bi bi-check2-circle me-1"></i> <?php echo $c['vaccinated_count']; ?> Doses Administered
                                        </span>
                                        <?php if ($c['pending_count'] > 0): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1.5 rounded-pill">
                                                <i class="bi bi-clock-history me-1"></i> <?php echo $c['pending_count']; ?> Request Pending
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                                    <div class="d-flex gap-1.5">
                                        <a href="child-profile.php?id=<?php echo $c['id']; ?>" class="btn btn-emerald btn-sm">
                                            <i class="bi bi-calendar3 me-1"></i> Vaccine Roadmap
                                        </a>
                                        <a href="booking.php?child_id=<?php echo $c['id']; ?>" class="btn btn-outline-emerald btn-sm">
                                            <i class="bi bi-plus-circle me-1"></i> Book Dose
                                        </a>
                                    </div>

                                    <div class="d-flex gap-1">
                                        <a href="edit-child.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-secondary btn-sm py-1 px-2" title="Edit Child">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="children.php" method="POST" onsubmit="return confirmDelete('Are you sure you want to remove this child profile?');" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="child_id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete Child">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 border">
                        <i class="bi bi-person-plus text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-dark fw-bold mt-2">No Children Registered Yet</h5>
                        <p class="text-muted small">Add your children to begin tracking their routine vaccination milestones.</p>
                        <a href="add-child.php" class="btn btn-emerald btn-sm px-4">
                            <i class="bi bi-plus-lg me-1"></i> Add Your First Child
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

