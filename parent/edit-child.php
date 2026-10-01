<?php
/**
 * Parent Edit Child Information
 * E-Vaccination Management System
 */
$page_title = 'Edit Child';
$page_header = 'Update Child Information';
$page_subheader = 'Modify registered child records and vitals';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();
$child_id = intval($_GET['id'] ?? 0);

if ($child_id <= 0) {
    set_flash('danger', 'Invalid child ID.');
    header("Location: children.php");
    exit();
}

// Fetch child ensuring ownership
$stmt = $pdo->prepare("SELECT * FROM children WHERE id = ? AND parent_id = ?");
$stmt->execute([$child_id, $parent_id]);
$child = $stmt->fetch();

if (!$child) {
    set_flash('danger', 'Child profile not found or unauthorized.');
    header("Location: children.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $dob = sanitize($_POST['dob'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $blood_group = sanitize($_POST['blood_group'] ?? 'Unknown');
    $birth_weight = sanitize($_POST['birth_weight'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $medical_notes = sanitize($_POST['medical_notes'] ?? '');

    if (empty($name) || empty($dob)) {
        $error = 'Child name and date of birth are required.';
    } else {
        $upd = $pdo->prepare("
            UPDATE children 
            SET name = ?, dob = ?, gender = ?, blood_group = ?, birth_weight = ?, address = ?, medical_notes = ?
            WHERE id = ? AND parent_id = ?
        ");
        if ($upd->execute([$name, $dob, $gender, $blood_group, $birth_weight, $address, $medical_notes, $child_id, $parent_id])) {
            set_flash('success', 'Child information updated successfully.');
            header("Location: child-profile.php?id=" . $child_id);
            exit();
        } else {
            $error = 'Failed to update child information.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <div class="mb-3">
                <a href="children.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to My Children
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card-3d p-4 p-md-5 bg-white">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="stat-icon emerald" style="width: 48px; height: 48px; font-size: 1.4rem;">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold font-outfit text-dark mb-0">Edit Child Details</h4>
                                <span class="text-muted small">Update medical notes and vital information for <?php echo htmlspecialchars($child['name']); ?></span>
                            </div>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show custom-alert shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="edit-child.php?id=<?php echo $child_id; ?>" method="POST">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small text-dark">Child Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($child['name']); ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-dark">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" <?php echo ($child['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($child['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($child['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($child['dob']); ?>" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <?php foreach (['Unknown', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                                            <option value="<?php echo $bg; ?>" <?php echo ($child['blood_group'] === $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Birth Weight</label>
                                    <input type="text" name="birth_weight" class="form-control" value="<?php echo htmlspecialchars($child['birth_weight']); ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Residential Address</label>
                                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($child['address']); ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Medical Notes / Known Allergies</label>
                                    <textarea name="medical_notes" class="form-control" rows="3"><?php echo htmlspecialchars($child['medical_notes']); ?></textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-emerald w-100 py-2.5">
                                        <i class="bi bi-check2-circle me-1"></i> Update Child Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

