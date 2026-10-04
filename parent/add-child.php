<?php

$page_title = 'Add Child';
$page_header = 'Register Child Profile';
$page_subheader = 'Enter your child’s birth details to generate an automated immunization timeline';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();
$error = '';

$name_val = '';
$dob_val = '';
$gender_val = 'Male';
$blood_val = 'Unknown';
$weight_val = '';
$address_val = '';
$notes_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $dob = sanitize($_POST['dob'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $blood_group = sanitize($_POST['blood_group'] ?? 'Unknown');
    $birth_weight = sanitize($_POST['birth_weight'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $medical_notes = sanitize($_POST['medical_notes'] ?? '');

    $name_val = $name;
    $dob_val = $dob;
    $gender_val = $gender;
    $blood_val = $blood_group;
    $weight_val = $birth_weight;
    $address_val = $address;
    $notes_val = $medical_notes;

    if (empty($name) || empty($dob)) {
        $error = 'Please provide child’s full name and date of birth.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO children (parent_id, name, dob, gender, blood_group, birth_weight, address, medical_notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$parent_id, $name, $dob, $gender, $blood_group, $birth_weight, $address, $medical_notes])) {
            $child_id = $pdo->lastInsertId();
            set_flash('success', 'Child profile for "' . htmlspecialchars($name) . '" registered successfully!');
            header("Location: child-profile.php?id=" . $child_id);
            exit();
        } else {
            $error = 'Failed to add child profile. Please try again.';
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
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold font-outfit text-dark mb-0">Add Child Information</h4>
                                <span class="text-muted small">Please fill in accurate date of birth for automated vaccine milestone scheduling</span>
                            </div>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show custom-alert shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="add-child.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small text-dark">Child Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Leo Jenkins" value="<?php echo htmlspecialchars($name_val); ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-dark">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" <?php echo ($gender_val === 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($gender_val === 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($gender_val === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($dob_val); ?>" required>
                                    <span class="text-muted" style="font-size: 0.75rem;">Used to calculate upcoming vaccine target dates.</span>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <?php foreach (['Unknown', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                                            <option value="<?php echo $bg; ?>" <?php echo ($blood_val === $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Birth Weight</label>
                                    <input type="text" name="birth_weight" class="form-control" placeholder="e.g. 3.2 kg" value="<?php echo htmlspecialchars($weight_val); ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Residential Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="e.g. 84 Maple Street, Apt 3B" value="<?php echo htmlspecialchars($address_val); ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Medical Notes / Known Allergies</label>
                                    <textarea name="medical_notes" class="form-control" rows="3" placeholder="Mention any pre-existing health conditions, premature birth details, or drug allergies..."><?php echo htmlspecialchars($notes_val); ?></textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-emerald w-100 py-2.5">
                                        <i class="bi bi-check2-circle me-1"></i> Save Child & Generate Roadmap
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>



