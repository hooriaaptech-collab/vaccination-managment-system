<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect directly to role dashboard
if (is_logged_in()) {
    redirect_by_role(current_user_role());
}

$error = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email_val = $email;

    if (empty($email) || empty($password)) {
        $error = 'Please fill in both email and password.';
    } else {
        // Query user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = 'Your account has been deactivated. Please contact the administrator.';
            } else {
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // If role is hospital, store hospital_id in session too
                if ($user['role'] === 'hospital') {
                    $h_stmt = $pdo->prepare("SELECT id, hospital_name FROM hospitals WHERE user_id = ? LIMIT 1");
                    $h_stmt->execute([$user['id']]);
                    $hosp = $h_stmt->fetch();
                    if ($hosp) {
                        $_SESSION['hospital_id'] = $hosp['id'];
                        $_SESSION['hospital_name'] = $hosp['hospital_name'];
                    }
                }

                set_flash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
                redirect_by_role($user['role']);
            }
        } else {
            $error = 'Invalid email address or password. Please try again.';
        }
    }
}

$page_title = 'Login - E-Vaccination System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card-3d p-4 p-sm-5 bg-white">
                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="brand-icon mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.6rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1">Account Login</h3>
                    <p class="text-muted small">Sign in to access your E-Vaccination portal</p>
                </div>

                <?php display_flash(); ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show custom-alert shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="login.php" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="loginEmail" class="form-control border-start-0" placeholder="name@example.com" value="<?php echo htmlspecialchars($email_val); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold small text-dark mb-0">Password</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" id="loginPassword" class="form-control border-start-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-emerald w-100 py-2.5 mb-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
                    </button>
                </form>


                <!-- Registration Link -->
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">Don't have an account yet? <a href="register.php" class="fw-bold text-teal">Register as Parent or Hospital</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillLogin(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pass;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

