<?php
/**
 * User Registration Page (Parent & Hospital)
 * E-Vaccination Management System
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect_by_role(current_user_role());
}

$error = '';
$duplicate_email = false;
$active_tab = 'parent';

// Form sticky values
$p_name = '';
$p_email = '';
$p_phone = '';
$h_name = '';
$h_email = '';
$h_phone = '';
$h_address = '';
$h_city = '';
$h_location = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize($_POST['role'] ?? 'parent');
    $active_tab = ($role === 'hospital') ? 'hospital' : 'parent';

    if ($role === 'parent') {
        $p_name = sanitize($_POST['p_name'] ?? '');
        $p_email = sanitize($_POST['p_email'] ?? '');
        $p_phone = sanitize($_POST['p_phone'] ?? '');
        $password = $_POST['p_password'] ?? '';
        $confirm_password = $_POST['p_confirm_password'] ?? '';

        if (empty($p_name) || empty($p_email) || empty($p_phone) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all required parent registration fields.';
        } elseif (!filter_var($p_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match. Please verify and try again.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            // Check for duplicate email
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check_stmt->execute([$p_email]);
            if ($check_stmt->fetch()) {
                $duplicate_email = true;
                $error = 'Account already exists with this email address. Please login.';
            } else {
                // Insert into users
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'parent', 'active')");
                if ($insert_stmt->execute([$p_name, $p_email, $p_phone, $hashed_password])) {
                    set_flash('success', 'Registration successful! Please login with your new parent account.');
                    header('Location: ' . base_url('login.php'));
                    exit();
                } else {
                    $error = 'Failed to create parent account. Please try again.';
                }
            }
        }
    } elseif ($role === 'hospital') {
        $h_name = sanitize($_POST['h_name'] ?? '');
        $h_email = sanitize($_POST['h_email'] ?? '');
        $h_phone = sanitize($_POST['h_phone'] ?? '');
        $h_address = sanitize($_POST['h_address'] ?? '');
        $h_city = sanitize($_POST['h_city'] ?? 'Metropolis');
        $h_location = sanitize($_POST['h_location'] ?? '');
        $password = $_POST['h_password'] ?? '';
        $confirm_password = $_POST['h_confirm_password'] ?? '';

        if (empty($h_name) || empty($h_email) || empty($h_phone) || empty($h_address) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all required hospital registration fields.';
        } elseif (!filter_var($h_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid hospital email address.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match. Please verify and try again.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            // Check for duplicate email
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check_stmt->execute([$h_email]);
            if ($check_stmt->fetch()) {
                $duplicate_email = true;
                $error = 'Account already exists with this email address. Please login.';
            } else {
                // Insert into users & hospitals
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $pdo->beginTransaction();
                try {
                    $insert_u = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'hospital', 'active')");
                    $insert_u->execute([$h_name, $h_email, $h_phone, $hashed_password]);
                    $user_id = $pdo->lastInsertId();

                    $insert_h = $pdo->prepare("INSERT INTO hospitals (user_id, hospital_name, email, phone, address, city, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                    $insert_h->execute([$user_id, $h_name, $h_email, $h_phone, $h_address, $h_city, $h_location]);
                    $hospital_id = $pdo->lastInsertId();

                    // Seed standard vaccines availability for this new hospital
                    $v_stmt = $pdo->query("SELECT id FROM vaccines");
                    $vaccines = $v_stmt->fetchAll();
                    $insert_hv = $pdo->prepare("INSERT INTO hospital_vaccines (hospital_id, vaccine_id, status) VALUES (?, ?, 'Available')");
                    foreach ($vaccines as $vac) {
                        $insert_hv->execute([$hospital_id, $vac['id']]);
                    }

                    $pdo->commit();

                    set_flash('success', 'Hospital registered successfully! You can now log in to your hospital portal.');
                    header('Location: ' . base_url('login.php'));
                    exit();
                } catch (Exception $ex) {
                    $pdo->rollBack();
                    $error = 'Hospital registration failed: ' . $ex->getMessage();
                }
            }
        }
    }
}

$page_title = 'Create Account - E-Vaccination System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-10">
            <div class="card-3d p-4 p-sm-5 bg-white">
                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="brand-icon mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.6rem;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1">Create an Account</h3>
                    <p class="text-muted small">Choose your account type below to get started</p>
                </div>

                <?php display_flash(); ?>

                <?php if (!empty($error)): ?>
                    <div class="alert <?php echo $duplicate_email ? 'alert-warning' : 'alert-danger'; ?> alert-dismissible fade show custom-alert shadow-sm" role="alert">
                        <i class="bi <?php echo $duplicate_email ? 'bi-info-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <?php if ($duplicate_email): ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-emerald btn-sm"><i class="bi bi-box-arrow-in-right me-1"></i> Proceed to Login</a>
                            </div>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Role Selection Tabs (Parent vs Hospital) -->
                <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3 border" id="registerTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 fw-bold <?php echo ($active_tab === 'parent') ? 'active bg-emerald text-white' : 'text-dark'; ?>" id="parent-tab" data-bs-toggle="tab" data-bs-target="#parentTabContent" type="button" role="tab">
                            <i class="bi bi-person-heart me-1.5"></i> Parent Registration
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 fw-bold <?php echo ($active_tab === 'hospital') ? 'active bg-emerald text-white' : 'text-dark'; ?>" id="hospital-tab" data-bs-toggle="tab" data-bs-target="#hospitalTabContent" type="button" role="tab">
                            <i class="bi bi-hospital me-1.5"></i> Hospital / Clinic Registration
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="registerTabsContent">
                    <!-- PARENT REGISTRATION TAB -->
                    <div class="tab-pane fade <?php echo ($active_tab === 'parent') ? 'show active' : ''; ?>" id="parentTabContent" role="tabpanel">
                        <form action="register.php" method="POST">
                            <input type="hidden" name="role" value="parent">

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                        <input type="text" name="p_name" class="form-control border-start-0" placeholder="e.g. Sarah Jenkins" value="<?php echo htmlspecialchars($p_name); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="p_email" class="form-control border-start-0" placeholder="sarah@example.com" value="<?php echo htmlspecialchars($p_email); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="p_phone" class="form-control border-start-0" placeholder="+1 (555) 000-0000" value="<?php echo htmlspecialchars($p_phone); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                        <input type="password" name="p_password" class="form-control border-start-0" placeholder="Min. 6 characters" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" name="p_confirm_password" class="form-control border-start-0" placeholder="Repeat password" required>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-emerald w-100 py-2.5">
                                        <i class="bi bi-check2-circle me-1"></i> Register as Parent
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- HOSPITAL REGISTRATION TAB -->
                    <div class="tab-pane fade <?php echo ($active_tab === 'hospital') ? 'show active' : ''; ?>" id="hospitalTabContent" role="tabpanel">
                        <form action="register.php" method="POST">
                            <input type="hidden" name="role" value="hospital">

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Hospital / Center Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-hospital"></i></span>
                                        <input type="text" name="h_name" class="form-control border-start-0" placeholder="e.g. St. Jude Children Hospital" value="<?php echo htmlspecialchars($h_name); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Official Hospital Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="h_email" class="form-control border-start-0" placeholder="info@hospital.org" value="<?php echo htmlspecialchars($h_email); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Contact Phone <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="h_phone" class="form-control border-start-0" placeholder="+1 (555) 000-0000" value="<?php echo htmlspecialchars($h_phone); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">City <span class="text-danger">*</span></label>
                                    <input type="text" name="h_city" class="form-control" placeholder="e.g. Metropolis" value="<?php echo htmlspecialchars($h_city); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Location / Wing Description</label>
                                    <input type="text" name="h_location" class="form-control" placeholder="e.g. East Wing, Pediatric OPD" value="<?php echo htmlspecialchars($h_location); ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Street Address <span class="text-danger">*</span></label>
                                    <textarea name="h_address" class="form-control" rows="2" placeholder="Full physical clinic address" required><?php echo htmlspecialchars($h_address); ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Account Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                        <input type="password" name="h_password" class="form-control border-start-0" placeholder="Min. 6 characters" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" name="h_confirm_password" class="form-control border-start-0" placeholder="Repeat password" required>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-emerald w-100 py-2.5">
                                        <i class="bi bi-building-check me-1"></i> Register Hospital Facility
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Login Link -->
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">Already registered? <a href="login.php" class="fw-bold text-teal">Sign In to Your Account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

