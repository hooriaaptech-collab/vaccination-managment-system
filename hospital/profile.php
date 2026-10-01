<?php
/**
 * Hospital Profile & Facility Settings
 * E-Vaccination Management System
 */

$page_title = 'Hospital Profile';
$page_header = 'Hospital Facility Settings';
$page_subheader = 'Manage clinic information, physical address, operating hours, and password';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('hospital');

$user_id = current_user_id();

// Fetch hospital and user
$stmt = $pdo->prepare("
    SELECT h.*, u.name, u.email, u.phone AS user_phone
    FROM hospitals h
    JOIN users u ON h.user_id = u.id
    WHERE h.user_id = ?
    LIMIT 1
");
$stmt->execute([$user_id]);
$hospital = $stmt->fetch();

if (!$hospital) {
    set_flash('danger', 'Hospital profile not found.');
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = sanitize($_POST['action'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | UPDATE HOSPITAL PROFILE
    |--------------------------------------------------------------------------
    */
    if ($action === 'update_profile') {

        $hospital_name = sanitize($_POST['hospital_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $operating_hours = sanitize($_POST['operating_hours'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | HOSPITAL IMAGE UPLOAD
        |--------------------------------------------------------------------------
        */

        // Keep existing image if no new image is uploaded
        $image_name = $hospital['image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

            // Check upload error
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                set_flash('danger', 'There was a problem uploading the hospital image.');
                header("Location: profile.php");
                exit();

            }

            // Maximum image size = 5 MB
            $max_size = 5 * 1024 * 1024;

            if ($_FILES['image']['size'] > $max_size) {

                set_flash('danger', 'Hospital image must be 5 MB or smaller.');
                header("Location: profile.php");
                exit();

            }

            // Allowed file extensions
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp','jfif'];

            $extension = strtolower(
                pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)
            );

            if (!in_array($extension, $allowed_extensions, true)) {

                set_flash(
                    'danger',
                    'Invalid image format. Please upload JPG, JPEG, PNG or WEBP.'
                );

                header("Location: profile.php");
                exit();

            }

            // Check actual MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file(
                $finfo,
                $_FILES['image']['tmp_name']
            );
            finfo_close($finfo);

            $allowed_mime_types = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!in_array($mime_type, $allowed_mime_types, true)) {

                set_flash('danger', 'The uploaded file is not a valid image.');

                header("Location: profile.php");
                exit();

            }

            /*
            |--------------------------------------------------------------------------
            | CREATE UPLOAD FOLDER
            |--------------------------------------------------------------------------
            */

            $upload_dir = __DIR__ . '/../uploads/hospitals/';

            if (!is_dir($upload_dir)) {

                if (!mkdir($upload_dir, 0755, true)) {

                    set_flash(
                        'danger',
                        'Unable to create hospital image upload folder.'
                    );

                    header("Location: profile.php");
                    exit();

                }
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE UNIQUE IMAGE NAME
            |--------------------------------------------------------------------------
            */

            $image_name = 'hospital_' . $user_id . '_' . time() . '.' . $extension;

            $image_path = $upload_dir . $image_name;

            /*
            |--------------------------------------------------------------------------
            | MOVE IMAGE TO uploads/hospitals/
            |--------------------------------------------------------------------------
            */

            if (!move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $image_path
            )) {

                // Keep old image if upload fails
                $image_name = $hospital['image'] ?? '';

                set_flash(
                    'danger',
                    'Hospital image could not be uploaded.'
                );

                header("Location: profile.php");
                exit();

            }
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE PROFILE DETAILS
        |--------------------------------------------------------------------------
        */

        if (!empty($hospital_name) && !empty($phone) && !empty($address)) {

            $pdo->beginTransaction();

            try {

                /*
                |--------------------------------------------------------------------------
                | UPDATE USERS TABLE
                |--------------------------------------------------------------------------
                */

                $u_upd = $pdo->prepare("
                    UPDATE users
                    SET name = ?, phone = ?
                    WHERE id = ?
                ");

                $u_upd->execute([
                    $hospital_name,
                    $phone,
                    $user_id
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE HOSPITALS TABLE
                |--------------------------------------------------------------------------
                */

                $h_upd = $pdo->prepare("
                    UPDATE hospitals
                    SET
                        hospital_name = ?,
                        phone = ?,
                        address = ?,
                        city = ?,
                        location = ?,
                        operating_hours = ?,
                        image = ?
                    WHERE user_id = ?
                ");

                $h_upd->execute([
                    $hospital_name,
                    $phone,
                    $address,
                    $city,
                    $location,
                    $operating_hours,
                    $image_name,
                    $user_id
                ]);

                $pdo->commit();

                // Update session hospital name
                $_SESSION['user_name'] = $hospital_name;

                set_flash(
                    'success',
                    'Hospital facility details updated successfully.'
                );

            } catch (Exception $ex) {

                $pdo->rollBack();

                set_flash(
                    'danger',
                    'Failed to update details: ' . $ex->getMessage()
                );
            }

        } else {

            set_flash(
                'danger',
                'Please fill in all required hospital details.'
            );
        }

        header("Location: profile.php");
        exit();
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'change_password') {

        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (
            empty($current_pass) ||
            empty($new_pass) ||
            empty($confirm_pass)
        ) {

            set_flash(
                'danger',
                'Please fill in all password fields.'
            );

        } elseif ($new_pass !== $confirm_pass) {

            set_flash(
                'danger',
                'New passwords do not match.'
            );

        } elseif (strlen($new_pass) < 6) {

            set_flash(
                'danger',
                'New password must be at least 6 characters long.'
            );

        } else {

            $stmt = $pdo->prepare("
                SELECT password
                FROM users
                WHERE id = ?
            ");

            $stmt->execute([$user_id]);

            $user_pass = $stmt->fetchColumn();

            if (password_verify($current_pass, $user_pass)) {

                $hashed = password_hash(
                    $new_pass,
                    PASSWORD_DEFAULT
                );

                $upd = $pdo->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE id = ?
                ");

                $upd->execute([
                    $hashed,
                    $user_id
                ]);

                set_flash(
                    'success',
                    'Hospital account password updated successfully.'
                );

            } else {

                set_flash(
                    'danger',
                    'Current password is incorrect.'
                );
            }
        }

        header("Location: profile.php");
        exit();
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

            <div class="row g-4">

                <!-- =====================================================
                     HOSPITAL PROFILE FORM
                ====================================================== -->

                <div class="col-lg-7">

                    <div class="card-3d p-4 bg-white h-100">

                        <div class="d-flex align-items-center gap-2 mb-3">

                            <div
                                class="stat-icon emerald"
                                style="width: 42px; height: 42px;"
                            >
                                <i class="bi bi-hospital"></i>
                            </div>

                            <h5 class="fw-bold font-outfit text-dark mb-0">
                                Healthcare Center Information
                            </h5>

                        </div>

                        <!-- IMPORTANT: multipart/form-data is required for image upload -->
                        <form
                            action="profile.php"
                            method="POST"
                            enctype="multipart/form-data"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="update_profile"
                            >

                            <div class="row g-3">

                                <!-- Hospital Name -->
                                <div class="col-md-8">

                                    <label class="form-label fw-semibold small text-dark">
                                        Hospital / Clinic Name
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="hospital_name"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($hospital['hospital_name'] ?? ''); ?>"
                                        required
                                    >

                                </div>

                                <!-- City -->
                                <div class="col-md-4">

                                    <label class="form-label fw-semibold small text-dark">
                                        City
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="city"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($hospital['city'] ?? ''); ?>"
                                        required
                                    >

                                </div>

                                <!-- Email -->
                                <div class="col-md-6">

                                    <label class="form-label fw-semibold small text-dark">
                                        Official Email (Login Email)
                                    </label>

                                    <input
                                        type="email"
                                        class="form-control bg-light"
                                        value="<?php echo htmlspecialchars($hospital['email'] ?? ''); ?>"
                                        readonly
                                    >

                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">

                                    <label class="form-label fw-semibold small text-dark">
                                        Contact Phone
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="phone"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($hospital['phone'] ?? ''); ?>"
                                        required
                                    >

                                </div>

                                <!-- Operating Hours -->
                                <div class="col-md-6">

                                    <label class="form-label fw-semibold small text-dark">
                                        Operating Hours
                                    </label>

                                    <input
                                        type="text"
                                        name="operating_hours"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($hospital['operating_hours'] ?? '08:00 AM - 05:00 PM'); ?>"
                                    >

                                </div>

                                <!-- Pediatric Department -->
                                <div class="col-md-6">

                                    <label class="form-label fw-semibold small text-dark">
                                        Wing / Pediatric Department
                                    </label>

                                    <input
                                        type="text"
                                        name="location"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($hospital['location'] ?? ''); ?>"
                                    >

                                </div>

                                <!-- Address -->
                                <div class="col-12">

                                    <label class="form-label fw-semibold small text-dark">
                                        Full Physical Address
                                        <span class="text-danger">*</span>
                                    </label>

                                    <textarea
                                        name="address"
                                        class="form-control"
                                        rows="2"
                                        required
                                    ><?php echo htmlspecialchars($hospital['address'] ?? ''); ?></textarea>

                                </div>

                                <!-- =====================================================
                                     HOSPITAL IMAGE UPLOAD
                                ====================================================== -->

                                <div class="col-12">

                                    <label class="form-label fw-semibold small text-dark">
                                        Hospital / Clinic Image
                                    </label>

                                    <input
                                        type="file"
                                        name="image"
                                        class="form-control"
                                        accept="image/jpeg,image/png,image/webp"
                                    >

                                    <small class="text-muted d-block mt-1">
                                       jfif , JPG, JPEG, PNG or WEBP — Maximum 5 MB
                                    </small>

                                    <?php if (!empty($hospital['image'])): ?>

                                        <div class="mt-3">

                                            <p class="small fw-semibold text-dark mb-2">
                                                Current Hospital Image
                                            </p>

                                            <img
                                                src="<?php echo base_url('uploads/hospitals/' . htmlspecialchars($hospital['image'])); ?>"
                                                alt="Hospital Image"
                                                style="
                                                    width: 180px;
                                                    height: 110px;
                                                    object-fit: cover;
                                                    border-radius: 12px;
                                                    border: 1px solid #ddd;
                                                "
                                            >

                                        </div>

                                    <?php endif; ?>

                                </div>

                                <!-- Save Button -->
                                <div class="col-12 mt-4">

                                    <button
                                        type="submit"
                                        class="btn btn-emerald btn-sm px-4"
                                    >
                                        <i class="bi bi-check2 me-1"></i>
                                        Save Facility Details
                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =====================================================
                     PASSWORD CARD
                ====================================================== -->

                <div class="col-lg-5">

                    <div class="card-3d p-4 bg-white h-100">

                        <div class="d-flex align-items-center gap-2 mb-3">

                            <div
                                class="stat-icon coral"
                                style="width: 42px; height: 42px;"
                            >
                                <i class="bi bi-key-fill"></i>
                            </div>

                            <h5 class="fw-bold font-outfit text-dark mb-0">
                                Staff Security & Password
                            </h5>

                        </div>

                        <form
                            action="profile.php"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="change_password"
                            >

                            <!-- Current Password -->
                            <div class="mb-3">

                                <label class="form-label fw-semibold small text-dark">
                                    Current Password
                                </label>

                                <input
                                    type="password"
                                    name="current_password"
                                    class="form-control"
                                    placeholder="••••••••"
                                    required
                                >

                            </div>

                            <!-- New Password -->
                            <div class="mb-3">

                                <label class="form-label fw-semibold small text-dark">
                                    New Password
                                </label>

                                <input
                                    type="password"
                                    name="new_password"
                                    class="form-control"
                                    placeholder="Min. 6 characters"
                                    required
                                >

                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">

                                <label class="form-label fw-semibold small text-dark">
                                    Confirm New Password
                                </label>

                                <input
                                    type="password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="Repeat new password"
                                    required
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-emerald btn-sm px-3"
                            >
                                <i class="bi bi-shield-lock-fill me-1"></i>
                                Change Password
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>