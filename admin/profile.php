<?php
/**
 * Admin Profile & Account Settings
 * E-Vaccination Management System
 */
$page_title = 'Account Settings';
$page_header = 'Admin Account Settings';
$page_subheader = 'Update administrator profile and change password';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$admin_id = current_user_id();

// Handle Profile Update / Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$name, $phone, $admin_id])) {
                $_SESSION['user_name'] = $name;
                set_flash('success', 'Profile details updated successfully.');
            } else {
                set_flash('danger', 'Failed to update profile.');
            }
        }
        header("Location: profile.php");
        exit();
    } elseif ($action === 'change_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
            set_flash('danger', 'Please fill in all password fields.');
        } elseif ($new_pass !== $confirm_pass) {
            set_flash('danger', 'New passwords do not match.');
        } elseif (strlen($new_pass) < 6) {
            set_flash('danger', 'New password must be at least 6 characters long.');
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $user_pass = $stmt->fetchColumn();

            if (password_verify($current_pass, $user_pass)) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->execute([$hashed, $admin_id]);
                set_flash('success', 'Password changed successfully.');
            } else {
                set_flash('danger', 'Current password is incorrect.');
            }
        }
        header("Location: profile.php");
        exit();
    }
}

// Fetch current admin user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <div class="row g-4">
                <!-- Profile Information Form -->
                <div class="col-lg-6">
                    <div class="card-3d p-4 bg-white h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="stat-icon emerald" style="width: 42px; height: 42px;">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <h5 class="fw-bold font-outfit text-dark mb-0">Admin Profile Details</h5>
                        </div>

                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark">Email Address (Read-only)</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($admin['email']); ?>" readonly>
                                <span class="text-muted small">System administrator email cannot be altered.</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
                            </div>

                            <button type="submit" class="btn btn-emerald btn-sm px-3">
                                <i class="bi bi-check2 me-1"></i> Update Profile Info
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change Password Form -->
                <div class="col-lg-6">
                    <div class="card-3d p-4 bg-white h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="stat-icon coral" style="width: 42px; height: 42px;">
                                <i class="bi bi-key-fill"></i>
                            </div>
                            <h5 class="fw-bold font-outfit text-dark mb-0">Security & Password</h5>
                        </div>

                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="change_password">

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark">Current Password</label>
                                <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark">New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                            </div>

                            <button type="submit" class="btn btn-emerald btn-sm px-3">
                                <i class="bi bi-shield-lock-fill me-1"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

