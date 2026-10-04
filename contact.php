<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$success = '';
$error = '';
$name_val = '';
$email_val = '';
$phone_val = '';
$subject_val = '';
$message_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    $name_val = $name;
    $email_val = $email;
    $phone_val = $phone;
    $subject_val = $subject;
    $message_val = $message;

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields (Name, Email, Subject, Message).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'Unread')");
            if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
                set_flash('success', 'Thank you! Your message has been sent successfully. Our support desk will contact you soon.');
                header("Location: contact.php");
                exit();
            } else {
                $error = 'Failed to send your message. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Contact Support - E-Vaccination Management System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Page Header Banner -->
<div class="py-5 bg-white border-bottom">
    <div class="container text-center max-w-700 mx-auto" style="max-width: 720px;">
        <span class="section-tag">Get in Touch</span>
        <h1 class="fw-extrabold font-outfit text-dark mb-3">Contact Support Desk</h1>
        <p class="text-muted">
            Have questions about vaccination schedules, hospital registration, or technical assistance? Send us a message or reach our 24/7 helpline.
        </p>
    </div>
</div>

<div class="container py-5">
    <?php display_flash(); ?>

    <div class="row g-5">
        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="card-3d p-4 p-md-5 bg-white">
                <h3 class="fw-bold font-outfit text-dark mb-2">Send an Inquiry</h3>
                <p class="text-muted small mb-4">Fill out the form below and our coordination team will respond within 24 hours.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show custom-alert shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Eleanor Vance" value="<?php echo htmlspecialchars($name_val); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo htmlspecialchars($email_val); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000" value="<?php echo htmlspecialchars($phone_val); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" placeholder="e.g. Schedule Query / Clinic Listing" value="<?php echo htmlspecialchars($subject_val); ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Describe your question or feedback..." required><?php echo htmlspecialchars($message_val); ?></textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-emerald w-100 py-2.5">
                                <i class="bi bi-send-fill me-1"></i> Submit Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contact Cards Column -->
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-4">
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon emerald">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Emergency Helpline</h6>
                            <span class="text-teal fw-bold">+1 (800) 555-EVAC (3822)</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Toll-free 24/7 guidance for routine and supplementary immunization drives.</p>
                </div>

                <div class="stat-box">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon teal">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Email Support</h6>
                            <span class="text-teal fw-bold">support@evaccine-system.org</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Direct queries regarding records, certificate re-issuance, and clinic partnerships.</p>
                </div>

                <div class="stat-box">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon coral">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Directorate Center</h6>
                            <span class="text-dark small">Health District, Building 4B, Metropolis</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Open Monday to Friday: 08:30 AM to 05:00 PM.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

