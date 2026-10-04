<?php

$page_title = 'Hospital Management';
$page_header = 'Authorized Hospitals & Clinics';
$page_subheader = 'Manage authorized immunization facilities, center locations, and contact records';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

// Upload one unique image per hospital. Returns the new filename.
function uploadHospitalImage(array $file): string {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Hospital image must be 5 MB or smaller.');
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Please upload a JPG, JPEG, PNG, WEBP or JFIF image.');
    }

    if (!is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('Invalid uploaded image.');
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        throw new RuntimeException('The selected file is not a supported image.');
    }

    $uploadDir = __DIR__ . '/../uploads/hospitals/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Could not create uploads/hospitals folder.');
    }

    // Random suffix prevents different hospitals from overwriting each other.
    $safeExtension = ($extension === 'jfif') ? 'jpg' : $extension;
    $filename = 'hospital_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $safeExtension;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new RuntimeException('Could not save the uploaded hospital image.');
    }
    return $filename;
}

// Handle Add / Edit / Delete POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'add') {
        $hospital_name = sanitize($_POST['hospital_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? 'Metropolis');
        $location = sanitize($_POST['location'] ?? '');
        $operating_hours = sanitize($_POST['operating_hours'] ?? '08:00 AM - 05:00 PM');
        $password = $_POST['password'] ?? 'Hospital@123';
        $image_name = '';

        if (empty($hospital_name) || empty($email) || empty($phone) || empty($address)) {
            set_flash('danger', 'Please fill in all required hospital details.');
        } else {
            // Check duplicate email
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                set_flash('danger', 'A user account with this email already exists.');
            } else {
                $pdo->beginTransaction();
                try {
                    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        $image_name = uploadHospitalImage($_FILES['image']);
                    }
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $u_ins = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'hospital', 'active')");
                    $u_ins->execute([$hospital_name, $email, $phone, $hashed_password]);
                    $user_id = $pdo->lastInsertId();

                    $h_ins = $pdo->prepare("INSERT INTO hospitals (user_id, hospital_name, email, phone, address, city, location, operating_hours, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                    $h_ins->execute([$user_id, $hospital_name, $email, $phone, $address, $city, $location, $operating_hours, $image_name ?: null]);
                    $hospital_id = $pdo->lastInsertId();

                    // Seed standard vaccines
                    $v_stmt = $pdo->query("SELECT id FROM vaccines");
                    $vaccines = $v_stmt->fetchAll();
                    $hv_ins = $pdo->prepare("INSERT INTO hospital_vaccines (hospital_id, vaccine_id, status) VALUES (?, ?, 'Available')");
                    foreach ($vaccines as $vac) {
                        $hv_ins->execute([$hospital_id, $vac['id']]);
                    }

                    $pdo->commit();
                    set_flash('success', 'Hospital "' . htmlspecialchars($hospital_name) . '" registered successfully.');
                } catch (Exception $ex) {
                    $pdo->rollBack();
                    set_flash('danger', 'Failed to register hospital: ' . $ex->getMessage());
                }
            }
        }
        header("Location: hospitals.php");
        exit();
    } elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $hospital_name = sanitize($_POST['hospital_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $operating_hours = sanitize($_POST['operating_hours'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');

        if ($id > 0 && !empty($hospital_name) && !empty($email) && !empty($phone) && !empty($address)) {
            try {
                $oldStmt = $pdo->prepare("SELECT image, user_id FROM hospitals WHERE id = ? LIMIT 1");
                $oldStmt->execute([$id]);
                $oldHospital = $oldStmt->fetch(PDO::FETCH_ASSOC);
                if (!$oldHospital) {
                    throw new RuntimeException('Hospital not found.');
                }

                $image_name = $oldHospital['image'] ?? '';
                if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $newImage = uploadHospitalImage($_FILES['image']);
                    $image_name = $newImage;
                }

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE hospitals SET hospital_name = ?, email = ?, phone = ?, address = ?, city = ?, location = ?, operating_hours = ?, status = ?, image = ? WHERE id = ?");
                $stmt->execute([$hospital_name, $email, $phone, $address, $city, $location, $operating_hours, $status, $image_name ?: null, $id]);

                // Keep the linked login account's contact details in sync.
                $uStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $uStmt->execute([$hospital_name, $email, $phone, $oldHospital['user_id']]);
                $pdo->commit();
                set_flash('success', 'Hospital details and image updated successfully.');
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                set_flash('danger', 'Failed to update hospital: ' . $ex->getMessage());
            }
        } else {
            set_flash('danger', 'Please fill in all required hospital details.');
        }
        header("Location: hospitals.php");
        exit();
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            // Get user_id
            $u_stmt = $pdo->prepare("SELECT user_id FROM hospitals WHERE id = ?");
            $u_stmt->execute([$id]);
            $user_id = $u_stmt->fetchColumn();

            if ($user_id) {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
            } else {
                $pdo->prepare("DELETE FROM hospitals WHERE id = ?")->execute([$id]);
            }
            set_flash('success', 'Hospital facility and linked user account removed.');
        }
        header("Location: hospitals.php");
        exit();
    } elseif ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $curr = sanitize($_POST['current_status'] ?? 'active');
        $new_status = ($curr === 'active') ? 'inactive' : 'active';

        $stmt = $pdo->prepare("UPDATE hospitals SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        set_flash('info', 'Hospital status changed to ' . $new_status . '.');
        header("Location: hospitals.php");
        exit();
    }
}

// Fetch all hospitals
$stmt = $pdo->query("SELECT * FROM hospitals ORDER BY id ASC");
$hospitals = $stmt->fetchAll();

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
                    <h5 class="fw-bold font-outfit text-dark mb-0">Partner Healthcare Centers (<?php echo count($hospitals); ?>)</h5>
                    <span class="text-muted small">Manage authorized clinics and immunization points</span>
                </div>
                <div class="d-flex gap-2">
                    <div style="max-width: 250px;">
                        <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Filter hospitals...">
                    </div>
                    <button type="button" class="btn btn-emerald btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addHospitalModal">
                        <i class="bi bi-building-add me-1"></i> Add Hospital Center
                    </button>
                </div>
            </div>

            <!-- Hospitals Table Card -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Hospital Name & Location</th>
                                <th>Contact Information</th>
                                <th>Operating Hours</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hospitals)): ?>
                                <?php foreach ($hospitals as $hosp): ?>
                                    <tr>
                                        <td style="width:100px;">
                                            <?php
                                                $adminDefaultImage = base_url('assets/images/hospital-default.jpg');
                                                $adminHospitalImage = $adminDefaultImage;
                                                if (!empty($hosp['image']) && is_file(__DIR__ . '/../uploads/hospitals/' . basename($hosp['image']))) {
                                                    $adminHospitalImage = base_url('uploads/hospitals/' . rawurlencode(basename($hosp['image'])));
                                                }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($adminHospitalImage, ENT_QUOTES, 'UTF-8'); ?>" alt="Hospital image" style="width:76px;height:58px;object-fit:cover;border-radius:8px;" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($adminDefaultImage, ENT_QUOTES, 'UTF-8'); ?>';">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($hosp['hospital_name']); ?></div>
                                            <div class="text-muted small">
                                                <i class="bi bi-geo-alt me-1 text-teal"></i><?php echo htmlspecialchars($hosp['address'] . ', ' . $hosp['city']); ?>
                                            </div>
                                            <?php if (!empty($hosp['location'])): ?>
                                                <span class="badge bg-light text-muted border small mt-1"><?php echo htmlspecialchars($hosp['location']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($hosp['phone']); ?></div>
                                            <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($hosp['email']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border small">
                                                <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($hosp['operating_hours'] ?? '08:00 AM - 05:00 PM'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="hospitals.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $hosp['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $hosp['status']; ?>">
                                                <button type="submit" class="btn p-0 border-0" title="Click to toggle status">
                                                    <?php echo status_badge($hosp['status']); ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1.5">
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#editHospitalModal<?php echo $hosp['id']; ?>" title="Edit Hospital">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <!-- Delete Form -->
                                                <form action="hospitals.php" method="POST" onsubmit="return confirmDelete('Are you sure you want to delete this hospital? This will delete associated hospital accounts.');" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $hosp['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete Hospital">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Edit Hospital Modal -->
                                            <div class="modal fade" id="editHospitalModal<?php echo $hosp['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <form action="hospitals.php" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?php echo $hosp['id']; ?>">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"><i class="bi bi-pencil-square text-teal me-2"></i> Edit Hospital Details</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row g-3">
                                                                    <div class="col-md-8">
                                                                        <label class="form-label fw-semibold small text-dark">Hospital / Center Name <span class="text-danger">*</span></label>
                                                                        <input type="text" name="hospital_name" class="form-control" value="<?php echo htmlspecialchars($hosp['hospital_name']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label fw-semibold small text-dark">Status</label>
                                                                        <select name="status" class="form-select">
                                                                            <option value="active" <?php echo ($hosp['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                                                            <option value="inactive" <?php echo ($hosp['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Official Email <span class="text-danger">*</span></label>
                                                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($hosp['email']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Contact Phone <span class="text-danger">*</span></label>
                                                                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($hosp['phone']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">City <span class="text-danger">*</span></label>
                                                                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($hosp['city']); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label fw-semibold small text-dark">Operating Hours</label>
                                                                        <input type="text" name="operating_hours" class="form-control" value="<?php echo htmlspecialchars($hosp['operating_hours']); ?>">
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold small text-dark">Location / Wing / Landmark</label>
                                                                        <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($hosp['location']); ?>">
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold small text-dark">Change Hospital Image</label>
                                                                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.jfif,image/jpeg,image/png,image/webp">
                                                                        <small class="text-muted d-block mt-1">Optional — JPG, JPEG, PNG, WEBP or JFIF; maximum 5 MB. Leave empty to keep the current image.</small>
                                                                        <?php if (!empty($hosp['image']) && is_file(__DIR__ . '/../uploads/hospitals/' . basename($hosp['image']))): ?>
                                                                            <img class="mt-2" src="<?php echo htmlspecialchars(base_url('uploads/hospitals/' . rawurlencode(basename($hosp['image']))), ENT_QUOTES, 'UTF-8'); ?>" alt="Current hospital image" style="width:120px;height:80px;object-fit:cover;border-radius:8px;">
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label fw-semibold small text-dark">Street Address <span class="text-danger">*</span></label>
                                                                        <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($hosp['address']); ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-emerald btn-sm px-3">Save Hospital</button>
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
                                        No hospital centers found. Click "Add Hospital Center" to create one.
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

<!-- Add Hospital Modal -->
<div class="modal fade" id="addHospitalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="hospitals.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-building-add text-teal me-2"></i> Register New Hospital Center</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-dark">Hospital / Center Name <span class="text-danger">*</span></label>
                            <input type="text" name="hospital_name" class="form-control" placeholder="e.g. Apex Pediatric Center" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="Metropolis" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Official Email (Used for Login) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="info@apexpediatric.org" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Operating Hours</label>
                            <input type="text" name="operating_hours" class="form-control" value="08:00 AM - 05:00 PM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Initial Password</label>
                            <input type="text" name="password" class="form-control" value="Hospital@123">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Location / Wing Description</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. 2nd Floor, Child Immunization Wing">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Full Physical Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Street address, building number, area..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Hospital / Clinic Image</label>
                            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.jfif,image/jpeg,image/png,image/webp">
                            <small class="text-muted d-block mt-1">Optional — JPG, JPEG, PNG, WEBP or JFIF; maximum 5 MB.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-3"><i class="bi bi-check2-circle me-1"></i> Register Center</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

