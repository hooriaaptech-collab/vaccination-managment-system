<?php

$page_title = 'Book Appointment';
$page_header = 'Schedule Vaccination Appointment';
$page_subheader = 'Submit a vaccination request for your child at an authorized hospital center';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();

// Fetch parent's children
$c_stmt = $pdo->prepare("SELECT id, name, dob, gender FROM children WHERE parent_id = ? ORDER BY name ASC");
$c_stmt->execute([$parent_id]);
$children = $c_stmt->fetchAll();

// If parent has no children registered yet, prompt them to add a child first
if (empty($children)) {
    set_flash('warning', 'Please register your child first before scheduling a vaccination appointment.');
    header("Location: add-child.php");
    exit();
}

// Fetch active hospitals & vaccines
$hospitals = $pdo->query("SELECT id, hospital_name, city, address, operating_hours FROM hospitals WHERE status = 'active' ORDER BY hospital_name ASC")->fetchAll();
$vaccines = $pdo->query("SELECT id, vaccine_name, short_code, recommended_age, target_disease FROM vaccines WHERE status = 'Available' ORDER BY id ASC")->fetchAll();

// Pre-selected parameters from query string
$preset_child_id = intval($_GET['child_id'] ?? 0);
$preset_hospital_id = intval($_GET['hospital_id'] ?? 0);
$preset_vaccine_id = intval($_GET['vaccine_id'] ?? 0);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = intval($_POST['child_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);
    $vaccine_id = intval($_POST['vaccine_id'] ?? 0);
    $appointment_date = sanitize($_POST['appointment_date'] ?? '');
    $appointment_time = sanitize($_POST['appointment_time'] ?? '');
    $parent_notes = sanitize($_POST['parent_notes'] ?? '');

    // Validate ownership of child
    $chk_child = $pdo->prepare("SELECT id FROM children WHERE id = ? AND parent_id = ?");
    $chk_child->execute([$child_id, $parent_id]);
    $valid_child = $chk_child->fetch();

    if (!$valid_child) {
        $error = 'Invalid child selected.';
    } elseif ($hospital_id <= 0 || $vaccine_id <= 0 || empty($appointment_date) || empty($appointment_time)) {
        $error = 'Please fill in all required appointment fields.';
    } elseif (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $error = 'Appointment date cannot be in the past. Please select today or a future date.';
    } else {
        // Generate unique booking code
        $booking_code = generate_booking_code();

        $stmt = $pdo->prepare("
            INSERT INTO appointments (booking_code, parent_id, child_id, hospital_id, vaccine_id, appointment_date, appointment_time, status, parent_notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
        ");
        if ($stmt->execute([$booking_code, $parent_id, $child_id, $hospital_id, $vaccine_id, $appointment_date, $appointment_time, $parent_notes])) {
            set_flash('success', 'Appointment request #' . $booking_code . ' submitted successfully! It is now pending administrative verification.');
            header("Location: requests.php");
            exit();
        } else {
            $error = 'Failed to submit appointment request. Please try again.';
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

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="card-3d p-4 p-md-5 bg-white">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="stat-icon emerald" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="bi bi-calendar-plus-fill"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold font-outfit text-dark mb-0">Book Vaccination Appointment</h4>
                                <span class="text-muted small">Select your child, vaccine dose, preferred clinic, and date</span>
                            </div>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show custom-alert shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="booking.php" method="POST">
                            <div class="row g-4">
                                <!-- Step 1: Select Child -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">
                                        <i class="bi bi-person-heart text-teal me-1"></i> Select Child <span class="text-danger">*</span>
                                    </label>
                                    <select name="child_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Registered Child --</option>
                                        <?php foreach ($children as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php echo ($preset_child_id == $c['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c['name']); ?> (<?php echo htmlspecialchars($c['gender']); ?>, <?php echo calculate_age($c['dob']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Step 2: Select Vaccine -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">
                                        <i class="bi bi-capsule text-teal me-1"></i> Select Vaccine <span class="text-danger">*</span>
                                    </label>
                                    <select name="vaccine_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Vaccine Dose --</option>
                                        <?php foreach ($vaccines as $v): ?>
                                            <option value="<?php echo $v['id']; ?>" <?php echo ($preset_vaccine_id == $v['id']) ? 'selected' : ''; ?>>
                                                [<?php echo htmlspecialchars($v['short_code']); ?>] <?php echo htmlspecialchars($v['vaccine_name']); ?> (<?php echo htmlspecialchars($v['recommended_age']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Step 3: Select Hospital -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">
                                        <i class="bi bi-hospital text-teal me-1"></i> Select Hospital / Healthcare Center <span class="text-danger">*</span>
                                    </label>
                                    <select name="hospital_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Hospital Facility --</option>
                                        <?php foreach ($hospitals as $h): ?>
                                            <option value="<?php echo $h['id']; ?>" <?php echo ($preset_hospital_id == $h['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($h['hospital_name']); ?> — <?php echo htmlspecialchars($h['address']); ?>, <?php echo htmlspecialchars($h['city']); ?> (<?php echo htmlspecialchars($h['operating_hours']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Step 4: Appointment Date -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">
                                        <i class="bi bi-calendar-event text-teal me-1"></i> Preferred Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="appointment_date" class="form-control form-control-lg" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" required>
                                </div>

                                <!-- Step 5: Time Slot -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">
                                        <i class="bi bi-clock text-teal me-1"></i> Preferred Time Slot <span class="text-danger">*</span>
                                    </label>
                                    <select name="appointment_time" class="form-select form-select-lg" required>
                                        <option value="09:00 AM">09:00 AM - 10:00 AM (Morning)</option>
                                        <option value="10:30 AM" selected>10:30 AM - 11:30 AM (Morning)</option>
                                        <option value="11:30 AM">11:30 AM - 12:30 PM (Noon)</option>
                                        <option value="02:00 PM">02:00 PM - 03:00 PM (Afternoon)</option>
                                        <option value="03:30 PM">03:30 PM - 04:30 PM (Evening)</option>
                                    </select>
                                </div>

                                <!-- Step 6: Parent Notes -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Special Instructions / Health Notes (Optional)</label>
                                    <textarea name="parent_notes" class="form-control" rows="2" placeholder="e.g. Baby had mild fever last week, or requesting booster dose administration."></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <div class="p-3 bg-light rounded-3 border mb-3 small text-muted">
                                        <i class="bi bi-info-circle-fill text-teal me-1"></i>
                                        <strong>Notice:</strong> Your appointment request will be verified by the system administrator to check vaccine stock and hospital capacity. You will be able to track live status in your dashboard.
                                    </div>
                                    <button type="submit" class="btn btn-emerald btn-lg w-100 py-3 font-outfit">
                                        <i class="bi bi-send-check-fill me-1"></i> Submit Appointment Request
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



